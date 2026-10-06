<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'bkash_number',
        'bkash_account_type',
        'nagad_number',
        'nagad_account_type',
        'bkash_enabled',
        'nagad_enabled',
        'payment_instruction',
    ];

    protected function casts(): array
    {
        return [
            'bkash_enabled' => 'boolean',
            'nagad_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::firstOrCreate(
            [],
            [
                'bkash_enabled' => false,
                'nagad_enabled' => false,
            ]
        );
    }
}