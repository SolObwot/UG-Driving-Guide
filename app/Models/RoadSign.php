<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoadSign extends Model
{
    /** @use HasFactory<\Database\Factories\RoadSignFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'description',
        'image_path',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Build a shuffled multiple-choice option set for identifying this sign:
     * this sign plus `distractorCount` other signs of the same type.
     * Falls back to any type if there aren't enough signs of the same type.
     */
    public function buildOptions(int $distractorCount = 3)
    {
        $distractors = static::where('id', '!=', $this->id)
            ->where('is_active', true)
            ->where('type', $this->type)
            ->inRandomOrder()
            ->take($distractorCount)
            ->get();

        if ($distractors->count() < $distractorCount) {
            $missing = $distractorCount - $distractors->count();
            $extra = static::where('id', '!=', $this->id)
                ->where('is_active', true)
                ->whereNotIn('id', $distractors->pluck('id'))
                ->inRandomOrder()
                ->take($missing)
                ->get();
            $distractors = $distractors->concat($extra);
        }

        return $distractors->push($this)->shuffle()->values();
    }
}
