<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('streaks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unique(); // 1 record per user
            $table->integer('current_streak')->unsigned()->default(0);
            $table->integer('longest_streak')->unsigned()->default(0);
            $table->date('last_qualifying_date')->nullable(); // Tanggal lokal terakhir yang lolos validasi >= 50m
            $table->timestamps();

            // Relasi Foreign Key
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('streaks');
    }
}