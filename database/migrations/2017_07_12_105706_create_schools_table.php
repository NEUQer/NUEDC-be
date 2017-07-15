<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',100)->unique();
            $table->string('level',45);
            $table->string('nick_name',100)->nullable();
            $table->string('english_name',100)->nullable();
            $table->string('english_nick_name',100)->nullable();
            $table->string('address')->nullable();
            $table->string('post_code',45)->nullable();//邮编
            $table->string('principal',100)->nullable();//负责人姓名
            $table->string('principal_mobile',45)->nullable();//负责人联系电话
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
        Schema::dropIfExists('schools');
    }
}
