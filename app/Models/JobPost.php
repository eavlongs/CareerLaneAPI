<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\JobTypeEnum;
use App\Enums\LocationEnum;

class JobPost extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'description',
        'location',
        'type',
        'salary',
        'salary_start_range',
        'salary_end_range',
        'is_salary_negotiable',
        'original_deadline',
        'extended_deadline',
        'applicants',
        'is_active',
        'company_id',
        'category_id',
    ];

    protected $casts = [
        "type" => JobTypeEnum::class,
        "location" => LocationEnum::class
    ];
}
