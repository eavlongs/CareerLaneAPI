<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\ProviderEnum;

class Provider extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        "provider",
        "id_from_provider",
        "provider_account_profile"
    ];

    protected $casts = [
        "provider" => ProviderEnum::class
    ];
}
