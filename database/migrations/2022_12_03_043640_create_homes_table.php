<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHomesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_home', function (Blueprint $table) {
            $table->id();
            $table->longText('banner');
            $table->longText('about');
            $table->string('phone', 50);
            $table->string('email', 225);
            $table->longtext('address');
            $table->timestamps();
        });

        Schema::create('mesage', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('subject', 50);
            $table->string('email', 225);
            $table->longText('message');
            $table->timestamps();
        });

        Schema::create('sponsor', function (Blueprint $table) {
            $table->id();
            $table->string('name', 225);
            $table->string('logo_path', 225);
            $table->string('logo_mime', 50);
            $table->text('link');
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
        Schema::dropIfExists('tb_home');
        Schema::dropIfExists('mesage');
        Schema::dropIfExists('sponsor');
    }
}
