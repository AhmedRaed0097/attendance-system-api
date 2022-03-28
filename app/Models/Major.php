<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    use HasFactory;

    protected $fillable = [
        // 'name',
        'major',
        'levels'
    ];


    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
