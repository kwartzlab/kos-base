<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('team_assignments', function (Blueprint $table) {
            $table->integer('gatekeeper_id')->default(0)->after('team_id');
            $table->string('status', 50)->default('active')->after('team_role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('team_assignments', function (Blueprint $table) {
            $table->dropColumn('gatekeeper_id');
            $table->dropColumn('status');
        });
    }
};
