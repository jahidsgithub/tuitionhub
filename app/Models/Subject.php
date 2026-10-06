<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(
            TeacherProfile::class,
            'subject_teacher_profile'
        )->withTimestamps();
    }

    public function tuitionPosts(): BelongsToMany
    {
        return $this->belongsToMany(
            TuitionPost::class,
            'subject_tuition_post'
        )->withTimestamps();
    }
}