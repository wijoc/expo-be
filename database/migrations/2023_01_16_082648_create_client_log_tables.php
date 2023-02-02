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
        Schema::create('client_search_product_log', function (Blueprint $table) {
            $table->id();
            $table->ipAddress('client_ip');
            $table->integer('user_id')->nullable();
            $table->text('product_id');
            $table->string('page');
            $table->string('keyword');
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestampTz('updated_at', $precision = 0)->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('client_search_store_log', function (Blueprint $table) {
            $table->id();
            $table->ipAddress('client_ip')->nullable();
            $table->integer('user_id')->nullable();
            $table->text('store_id');
            $table->string('page')->nullable();
            $table->string('keyword')->nullable();
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestampTz('updated_at', $precision = 0)->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('client_keyword_log', function (Blueprint $table) {
            $table->id();
            $table->ipAddress('client_ip')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('keyword');
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestampTz('updated_at', $precision = 0)->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_search_product_log');
        Schema::dropIfExists('client_search_store_log');
        Schema::dropIfExists('client_keyword_log');
    }
}
