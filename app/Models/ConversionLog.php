<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'opportunity_id',
        'patient_id',
        'contact_id',
        'source',
        'patient_name',
        'patient_phone',
        'patient_email',
        'contact_name',
        'contact_phone',
        'contact_email',
    ];
}
