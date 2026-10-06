<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_photo',
        'gender',
        'university',
        'department',
        'degree',
        'experience_years',
        'bio',
        'expected_salary_min',
        'expected_salary_max',
        'teaching_mode',
        'is_verified',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
            'expected_salary_min' => 'decimal:2',
            'expected_salary_max' => 'decimal:2',
            'is_verified' => 'boolean',
            'is_available' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'subject_teacher_profile'
        )->withTimestamps();
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(
            Location::class,
            'location_teacher_profile'
        )->withTimestamps();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(
            TuitionApplication::class
        );
    }

    public function teacherRequests(): HasMany
    {
        return $this->hasMany(
            TeacherRequest::class
        );
    }

    public function verificationDocuments(): HasMany
    {
        return $this->hasMany(
            TeacherVerificationDocument::class
        );
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(
            TuitionAssignment::class
        );
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(
            TeacherReview::class
        );
    }

    public function hasApprovedVerificationDocument(): bool
    {
        return $this
            ->verificationDocuments()
            ->where(
                'status',
                'approved'
            )
            ->exists();
    }

    public function isCompleteForVerification(): bool
    {
        return empty(
            $this->missingVerificationFields()
        );
    }

    public function missingVerificationFields(): array
    {
        $missing = [];

        if (! $this->profile_photo) {
            $missing[] = 'profile photo';
        }

        if (! $this->gender) {
            $missing[] = 'gender';
        }

        if (! $this->university) {
            $missing[] = 'university / institution';
        }

        if (! $this->department) {
            $missing[] = 'department';
        }

        if (! $this->teaching_mode) {
            $missing[] = 'teaching mode';
        }

        if (
            ! $this
                ->subjects()
                ->exists()
        ) {
            $missing[] = 'at least one subject';
        }

        if (
            in_array(
                $this->teaching_mode,
                [
                    'offline',
                    'both',
                ],
                true
            )
            &&
            ! $this
                ->locations()
                ->exists()
        ) {
            $missing[] = 'at least one teaching location';
        }

        return $missing;
    }

    public function averageRating(): float
    {
        return round(
            (float) (
                $this
                    ->reviews()
                    ->avg('rating') ?? 0
            ),
            1
        );
    }

    public function completedAssignmentsCount(): int
    {
        return $this
            ->assignments()
            ->where(
                'status',
                'completed'
            )
            ->count();
    }
}