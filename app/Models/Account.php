<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        "email",
        "password"
    ];

    protected $hidden = [
        "password"
    ];

    public function accountProviders()
    {
        return $this->hasMany(AccountProvider::class);
    }
}
