<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastActivityDateToStreaksTable extends Migration
{
    public function up()
    {
        Schema::table('streaks', function (Blueprint $table) {
            $table->date('last_activity_date')->nullable()->after('longest_streak');
        });
    }

    public function down()
    {
        Schema::table('streaks', function (Blueprint $table) {
            $table->dropColumn('last_activity_date');
        });
    }
}