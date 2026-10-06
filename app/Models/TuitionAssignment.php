<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TuitionAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tuition_post_id',
        'teacher_profile_id',
        'student_user_id',
        'source',
        'tuition_application_id',
        'teacher_request_id',
        'status',
        'assigned_at',
        'completed_at',
        'cancelled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function tuitionPost(): BelongsTo
    {
        return $this->belongsTo(
            TuitionPost::class
        );
    }

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(
            TeacherProfile::class
        );
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'student_user_id'
        );
    }

    public function tuitionApplication(): BelongsTo
    {
        return $this->belongsTo(
            TuitionApplication::class
        );
    }

    public function teacherRequest(): BelongsTo
    {
        return $this->belongsTo(
            TeacherRequest::class
        );
    }

    public function review(): HasOne
    {
        return $this->hasOne(
            TeacherReview::class
        );
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}