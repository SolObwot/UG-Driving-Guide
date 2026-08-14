<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoadSignController;
use App\Http\Controllers\SignPracticeAnswerController;
use App\Http\Controllers\SignPracticeSessionController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('road-signs', RoadSignController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('practice', [SignPracticeSessionController::class, 'index'])->name('practice.index');
    Route::get('practice/history', [SignPracticeSessionController::class, 'history'])->name('practice.history');
    Route::post('practice/sessions', [SignPracticeSessionController::class, 'store'])->name('practice.sessions.store');
    Route::post('practice/sessions/{signPracticeSession}/answers', [SignPracticeAnswerController::class, 'store'])->name('practice.sessions.answers.store');
});

require __DIR__.'/settings.php';
