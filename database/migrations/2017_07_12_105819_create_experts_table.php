<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('experts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',45);
            $table->integer('school_id');
            $table->string('school_name',100);
            $table->string('school_level',45);
            $table->string('sex',4);
            $table->string('mobile',45);
            $table->string('telephone',45);
            $table->string('email',100);
            $table->string('position',100);
            $table->string('major',100);
            $table->string('occupation',100);
            $table->string('status');
            $table->text('add_on');
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
        Schema::dropIfExists('experts');
    }
}
