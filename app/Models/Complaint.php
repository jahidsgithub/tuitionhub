<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'tuition_assignment_id',
        'reported_by',
        'reported_user_id',
        'category',
        'subject',
        'description',
        'status',
        'unresolved_guard',
        'admin_note',
        'handled_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' =>
                'datetime',

            'unresolved_guard' =>
                'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Complaint Guard Invariant
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saving(
            function (
                Complaint $complaint
            ) {
                if (
                    in_array(
                        $complaint->status,
                        [
                            'open',
                            'investigating',
                        ],
                        true
                    )
                ) {
                    $complaint
                        ->unresolved_guard = 1;
                } else {
                    $complaint
                        ->unresolved_guard = null;
                }
            }
        );
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(
            TuitionAssignment::class,
            'tuition_assignment_id'
        );
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reported_by'
        );
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reported_user_id'
        );
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'handled_by'
        );
    }

    public function isUnresolved(): bool
    {
        return in_array(
            $this->status,
            [
                'open',
                'investigating',
            ],
            true
        );
    }

    public function isResolved(): bool
    {
        return in_array(
            $this->status,
            [
                'resolved',
                'rejected',
            ],
            true
        );
    }
}