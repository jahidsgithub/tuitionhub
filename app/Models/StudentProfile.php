<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_photo',
        'gender',
        'guardian_name',
        'guardian_phone',
        'class_level',
        'medium',
        'address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    /**
     * Minimum profile requirements for marketplace actions.
     */
    public function isCompleteForMarketplace(): bool
    {
        return empty(
            $this->missingRequiredFields()
        );
    }

    /**
     * Get missing required fields.
     */
    public function missingRequiredFields(): array
    {
        $missing = [];

        if (! $this->gender) {
            $missing[] = 'gender';
        }

        if (! $this->class_level) {
            $missing[] = 'class / level';
        }

        if (! $this->medium) {
            $missing[] = 'medium';
        }

        if (! $this->address) {
            $missing[] = 'address';
        }

        return $missing;
    }
}