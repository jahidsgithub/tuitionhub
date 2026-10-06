<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_user_id',
        'teacher_profile_id',
        'tuition_post_id',
        'message',
        'status',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'student_user_id'
        );
    }

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(
            TeacherProfile::class
        );
    }

    public function tuitionPost(): BelongsTo
    {
        return $this->belongsTo(
            TuitionPost::class
        );
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }
}