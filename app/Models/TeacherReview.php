<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'tuition_assignment_id',
        'teacher_profile_id',
        'student_user_id',
        'rating',
        'review',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function tuitionAssignment(): BelongsTo
    {
        return $this->belongsTo(
            TuitionAssignment::class,
            'tuition_assignment_id'
        );
    }

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(
            TeacherProfile::class,
            'teacher_profile_id'
        );
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'student_user_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function ratingLabel(): string
    {
        return match ($this->rating) {
            5 => 'Excellent',
            4 => 'Very Good',
            3 => 'Good',
            2 => 'Fair',
            1 => 'Poor',
            default => 'Not Rated',
        };
    }
}