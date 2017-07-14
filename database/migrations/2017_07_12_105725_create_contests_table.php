<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title',45);
            $table->text('description');
            $table->string('status');
            $table->tinyInteger('can_register')->default(0);
            $table->tinyInteger('can_select_problem')->default(0);
            // 报名时间
            $table->string('register_start_time');
            $table->string('register_end_time');
            // 选题时间
            $table->string('problem_start_time');
            $table->string('problem_end_time');
            // 附加
            $table->json('add_on')->nullable();
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
        Schema::dropIfExists('contests');
    }
}
