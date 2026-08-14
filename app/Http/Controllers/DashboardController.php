<?php

namespace App\Http\Controllers;

use App\Models\RoadSign;
use App\Models\SignPracticeSession;
use App\Models\StudentSignProgress;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $progress = StudentSignProgress::query()
            ->where('user_id', $user->id)
            ->orderByDesc('accuracy_percent')
            ->get();

        $attempted = (int) $progress->sum('signs_attempted');
        $correct = (int) $progress->sum('signs_correct');

        $completedSessions = SignPracticeSession::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed');

        return Inertia::render('Dashboard', [
            'overallProgress' => [
                'attempted' => $attempted,
                'correct' => $correct,
                'accuracy_percent' => $attempted > 0 ? (int) round(($correct / $attempted) * 100) : 0,
                'sessions_completed' => (clone $completedSessions)->count(),
                'best_score' => (int) ((clone $completedSessions)->max('score_percent') ?? 0),
            ],
            'progressByType' => $progress
                ->take(6)
                ->map(fn (StudentSignProgress $item) => [
                    'sign_type' => $item->sign_type,
                    'accuracy_percent' => $item->accuracy_percent,
                    'signs_attempted' => $item->signs_attempted,
                    'signs_correct' => $item->signs_correct,
                    'mastery_level' => $item->mastery_level,
                    'last_practiced_at' => $item->last_practiced_at?->toDateTimeString(),
                ])
                ->values(),
            'recentSessions' => $completedSessions
                ->latest('completed_at')
                ->take(5)
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
            'activeRoadSignsCount' => RoadSign::query()->where('is_active', true)->count(),
        ]);
    }
}