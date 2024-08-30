<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPost extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        "title",
        "description",
        "original_deadline",
        "company_id",
        "category_id"
    ];
}
