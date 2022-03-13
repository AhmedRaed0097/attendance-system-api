<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Lecture;
use App\Models\Subject;

class MasterTable extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function lectures()
    {
        return $this->hasMany(Lecture::class);
    }
    public function students()
    {
        return $this->hasMany(Student::class);
    }
    public function subjects()
    {
        return $this->hasManyThrough(Subject::class, Lecture::class);
    }
}
