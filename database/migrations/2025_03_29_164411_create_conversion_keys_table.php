<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('conversion_keys', function (Blueprint $table) {
            $table->id();
            $table->string('cliniko_api_key');
            $table->string('ghl_api_key');
            $table->string('cliniko_app_type_id');
            $table->string('ghl_pipeline_id');
            $table->string('ghl_pipeline_stage_id');
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversion_keys');
    }
};
