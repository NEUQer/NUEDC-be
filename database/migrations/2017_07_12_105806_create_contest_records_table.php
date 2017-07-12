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
            $table->string('team_name');
            $table->integer('school_id');
            $table->string('school_name',100);
            $table->integer('contest_id');
            $table->string('school_level',45);
            $table->string('member1');
            $table->string('member2');
            $table->string('member3');
            $table->string('teacher');
            $table->string('contact_mobile',45);
            $table->string('email',100);
            $table->integer('problem_selected');
            $table->string('status');
            $table->string('result'); // 关键标志位，老王的意思直接用中文字符
            $table->string('result_info');
            $table->string('onsite_info');//现场比赛相关信息
            $table->string('problem_selected_at');
            $table->string('result_at');//比赛结果确定时间
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
