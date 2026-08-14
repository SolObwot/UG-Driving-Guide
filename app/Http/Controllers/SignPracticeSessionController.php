<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSignPracticeSessionRequest;
use App\Http\Requests\UpdateSignPracticeSessionRequest;
use App\Models\RoadSign;
use App\Models\SignPracticeSession;
use App\Models\StudentSignProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class SignPracticeSessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $activeSession = SignPracticeSession::query()
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->when($request->filled('session'), fn ($query) => $query->whereKey((int) $request->integer('session')))
            ->latest('started_at')
            ->first();

        $question = null;
        $activeSessionPayload = null;

        if ($activeSession) {
            $answeredCount = $activeSession->answers()->count();

            if ($activeSession->total_questions > 0 && $answeredCount >= $activeSession->total_questions) {
                $activeSession->complete();
                $activeSession = null;
            } else {
                $questionData = $activeSession->questionAt($answeredCount);
                $questionSign = $questionData['sign'] ?? null;

                if ($questionSign) {
                    $question = [
                        'index' => $answeredCount,
                        'total' => $activeSession->total_questions,
                        'sign' => [
                            'id' => $questionSign->id,
                            'name' => $questionSign->name,
                            'type' => $questionSign->type,
                            'description' => $questionSign->description,
                            'image_path' => $questionSign->image_path,
                        ],
                        'options' => collect($questionData['options'] ?? [])->map(fn ($option) => [
                            'id' => $option->id,
                            'name' => $option->name,
                        ])->values(),
                    ];
                }

                $activeSessionPayload = [
                    'id' => $activeSession->id,
                    'sign_type' => $activeSession->sign_type,
                    'total_questions' => $activeSession->total_questions,
                    'answered_count' => $answeredCount,
                ];
            }
        }

        $progress = StudentSignProgress::query()
            ->where('user_id', $user->id)
            ->orderByDesc('last_practiced_at')
            ->get();

        return Inertia::render('SignPractice', [
            'signTypes' => RoadSign::query()
                ->where('is_active', true)
                ->distinct()
                ->orderBy('type')
                ->pluck('type')
                ->values(),
            'activeSession' => $activeSessionPayload,
            'question' => $question,
            'progressByType' => $progress->map(fn (StudentSignProgress $item) => [
                'sign_type' => $item->sign_type,
                'accuracy_percent' => $item->accuracy_percent,
                'signs_attempted' => $item->signs_attempted,
                'signs_correct' => $item->signs_correct,
                'mastery_level' => $item->mastery_level,
                'last_practiced_at' => $item->last_practiced_at?->toDateTimeString(),
            ])->values(),
            'recentSessions' => SignPracticeSession::query()
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->latest('completed_at')
                ->take(6)
                ->get(['id', 'sign_type', 'total_questions', 'correct_count', 'wrong_count', 'score_percent', 'completed_at'])
                ->map(fn (SignPracticeSession $session) => [
                    'id' => $session->id,
                    'sign_type' => $session->sign_type,
                    'total_questions' => $session->total_questions,
                    'correct_count' => $session->correct_count,
                    'wrong_count' => $session->wrong_count,
                    'score_percent' => $session->score_percent,
                    'completed_at' => $session->completed_at?->toDateTimeString(),
                ])
                ->values(),
        ]);
    }

    /**
     * Show completed practice sessions with per-question review.
     */
    public function history(Request $request): Response
    {
        $user = $request->user();

        $allowedTypes = [
            'warning',
            'regulatory',
            'road_markings',
            'information',
            'road_signs',
            'traffic_signals',
            'hand_signals',
            'other',
        ];

        $signType = (string) $request->query('sign_type', 'all');
        $outcome = (string) $request->query('outcome', 'all');
        $sort = (string) $request->query('sort', 'latest');

        $baseQuery = SignPracticeSession::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed');

        if ($signType !== 'all' && in_array($signType, $allowedTypes, true)) {
            $baseQuery->where('sign_type', $signType);
        }

        if ($outcome === 'mistakes_only') {
            $baseQuery->where('wrong_count', '>', 0);
        }

        if ($outcome === 'perfect_only') {
            $baseQuery->where('wrong_count', 0);
        }

        match ($sort) {
            'score_desc' => $baseQuery->orderByDesc('score_percent')->orderByDesc('completed_at'),
            'score_asc' => $baseQuery->orderBy('score_percent')->orderByDesc('completed_at'),
            default => $baseQuery->orderByDesc('completed_at'),
        };

        $sessions = (clone $baseQuery)
            ->with([
                'signs',
                'answers.roadSign',
                'answers.selectedSign',
            ])
            ->paginate(6)
            ->withQueryString();

        $summaryQuery = (clone $baseQuery);
        $summaryTotal = (clone $summaryQuery)->count();
        $summaryAverage = (int) round((clone $summaryQuery)->avg('score_percent') ?? 0);
        $summaryPerfect = (clone $summaryQuery)->where('wrong_count', 0)->count();

        /** @var LengthAwarePaginator $sessions */
        $sessions->setCollection(
            $sessions->getCollection()->map(function (SignPracticeSession $session) {
                $answersBySign = $session->answers->keyBy('road_sign_id');

                $questions = $session->signs->values()->map(function (RoadSign $sign, int $index) use ($answersBySign) {
                    $answer = $answersBySign->get($sign->id);

                    return [
                        'order' => $index + 1,
                        'shown_sign' => [
                            'id' => $sign->id,
                            'name' => $sign->name,
                            'image_path' => $sign->image_path,
                            'type' => $sign->type,
                        ],
                        'selected_sign' => $answer?->selectedSign
                            ? [
                                'id' => $answer->selectedSign->id,
                                'name' => $answer->selectedSign->name,
                            ]
                            : null,
                        'correct_sign' => [
                            'id' => $sign->id,
                            'name' => $sign->name,
                        ],
                        'is_correct' => (bool) ($answer?->is_correct ?? false),
                        'time_taken_seconds' => $answer?->time_taken_seconds,
                    ];
                });

                return [
                    'id' => $session->id,
                    'sign_type' => $session->sign_type,
                    'score_percent' => $session->score_percent,
                    'total_questions' => $session->total_questions,
                    'correct_count' => $session->correct_count,
                    'wrong_count' => $session->wrong_count,
                    'completed_at' => $session->completed_at?->toDateTimeString(),
                    'questions' => $questions,
                ];
            })
        );

        return Inertia::render('PracticeHistory', [
            'sessions' => $sessions,
            'availableSignTypes' => $allowedTypes,
            'filters' => [
                'sign_type' => $signType,
                'outcome' => $outcome,
                'sort' => $sort,
            ],
            'summary' => [
                'total' => $summaryTotal,
                'average_score' => $summaryAverage,
                'perfect_sessions' => $summaryPerfect,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSignPracticeSessionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $signType = $validated['sign_type'] ?? null;
        $questionCount = (int) ($validated['total_questions'] ?? 10);

        $availableSigns = RoadSign::query()
            ->where('is_active', true)
            ->when($signType, fn ($query) => $query->where('type', $signType))
            ->count();

        if ($availableSigns === 0) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('No active road signs are available for the selected category.'),
            ]);

            return to_route('practice.index');
        }

        $session = SignPracticeSession::start(
            userId: $request->user()->id,
            signType: $signType,
            count: min($questionCount, $availableSigns),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Practice session started. Choose the correct sign name for each image.'),
        ]);

        return to_route('practice.index', ['session' => $session->id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(SignPracticeSession $signPracticeSession)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SignPracticeSession $signPracticeSession)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSignPracticeSessionRequest $request, SignPracticeSession $signPracticeSession)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SignPracticeSession $signPracticeSession)
    {
        //
    }
}
