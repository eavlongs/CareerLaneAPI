<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerifyToken extends Model
{
    use HasFactory;

    protected $fillable = [
        "token",
        "account_id",
        "expires_at",
    ];
}
