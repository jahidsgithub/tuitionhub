<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function teacherProfile(): HasOne
    {
        return $this->hasOne(
            TeacherProfile::class
        );
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(
            StudentProfile::class
        );
    }

    public function tuitionPosts(): HasMany
    {
        return $this->hasMany(
            TuitionPost::class
        );
    }

    public function teacherRequests(): HasMany
    {
        return $this->hasMany(
            TeacherRequest::class,
            'student_user_id'
        );
    }

    public function tuitionAssignments(): HasMany
    {
        return $this->hasMany(
            TuitionAssignment::class,
            'student_user_id'
        );
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(
            Subscription::class
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            Payment::class
        );
    }

    public function activeSubscription(): ?Subscription
    {
        return $this
            ->subscriptions()
            ->with('plan')
            ->where(
                'status',
                'active'
            )
            ->where(
                function ($query) {
                    $query
                        ->whereNull(
                            'expires_at'
                        )
                        ->orWhere(
                            'expires_at',
                            '>',
                            now()
                        );
                }
            )
            ->latest(
                'expires_at'
            )
            ->first();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}