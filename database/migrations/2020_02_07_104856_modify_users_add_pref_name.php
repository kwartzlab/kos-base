<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUsersAddPrefName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_preferred')->nullable()->after('last_name');
            $table->string('last_preferred')->nullable()->after('first_preferred');
            $table->string('google_account')->nullable()->after('postal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('first_preferred');
            $table->dropColumn('last_preferred');
            $table->dropColumn('google_account');
        });
    }
}
