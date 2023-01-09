<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_user', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('email', 225)->unique()->nullable();
            $table->string('email_prefix', 225)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('phone', 225)->unique()->nullable();
            $table->string('phone_prefix', 13)->nullable();
            $table->string('password', 225);
            $table->string('image_path', 225)->nullable();
            $table->string('role', 5);
            $table->timestamps();
        });

        Schema::create('user_address', function (Blueprint $table) {
            $table->id();
            $table->longText('full_address');
            $table->text('note');
            $table->string('postal_code', 50);
            $table->timestamps();

            $table->foreignId('user_id')->constrained('tb_user')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('district_id')->constrained('ref_district')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('city_id')->constrained('ref_city')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('province_id')->constrained('ref_province')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tb_user');
        Schema::dropIfExists('user_address');
    }
}
