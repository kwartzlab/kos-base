<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyGatekeepersAddTraining extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gatekeepers', function (Blueprint $table) {
            $table->integer('auth_expires')->default(0)->after('auth_key');
            $table->boolean('is_trainable')->default(true)->after('team_id');
            $table->text('training_desc')->nullable()->after('is_trainable');
            $table->integer('training_prereq')->default(0)->after('training_desc');
            $table->string('training_eta', 50)->nullable()->after('training_prereq');
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
            $table->dropColumn('auth_expires');
            $table->dropColumn('is_trainable');
            $table->dropColumn('training_desc');
            $table->dropColumn('training_prereq');
            $table->dropColumn('training_eta');
        });
    }
}
