<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('conversion_logs', function (Blueprint $table) {
            $table->string('appointment_id')->after('id'); // or use ->unsignedBigInteger() if it's a numeric ID
            $table->string('contact_id')->after('appointment_id'); // or use ->unsignedBigInteger() if it's a numeric ID
        });
    }

    public function down()
    {
        Schema::table('conversion_logs', function (Blueprint $table) {
            $table->dropColumn('appointment_id');
            $table->dropColumn('contact_id');
        });
    }
};
