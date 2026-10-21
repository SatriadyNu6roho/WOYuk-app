<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id'); // Internal Primary Key
            $table->char('public_id', 10)->nullable()->unique()->index(); // Public ID: 0000000001
            $table->string('email', 191)->unique()->index();
            $table->string('password')->nullable(); // Nullable jika login via Google OAuth
            $table->string('google_id', 191)->nullable()->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}