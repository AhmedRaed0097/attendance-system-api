<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Lecturer extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;


    protected $fillable = [
        'name',
        'email',
        'password',
        'state'
    ];

    protected $table = 'lecturers';

    protected $hidden = [
        'created_at',
        'updated_at',
        'email_verified_at',
        'password',

    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
    public function lectures()
    {
        return $this->hasMany(Lecture::class);
    }
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
