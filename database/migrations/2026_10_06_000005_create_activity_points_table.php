<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_points', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('activity_id');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('altitude', 7, 2)->nullable();
            $table->decimal('speed_ms', 5, 2)->nullable(); // meter per detik dari browser
            $table->decimal('accuracy_m', 6, 2)->nullable(); // tingkat akurasi sinyal GPS
            $table->timestamp('recorded_at', 3); // Presisi milidetik
            
            // Index Komposit untuk mempercepat rendering rute peta
            $table->index(['activity_id', 'recorded_at']);

            // Relasi Foreign Key
            $table->foreign('activity_id')
                  ->references('id')
                  ->on('activities')
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
        Schema::dropIfExists('activity_points');
    }
}