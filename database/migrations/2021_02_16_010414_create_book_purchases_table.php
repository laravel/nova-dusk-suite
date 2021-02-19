<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('book_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id');
            $table->foreignId('user_id');
            $table->unsignedInteger('price');
            $table->string('type')->default('personal');
            $table->dateTime('purchased_at');
            $table->timestamps();
            //$table->softDeletes();

            $table->index(['book_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('book_purchases');
    }
}
