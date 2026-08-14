<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignPracticeAnswer extends Model
{
    /** @use HasFactory<\Database\Factories\SignPracticeAnswerFactory> */
    use HasFactory;

    protected $fillable = [
        'session_id',
        'road_sign_id',
        'selected_road_sign_id',
        'is_correct',
        'time_taken_seconds',
    ];

    protected $casts = ['is_correct' => 'boolean'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(SignPracticeSession::class, 'session_id');
    }

    // The sign that was actually shown to the student.
    public function roadSign(): BelongsTo
    {
        return $this->belongsTo(RoadSign::class, 'road_sign_id');
    }

    // The sign name the student picked as their answer.
    public function selectedSign(): BelongsTo
    {
        return $this->belongsTo(RoadSign::class, 'selected_road_sign_id');
    }
}
