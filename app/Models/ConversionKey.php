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
        'ghl_pipeline_stage_source_id',
        'ghl_pipeline_stage_target_id',
        'active_at',
    ];

    protected $dates = ['active_at'];

    protected function activeAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => !!$value,
            set: fn($value) => $value ? now() : null,
        );
    }

    public function conversionLogs()
    {
        return $this->hasMany(ConversionLog::class, 'conversion_id');
    }

    protected function ghlPipelineStageSourceName(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                $pipeline = $this->fetchPipeline($attributes);
                $stage = collect(data_get($pipeline, 'stages', []))
                    ->where('id', $attributes['ghl_pipeline_stage_source_id'])
                    ->first();
                return data_get($stage ?: [], 'name', '');
            },
        );
    }

    protected function ghlPipelineStageSourceCount(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                $opportunities = $this->fetchOpportunities($attributes, 'ghl_pipeline_stage_source_id');
                return count($opportunities);
            },
        );
    }

    protected function ghlPipelineStageTargetCount(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                $opportunities = $this->fetchOpportunities($attributes, 'ghl_pipeline_stage_target_id');
                return count($opportunities);
            },
        );
    }

    protected function ghlPipelineStageTargetName(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                $pipeline = $this->fetchPipeline($attributes);
                $stage = collect(data_get($pipeline, 'stages', []))
                    ->where('id', $attributes['ghl_pipeline_stage_target_id'])
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

                $cliniko = $this->getClinikoApi($attributes['cliniko_api_key']);
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

        $ghl = $this->getGoHighLevelApi($attributes['ghl_api_key']);
        $pipelines = $ghl->request('pipelines');
        $pipeline = collect(data_get($pipelines, 'pipelines', []))
            ->where('id', $attributes['ghl_pipeline_id'])
            ->first();
        return $pipeline ?: [];
    }

    protected function fetchOpportunities(array $attributes, string $stageIdKey): array
    {
        $stageId = $attributes[$stageIdKey];
        if (!$attributes['ghl_api_key'] || !$attributes['ghl_pipeline_id'] || !$stageId) return [];

        $ghl = $this->getGoHighLevelApi($attributes['ghl_api_key']);
        $pipelineId = $attributes['ghl_pipeline_id'];
        $opportunities = $ghl->request('pipelines/' . $pipelineId . '/opportunities', compact('stageId'));

        return collect(data_get($opportunities, 'opportunities', []))
            ->toArray();
    }

    /**
     * Scope a query to only include popular users.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('active_at', '<=', now());
    }

    public function getClinikoApi($key = null): ClinikoApi
    {
        $cliniko = new ClinikoApi;
        $cliniko->setToken($key ?: $this->attributes['cliniko_api_key']);
        return $cliniko;
    }

    public function getGoHighLevelApi($key = null): GoHighLevelApi
    {
        $cliniko = new GoHighLevelApi;
        $cliniko->setToken($key ?: $this->attributes['ghl_api_key']);
        return $cliniko;
    }
}
