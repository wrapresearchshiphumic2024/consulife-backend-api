<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin_verification extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'status',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function psychologist()
    {
        return $this->belongsTo(Psychologist::class);
    }
}
