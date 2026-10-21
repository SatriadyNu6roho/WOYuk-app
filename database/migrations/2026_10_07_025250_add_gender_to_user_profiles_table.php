<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGenderToUserProfilesTable extends Migration
{
    public function up()
{
    if (!Schema::hasColumn('user_profiles', 'gender')) {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('gender', 10)->nullable()->after('height_cm');
        });
    }
}

    public function down()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn('gender');
        });
    }
}