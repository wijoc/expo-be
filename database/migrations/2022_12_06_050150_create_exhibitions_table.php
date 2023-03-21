<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExhibitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_category', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->integer('is_sub_category')->nullable()->default(0);
            $table->integer('parent_id')->nullable()->default(0);
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('store_category', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('store', function (Blueprint $table) {
            $table->id();
            $table->string('store_name', 50);
            $table->string('domain', 50);
            $table->string('email', 225)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->string('image_path', 225)->nullable();
            $table->string('image_mime', 225)->nullable();
            $table->text('description')->nullable();
            $table->longText('full_address');
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreignId('user_id')->constrained('tb_user')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('district_id')->constrained('ref_district')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('city_id')->constrained('ref_city')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('province_id')->constrained('ref_province')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('store_category')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('store_ecommerce', function (Blueprint $table) {
            $table->id();
            $table->string('ecommerce_name', 50);
            $table->string('ecommerce_link', 225);
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreignId('store_id')->constrained('store')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('ecommerce_id')->constrained('ref_ecommerce')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('store_delivery', function (Blueprint $table) {
            $table->id();
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreignId('store_id')->constrained('store')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('delivery_courier_id')->constrained('ref_delivery_courier')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('product', function (Blueprint $table) {
            $table->id();
            $table->uuid('product_uuid')->unique();
            $table->string('name', 50);
            $table->string('condition', 2);
            $table->decimal('initial_price', $precision = 13, $scale = 2);
            $table->decimal('net_price', $precision = 13, $scale = 2);
            $table->decimal('disc_percent', $precision = 5, $scale = 2)->nullable();
            $table->decimal('disc_price', $precision = 13, $scale = 2)->nullable();
            $table->integer('weight_g');
            $table->integer('min_purchase');
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreignId('store_id')->constrained('store')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('product_category')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('product_img', function (Blueprint $table) {
            $table->id();
            $table->uuid('product_uuid');
            $table->string('mime', 225);
            $table->string('path', 225);
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('product_uuid')->references('product_uuid')->on('product')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_category');
        Schema::dropIfExists('store_category');
        Schema::dropIfExists('store');
        Schema::dropIfExists('store_ecommerce');
        Schema::dropIfExists('product');
        Schema::dropIfExists('product_img');
    }
}
