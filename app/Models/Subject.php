<?php

namespace App\Models;

use App\Models\Period as ModelsPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Period;
use App\Models\Lecture;

class Subject extends Model
{
    use HasFactory;
    protected $fillable = [
        'subject_name'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function period()
    {
        return $this->belongsToMany(Period::class);
    }



    public function lectures()
    {
        return $this->hasMany(Lecture::class);
    }
}
