<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProblemChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('problem_checks', function (Blueprint $table) {
            $table->integer('school_id');
            $table->integer('contest_id');
            $table->string('status',45)->default('未审核');
            $table->timestamps();
            $table->primary(['school_id','contest_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('problem_checks');
    }
}
