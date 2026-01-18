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
        Schema::create('form_submission_outbox', function (Blueprint $table) {
            // Outbox PK
            $table->bigIncrements('id');

            // 1:1 link to form_submissions.id
            $table->unsignedInteger('form_submission_id')->unique();

            // Lifecycle
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();

            // Optional but useful for visibility if something fails
            $table->text('last_error')->nullable();

            // FK constraint
            $table->foreign('form_submission_id')
                ->references('id')
                ->on('form_submissions')
                ->onDelete('cascade');

            // Helps the poller
            $table->index(['processed_at', 'created_at'], 'form_submission_outbox_poll_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('form_submission_outbox');
    }
};
