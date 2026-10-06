<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TuitionApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'tuition_post_id',
        'teacher_profile_id',
        'message',
        'expected_salary',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'expected_salary' => 'decimal:2',
        ];
    }

    public function tuitionPost(): BelongsTo
    {
        return $this->belongsTo(TuitionPost::class);
    }

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class);
    }
}