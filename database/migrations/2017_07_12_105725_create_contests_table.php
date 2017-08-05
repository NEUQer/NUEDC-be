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
        $current = new \Carbon\Carbon();

        Schema::create('contests', function (Blueprint $table) use($current){
            $table->increments('id');
            $table->string('title',45);
            $table->text('description')->nullable();
//            $table->string('level',45);
            $table->string('prefix',4);
            $table->string('status');
            $table->string('result_check',45)->default('未公布');
            $table->tinyInteger('can_register')->default(-1);
            $table->tinyInteger('can_select_problem')->default(-1);
            // 报名时间
            // 下面这几个时间戳最好改成可空
            $table->timestamp('register_start_time')->default($current);
            $table->timestamp('register_end_time')->default($current);
            // 选题时间
            $table->timestamp('problem_start_time')->default($current);
            $table->timestamp('problem_end_time')->default($current);
            // 附加
            $table->timestamp('submit_end_time')->default($current);
            $table->text('add_on')->nullable();
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
