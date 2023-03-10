<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code', 50);
            $table->timestamp('transaction_date');
            $table->decimal('disc_percent', $precision = 3, $scale = 2)->nullable()->default(0);
            $table->decimal('disc_price', $precision = 10, $scale = 2)->nullable()->default(0);
            $table->string('delivery_method');
            $table->decimal('delivery_fee', $precision = 10, $scale = 2)->nullable()->default(0);
            $table->decimal('total_payment', $precision = 10, $scale = 2);
            $table->string('payment_method');
            $table->integer('payment_status')->nullable()->default(0);
            $table->timestamp('due_date')->default(NOW());
            $table->integer('return_status')->nullable()->default(0);
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreignId('user_id')->constrained('tb_user')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('delivery_service_id')->constrained('ref_delivery_service')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('transaction_item', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('qty');
            $table->decimal('initial_price', $precision = 10, $scale = 2)->default(0);
            $table->decimal('net_price', $precision = 10, $scale = 2)->default(0);
            $table->decimal('disc_percent', $precision = 3, $scale = 2)->nullable()->default(0);
            $table->decimal('disc_price', $precision = 10, $scale = 2)->nullable()->default(0);
            $table->decimal('product_price', $precision = 10, $scale = 2);
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreignId('transaction_id')->constrained('transaction')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('product')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('payment_proof', function (Blueprint $table) {
            $table->id();
            $table->string('path', 225);
            $table->string('mime', 225);
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreignId('transaction_id')->constrained('transaction')->onUpdate('cascade')->onDelete('cascade');
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
