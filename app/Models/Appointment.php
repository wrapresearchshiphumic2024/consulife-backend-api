<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'psychologist_id',
        'date',
        'time',
        'status',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function psychologist()
    {
        return $this->belongsTo(Psychologist::class);
    }
}
