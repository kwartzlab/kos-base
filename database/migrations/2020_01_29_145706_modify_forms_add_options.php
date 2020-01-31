<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyFormsAddOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->string('submit_label')->nullable()->after('special_form');
            $table->string('submitted_label')->nullable()->after('submit_label');
            $table->text('submit_options')->nullable()->after('submitted_label');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('submit_label');
            $table->dropColumn('submitted_label');
            $table->dropColumn('submit_options');
        });
    }
}
