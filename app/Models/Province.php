<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ["name"];

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }
}