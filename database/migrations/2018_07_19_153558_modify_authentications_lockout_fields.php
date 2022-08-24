<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyAuthenticationsLockoutFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('authentications', function (Blueprint $table) {
            //
            $table->datetime('lock_out')->nullable()->after('meta_data');
            $table->datetime('lock_in')->nullable()->after('meta_data');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('authentications', function (Blueprint $table) {
            //
            $table->dropColumn('lock_in');
            $table->dropColumn('lock_out');
        });
    }
}
