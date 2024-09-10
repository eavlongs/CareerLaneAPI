<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        "name",
        "description",
        "account_id",
        "links",
        "logo_url",
        "province_id"
    ];

    protected $casts = [
        "links" => "array"
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function job_posts(): HasMany
    {
        return $this->hasMany(JobPost::class);
    }
}