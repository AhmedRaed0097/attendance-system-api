<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $casts = [
        'master_table_id' => 'integer',
        'email_verified_at' => 'datetime',
    ];
    protected $fillable = [
        //        'name',
        'student_name',
        'master_table_id',
        'state',
        'email',
        'password',
    ];


    protected $table = 'students';


    protected $hidden = [
        'created_at',
        'email_verified_at',
        'password',
        'email_verified_at',
        'updated_at',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function lectures()
    {
        return $this->hasMany(Lecture::class);
    }
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function masterTable()
    {
        return $this->belongsTo(MasterTable::class);
    }
}
