<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use  HasUuids, HasApiTokens, HasFactory, Notifiable;

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function psychologist()
    {
        return $this->hasOne(Psychologist::class, 'user_id', 'id');
    }

    public function isPsychologist()
    {
        return $this->role === 'psychologist';
    }

    public function isPatient()
    {
        return $this->role === 'patient';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
