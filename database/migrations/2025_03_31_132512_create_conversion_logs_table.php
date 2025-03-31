<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversion_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('conversion_id')->index();
            $table->string('source')->nullable();
            $table->string('opportunity_id')->nullable();
            $table->string('patient_id')->nullable();
            $table->string('patient_name')->nullable()->index();
            $table->string('patient_phone')->nullable()->index();
            $table->string('patient_email')->nullable()->index();
            $table->string('contact_name')->nullable()->index();
            $table->string('contact_phone')->nullable()->index();
            $table->string('contact_email')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversion_logs');
    }
};
