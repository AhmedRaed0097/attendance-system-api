<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Lecture;

class Period extends Model
{
    use HasFactory;

    // public function subject()
    // {
    //    return $this->belongsToMany(Subject::class);
    // }

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function lectures()
    {
        return $this->hasMany(Lecture::class);
    }
}
