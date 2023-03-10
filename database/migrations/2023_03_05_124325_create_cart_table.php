<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart', function (Blueprint $table) {
            $table->id();
            $table->uuid('product_uuid');
            $table->integer('product_qty');
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreignId('user_id')->constrained('tb_user')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('product_uuid')->references('product_uuid')->on('product')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('store_id')->constrained('store')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart');
    }
}
