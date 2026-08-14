<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSignPracticeAnswerRequest;
use App\Http\Requests\UpdateSignPracticeAnswerRequest;
use App\Models\RoadSign;
use App\Models\SignPracticeAnswer;
use App\Models\SignPracticeSession;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class SignPracticeAnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StoreSignPracticeAnswerRequest $request, SignPracticeSession $signPracticeSession): RedirectResponse
    {
        if ($signPracticeSession->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($signPracticeSession->status !== 'in_progress') {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('This practice session is no longer active.'),
            ]);

            return to_route('practice.index');
        }

        $validated = $request->validated();

        $shownSign = $signPracticeSession->signs()
            ->whereKey((int) $validated['road_sign_id'])
            ->first();

        if (! $shownSign) {
            abort(422, 'This question does not belong to the selected session.');
        }

        $selectedSign = isset($validated['selected_road_sign_id'])
            ? RoadSign::query()->find($validated['selected_road_sign_id'])
            : null;

        $signPracticeSession->answer(
            shownSign: $shownSign,
            selectedSign: $selectedSign,
            timeTakenSeconds: isset($validated['time_taken_seconds']) ? (int) $validated['time_taken_seconds'] : null,
        );

        $answersCount = $signPracticeSession->answers()->count();

        if ($answersCount >= $signPracticeSession->total_questions) {
            $completedSession = $signPracticeSession->complete();

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => __('Session completed with :score%.', ['score' => $completedSession->score_percent]),
            ]);

            return to_route('practice.index');
        }

        return to_route('practice.index', ['session' => $signPracticeSession->id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(SignPracticeAnswer $signPracticeAnswer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SignPracticeAnswer $signPracticeAnswer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSignPracticeAnswerRequest $request, SignPracticeAnswer $signPracticeAnswer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SignPracticeAnswer $signPracticeAnswer)
    {
        //
    }
}
