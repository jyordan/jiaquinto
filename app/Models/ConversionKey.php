<?php

namespace App\Models;

use App\Modules\Api\ClinikoApi;
use App\Modules\Api\GoHighLevelApi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversionKey extends Model
{
    use HasFactory;

    protected $table = 'conversion_keys'; // Define table name

    protected $fillable = [
        'company_name',
        'cliniko_api_key',
        'ghl_api_key',
        'cliniko_app_type_id',
        'ghl_pipeline_id',
        'ghl_pipeline_stage_id',
        'active_at',
    ];

    protected $dates = ['active_at'];

    protected function activeAt(): Attribute
    {
        return Attribute::make(
            set: fn($value) => $value ? now() : null,
        );
    }

    public function conversionLogs()
    {
        return $this->hasMany(ConversionLog::class, 'conversion_id');
    }

    protected function ghlPipelineStageName(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                $pipeline = $this->fetchPipeline($attributes);
                $stage = collect(data_get($pipeline, 'stages', []))
                    ->where('id', $attributes['ghl_pipeline_stage_id'])
                    ->first();
                return data_get($stage ?: [], 'name', '');
            },
        );
    }

    protected function ghlPipelineName(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                $pipeline = $this->fetchPipeline($attributes);
                return data_get($pipeline, 'name', '');
            },
        );
    }

    protected function clinikoAppTypeName(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                if (!$attributes['cliniko_api_key'] || !$attributes['cliniko_app_type_id']) return;

                $cliniko = new ClinikoApi;
                $cliniko->setToken($attributes['cliniko_api_key']);
                $result = $cliniko->request('appointment_types');
                $appTypes = collect(data_get($result, 'appointment_types', []))
                    ->where('id', $attributes['cliniko_app_type_id'])
                    ->first();
                return data_get($appTypes, 'name', '');
            },
        );
    }

    protected function fetchPipeline(array $attributes): array
    {
        if (!$attributes['ghl_api_key'] || !$attributes['ghl_pipeline_id']) return [];

        $ghl = new GoHighLevelApi;
        $ghl->setToken($attributes['ghl_api_key']);
        $pipelines = $ghl->request('pipelines');
        $pipeline = collect(data_get($pipelines, 'pipelines', []))
            ->where('id', $attributes['ghl_pipeline_id'])
            ->first();
        return $pipeline ?: [];
    }

    /**
     * Scope a query to only include popular users.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active_at', '<=', now());
    }
}
