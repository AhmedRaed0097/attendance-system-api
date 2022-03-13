<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecture extends Model
{
    use HasFactory;

    protected $fillable = [
        //        'name',
        'subject_id',
        'period_id',
        'lecturer_id',
        'master_table_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
    public function period()
    {
        return $this->belongsTo(Period::class);
    }
    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }
    public function masterTable()
    {
        return $this->belongsTo(MasterTable::class);
    }
    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }
}
