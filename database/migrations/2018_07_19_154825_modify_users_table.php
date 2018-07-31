<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('last_name')->after('id');
            $table->string('first_name')->after('id');
            $table->text('notes')->after('acl')->nullable();
            $table->string('postal')->after('acl');
            $table->string('province')->after('acl');
            $table->string('city')->after('acl');
            $table->string('address')->after('acl');
            $table->string('phone')->after('acl');
            $table->date('date_withdrawn')->after('acl')->nullable();
            $table->date('date_hiatus_end')->after('acl')->nullable();
            $table->date('date_hiatus_start')->after('acl')->nullable();
            $table->date('date_admitted')->after('acl')->nullable();
            $table->date('date_applied')->after('acl')->nullable();
            $table->dropColumn('name');

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
            //
        });
    }
}
