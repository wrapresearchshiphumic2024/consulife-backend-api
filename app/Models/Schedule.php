<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'psychologist_id',
        'days',
        'time',
    ];

    public function psychologist()
    {
        return $this->belongsTo(Psychologist::class);
    }
}
