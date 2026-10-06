<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TuitionPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'tuition_code',
        'user_id',
        'location_id',
        'title',
        'class_level',
        'medium',
        'student_gender',
        'preferred_teacher_gender',
        'days_per_week',
        'salary',
        'teaching_mode',
        'requirements',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'days_per_week' => 'integer',
            'salary' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(
            Location::class
        );
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'subject_tuition_post'
        )->withTimestamps();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(
            TuitionApplication::class
        );
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(
            TuitionAssignment::class
        );
    }
}