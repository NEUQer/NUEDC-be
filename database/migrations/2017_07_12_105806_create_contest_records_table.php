<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContestRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contest_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('register_id');
            $table->string('team_name');
            $table->integer('school_id');
            $table->string('school_name', 100);
            $table->integer('contest_id');
            $table->string('school_level', 45);
            $table->string('member1');
            $table->string('member2');
            $table->string('member3');
            $table->string('teacher');
            $table->string('contact_mobile', 45);
            $table->string('email', 100);
            $table->integer('problem_selected')->default(-1);
            $table->string('status');
            $table->string('result')->nullable(); // 获奖情况
            $table->string('result_info')->nullable(); // 结果评审进度
            $table->string('onsite_info')->nullable();//现场比赛相关信息
            $table->timestamp('problem_selected_at')->nullable();
            $table->timestamp('result_at')->nullable();//比赛结果确定时间
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
        Schema::dropIfExists('contest_records');
    }
}
