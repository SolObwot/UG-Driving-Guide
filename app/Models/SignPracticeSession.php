<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class SignPracticeSession extends Model
{
    /** @use HasFactory<\Database\Factories\SignPracticeSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sign_type',
        'total_questions',
        'correct_count',
        'wrong_count',
        'score_percent',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * The road signs drawn into this session, in order.
     */
    public function signs(): BelongsToMany
    {
        return $this->belongsToMany(RoadSign::class, 'sign_practice_session_items', 'session_id', 'road_sign_id')
            ->withPivot('order')
            ->orderBy('sign_practice_session_items.order');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SignPracticeAnswer::class, 'session_id');
    }

    /**
     * Start a practice session for a student on one sign type (or null = mixed).
     * Draws `count` random road signs of that type and snapshots them into
     * sign_practice_session_items so the set is fixed for this run.
     */
    public static function start(int $userId, ?string $signType, int $count = 10): self
    {
        return DB::transaction(function () use ($userId, $signType, $count) {
            $session = self::create([
                'user_id' => $userId,
                'sign_type' => $signType,
                'total_questions' => 0,
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            $signs = RoadSign::when($signType, fn($q) => $q->where('type', $signType))
                ->where('is_active', true)
                ->inRandomOrder()
                ->take($count)
                ->get();

            $rows = $signs->values()->map(fn($sign, $index) => [
                'session_id' => $session->id,
                'road_sign_id' => $sign->id,
                'order' => $index,
            ])->all();

            if (! empty($rows)) {
                DB::table('sign_practice_session_items')->insert($rows);
            }

            $session->update(['total_questions' => $signs->count()]);

            return $session;
        });
    }

    /**
     * Get the sign to display plus a shuffled MCQ option set for a given
     * position in the session (0-indexed), ready for the practice screen.
     */
    public function questionAt(int $index): array
    {
        $sign = $this->signs()->get()[$index] ?? null;

        if (! $sign) {
            return [];
        }

        return [
            'sign' => $sign,
            'options' => $sign->buildOptions(),
        ];
    }

    /**
     * Record the student's answer: which sign was shown, which sign name
     * they picked as the answer.
     */
    public function answer(RoadSign $shownSign, ?RoadSign $selectedSign, ?int $timeTakenSeconds = null): SignPracticeAnswer
    {
        return $this->answers()->updateOrCreate(
            ['road_sign_id' => $shownSign->id],
            [
                'selected_road_sign_id' => $selectedSign?->id,
                'is_correct' => $selectedSign?->id === $shownSign->id,
                'time_taken_seconds' => $timeTakenSeconds,
            ]
        );
    }

    /**
     * Tally the session, mark it completed, and roll the result into
     * student_sign_progress for this sign type (or each type touched, if mixed).
     */
    public function complete(): self
    {
        return DB::transaction(function () {
            $correct = $this->answers()->where('is_correct', true)->count();
            $total = $this->answers()->count();

            $this->update([
                'correct_count' => $correct,
                'wrong_count' => $total - $correct,
                'total_questions' => $total,
                'score_percent' => $total > 0 ? (int) round(($correct / $total) * 100) : 0,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $this->updateStudentSignProgress();

            return $this->fresh();
        });
    }

    /**
     * Update per-sign-type progress. If the session was mixed (sign_type null),
     * split the tally across whichever sign types actually appeared.
     */
    protected function updateStudentSignProgress(): void
    {
        $answers = $this->answers()->with('roadSign')->get();

        $byType = $answers->groupBy(fn($answer) => $answer->roadSign->type);

        foreach ($byType as $signType => $group) {
            $progress = StudentSignProgress::firstOrNew([
                'user_id' => $this->user_id,
                'sign_type' => $signType,
            ]);

            $attempted = $group->count();
            $correct = $group->where('is_correct', true)->count();

            $progress->signs_attempted = ($progress->signs_attempted ?? 0) + $attempted;
            $progress->signs_correct = ($progress->signs_correct ?? 0) + $correct;
            $progress->accuracy_percent = $progress->signs_attempted > 0
                ? (int) round(($progress->signs_correct / $progress->signs_attempted) * 100)
                : 0;
            $progress->mastery_level = match (true) {
                $progress->accuracy_percent >= 85 => 'advanced',
                $progress->accuracy_percent >= 60 => 'intermediate',
                default => 'beginner',
            };
            $progress->last_practiced_at = now();
            $progress->save();
        }
    }
}
