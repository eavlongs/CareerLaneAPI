<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountProvider extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        "provider_id",
        "account_id"
    ];
}
