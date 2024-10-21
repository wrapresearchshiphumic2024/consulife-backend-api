<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    use HasFactory;

    protected $fillable = ['day', 'status', 'schedule_id'];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
