<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Time extends Model
{
    use HasFactory;

    protected $fillable = ['day_id', 'start', 'end', 'status'];

    public function day()
    {
        return $this->belongsTo(Day::class);
    }
}
