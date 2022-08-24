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
        Schema::table('gatekeepers', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('training_prereq');
            $table->integer('shared_auth')->default(0)->after('auth_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gatekeepers', function (Blueprint $table) {
            $table->dropColumn('photo');
            $table->dropColumn('shared_auth');
        });
    }
};
