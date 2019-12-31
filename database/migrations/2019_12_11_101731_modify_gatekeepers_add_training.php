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
            $table->string('auth_expiry_type', 50)->default('revoke')->after('auth_expires');
            $table->text('training_desc')->nullable()->after('team_id');
            $table->string('training_eta', 80)->nullable()->after('training_desc');
            $table->integer('training_prereq')->default(0)->after('training_eta');

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
            $table->dropColumn('auth_expiry_type');
            $table->dropColumn('training_desc');
            $table->dropColumn('training_eta');
            $table->dropColumn('training_prereq');
        });
    }
}
