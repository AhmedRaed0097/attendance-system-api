<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Lecturer extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'email',
        'password',
        'state'
    ];

    protected $table = 'lecturers';

    protected $hidden = [
        'created_at',
        'email_verified_at',
        'password',
        'email_verified_at',
        'updated_at',

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
