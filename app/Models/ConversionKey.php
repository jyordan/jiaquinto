<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversionKey extends Model
{
    use HasFactory;

    protected $table = 'conversion_keys'; // Define table name

    protected $fillable = [
        'cliniko_api_key',
        'ghl_api_key',
        'cliniko_app_type_id',
        'ghl_pipeline_id',
        'ghl_pipeline_stage_id',
        'starts_at',
        'ends_at',
    ];

    protected $dates = ['starts_at', 'ends_at'];
}
