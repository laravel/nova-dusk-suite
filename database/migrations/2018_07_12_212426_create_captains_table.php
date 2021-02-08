<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaptainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('captains', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('photo');
            $table->timestamps();
        });

        Schema::create('captain_ship', function (Blueprint $table) {
            $table->foreignId('captain_id');
            $table->foreignId('ship_id');
            $table->string('notes')->nullable();
            $table->string('contract')->nullable();

            $table->index(['captain_id', 'ship_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('captains');
        Schema::dropIfExists('captain_ship');
    }
}
