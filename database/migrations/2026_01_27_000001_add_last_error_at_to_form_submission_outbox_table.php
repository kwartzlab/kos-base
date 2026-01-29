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
        Schema::table('form_submission_outbox', function (Blueprint $table) {
            // Track when the last error occurred
            $table->timestamp('last_error_at')->nullable()->after('last_error');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_submission_outbox', function (Blueprint $table) {
            $table->dropColumn('last_error_at');
        });
    }
};
