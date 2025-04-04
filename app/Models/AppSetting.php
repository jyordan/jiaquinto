<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $table = 'app_settings'; // Specify table name (optional if follows convention)

    protected $fillable = ['key', 'value']; // Mass assignment fields
}
