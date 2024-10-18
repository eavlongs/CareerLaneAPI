<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\JobTypeEnum;
use App\Enums\LocationEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        "type" => JobTypeEnum::class,
        "location" => LocationEnum::class
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}