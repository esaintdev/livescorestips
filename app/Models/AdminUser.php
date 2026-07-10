<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class AdminUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    // Automatically hash password when setting (skip if already hashed)
    public function setPasswordAttribute($value)
    {
        // Avoid double-hashing if Hash::make() was already called before assignment
        if (!str_starts_with($value, '$2y$') && !str_starts_with($value, '$2a$')) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }
}
