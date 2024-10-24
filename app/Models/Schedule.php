<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'psychologist_id',
        'status'
    ];

    public function psychologist()
    {
        return $this->belongsTo(Psychologist::class);
    }

    public function days()
    {
        return $this->hasMany(Day::class);
    }
}
