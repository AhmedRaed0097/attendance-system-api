<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $casts = [
        'master_table_id' => 'integer',
    ];
    protected $fillable = [
        //        'name',
        'student_name',
        'master_table_id',
        'state',
        'email',
    ];
    protected $hidden = [
        'created_at',
        'email_verified_at',
        'updated_at'
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
