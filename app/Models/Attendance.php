<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;


    protected $table = "attendances";

    protected $fillable = [
        'state',
        'student_id',
        'lecture_id',
        'week_no'
    ];

    protected $hidden = [
        'updated_at',
        'created_at'
    ];

    public function lecture()
    {
        return $this->belongsTo(Lecture::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
