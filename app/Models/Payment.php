<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_id',
        'transaction_id',
        'payment_method',
        'amount',
        'currency',
        'status',
        'gateway_response',
        'paid_at',
        'pending_subscription_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' =>
                'decimal:2',

            'gateway_response' =>
                'array',

            'paid_at' =>
                'datetime',

            'pending_subscription_id' =>
                'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Model Guard Invariants
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saving(
            function (
                Payment $payment
            ) {
                /*
                |--------------------------------------------------------------------------
                | Pending Payment Guard
                |--------------------------------------------------------------------------
                |
                | A subscription may have only one pending payment.
                |
                | The DB UNIQUE index on pending_subscription_id is the final
                | concurrency protection.
                |
                */

                if (
                    $payment->status ===
                        'pending' &&
                    $payment
                        ->subscription_id !==
                        null
                ) {
                    $payment
                        ->pending_subscription_id =
                        $payment
                            ->subscription_id;
                } else {
                    $payment
                        ->pending_subscription_id =
                        null;
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(
            Subscription::class
        );
    }
}