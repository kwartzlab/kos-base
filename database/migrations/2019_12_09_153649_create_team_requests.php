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
        Schema::create('team_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('request_type', 50);
            $table->string('status', 50);
            $table->integer('user_id');
            $table->integer('team_id');
            $table->integer('gatekeeper_id')->default('0');
            $table->text('message')->nullable();
            $table->text('response')->nullable();
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
        Schema::dropIfExists('team_requests');
    }
};
