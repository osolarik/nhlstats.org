<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddYearToPlayoffTeams extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('playoff_teams', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->smallInteger('year');
            $table->index(['year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('playoff_teams', function (Blueprint $table) {
            $table->dropColumn('year');
        });
    }
}
