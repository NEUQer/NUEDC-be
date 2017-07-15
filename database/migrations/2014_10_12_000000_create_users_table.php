<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('login_name',100)->unique()->nullable();
            $table->string('name',100)->nullable();
            $table->string('email',100)->nullable();
            $table->string('mobile',45)->unique()->nullable();
            $table->integer('school_id');
            $table->string('school_name',100);
            $table->string('password');
            $table->string('sex',4)->nullable();
            $table->string('add_on')->nullable();
            $table->integer('status')->default(0);
            $table->string('role',45)->nullable();
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
        Schema::dropIfExists('users');
    }
}
