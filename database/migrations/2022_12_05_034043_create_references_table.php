<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ref_province', function (Blueprint $table) {
            $table->id();
            $table->string('name', 225);
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('ref_city', function (Blueprint $table) {
            $table->id();
            $table->string('name', 225);
            $table->integer('ro_api_code');
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreignId('province_id')->constrained('ref_province')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('ref_district', function (Blueprint $table) {
            $table->id();
            $table->string('name', 225);
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreignId('city_id')->constrained('ref_city')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('ref_ecommerce', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('ref_delivery_courier', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('ro_api_param', 50);
            $table->string('created_tz')->default('SYSTEM');
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_tz')->default('SYSTEM');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ref_province');
        Schema::dropIfExists('ref_city');
        Schema::dropIfExists('ref_district');
        Schema::dropIfExists('ref_ecommerce');
        Schema::dropIfExists('ref_delivery_courier');
    }
}
