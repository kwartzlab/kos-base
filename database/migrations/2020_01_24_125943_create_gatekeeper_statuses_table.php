<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGatekeeperStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gatekeeper_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('gatekeeper_id');
            $table->string('status',50)->default('online');
            $table->string('status_text')->nullable();
            $table->integer('user_id')->nullable();
            $table->datetime('lock_in')->nullable();
            $table->datetime('last_seen')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gatekeeper_statuses');
    }
}
