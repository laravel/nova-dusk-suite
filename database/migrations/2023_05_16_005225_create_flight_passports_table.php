<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('flight_passports', function (Blueprint $table) {
            $table->foreignId('flight_id');
            $table->foreignId('passport_id');
            $table->timestamps();

            $table->index(['flight_id', 'passport_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('flight_passports');
    }
};
