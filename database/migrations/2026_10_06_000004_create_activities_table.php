<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->enum('sport_type', ['running', 'walking', 'cycling'])->index();
            $table->enum('status', ['active', 'completed', 'incomplete', 'cancelled'])->default('active')->index();
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->integer('duration_seconds')->unsigned()->default(0);
            $table->decimal('distance_meters', 10, 2)->default(0.00)->index(); // Presisi Meter murni
            $table->decimal('avg_speed_kmh', 5, 2)->default(0.00);
            $table->string('avg_pace_min_km', 10)->nullable(); // MM:SS /km
            $table->decimal('elevation_gain_m', 7, 2)->default(0.00);
            $table->integer('calories_burned')->unsigned()->default(0);
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
        Schema::dropIfExists('activities');
    }
}