<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherProfileEditRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'teacher_profile_id',
        'requested_by_user_id',
        'requested_data',
        'profile_photo_path',
        'status',
        'active_key',
        'admin_note',
        'reviewed_by_user_id',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_data' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(
            TeacherProfile::class
        );
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'requested_by_user_id'
        );
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by_user_id'
        );
    }

    public function isPending(): bool
    {
        return $this->status ===
            self::STATUS_PENDING;
    }
}