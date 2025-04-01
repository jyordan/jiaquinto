<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('conversion_keys', function (Blueprint $table) {
            // Rename ghl_pipeline_stage_id to ghl_pipeline_stage_target_id
            $table->renameColumn('ghl_pipeline_stage_id', 'ghl_pipeline_stage_target_id');

            // Add new column ghl_pipeline_stage_source_id
            $table->string('ghl_pipeline_stage_source_id')->after('ghl_pipeline_id');
        });
    }

    public function down()
    {
        Schema::table('conversion_keys', function (Blueprint $table) {
            // Rollback column name change
            $table->renameColumn('ghl_pipeline_stage_target_id', 'ghl_pipeline_stage_id');

            // Remove the newly added column
            $table->dropColumn('ghl_pipeline_stage_source_id');
        });
    }
};
