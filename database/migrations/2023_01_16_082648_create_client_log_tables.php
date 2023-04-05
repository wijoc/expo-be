<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientLogTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_keyword_log', function (Blueprint $table) {
            $table->id();
            $table->ipAddress('client_ip')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('keyword');
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_keyword_log');
    }
}
