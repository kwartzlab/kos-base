<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyFormSubmissionsIdentifiers extends Migration
{
    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            //
            $table->ipAddress('submitter_ip')->after('submitted_by');
            $table->string('submitter_agent')->after('submitter_ip');
            $table->string('special_form')->nullable()->after('form_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropColumn('submitter_ip');
            $table->dropColumn('submitter_agent');
            $table->dropColumn('special_form');
        });
    }
}
