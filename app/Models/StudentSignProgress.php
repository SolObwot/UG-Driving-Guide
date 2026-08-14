<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSignProgress extends Model
{
    /** @use HasFactory<\Database\Factories\StudentSignProgressFactory> */
    use HasFactory;
    protected $table = 'student_sign_progress';

    protected $fillable = [
        'user_id',
        'sign_type',
        'signs_attempted',
        'signs_correct',
        'accuracy_percent',
        'mastery_level',
        'last_practiced_at',
    ];

    protected $casts = ['last_practiced_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
