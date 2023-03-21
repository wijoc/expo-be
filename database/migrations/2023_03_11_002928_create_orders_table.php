<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trans_order', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 225)->unique();
            $table->timestamp('order_date');
            $table->string('order_status', 10);
            $table->decimal('disc_percent', $precision = 3, $scale = 2)->nullable()->default(0);
            $table->decimal('disc_price', $precision = 10, $scale = 2)->nullable()->default(0);
            $table->string('delivery_method');
            $table->string('delivery_service', 50)->nullable();
            $table->string('delivery_etd', 225)->nullable();
            $table->string('delivery_note', 225)->nullable();
            $table->decimal('delivery_fee', $precision = 10, $scale = 2)->nullable()->default(0);
            $table->string('tracking_number', 225)->nullable();
            $table->integer('total_weight_g');
            $table->decimal('total_cart', $precision = 10, $scale = 2);
            $table->decimal('total_payment', $precision = 10, $scale = 2);
            $table->string('payment_method');
            $table->integer('payment_status')->nullable()->default(0);
            $table->timestamp('payment_date')->nullable();
            $table->timestamp('due_date')->default(NOW());
            $table->integer('return_status')->nullable()->default(0);
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('return_tz')->nullable();
            $table->timestamp('return_at')->nullable();

            $table->foreignId('store_id')->constrained('store');
            $table->foreignId('user_id')->constrained('tb_user')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('user_address_id')->constrained('user_address')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('delivery_courier_id')->constrained('ref_delivery_courier')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('order_item', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 225);
            $table->unsignedInteger('qty');
            $table->uuid('product_uuid');
            $table->decimal('initial_price', $precision = 10, $scale = 2)->default(0);
            $table->decimal('net_price', $precision = 10, $scale = 2)->default(0);
            $table->decimal('disc_percent', $precision = 5, $scale = 2)->nullable();
            $table->decimal('disc_price', $precision = 13, $scale = 2)->nullable();
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('order_code')->references('order_code')->on('trans_order')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('product_uuid')->references('product_uuid')->on('product')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('payment_proof', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 225);
            $table->string('path', 225);
            $table->string('mime', 225);
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('order_code')->references('order_code')->on('trans_order')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trans_order');
        Schema::dropIfExists('order_item');
        Schema::dropIfExists('payment_proof');
    }
}
