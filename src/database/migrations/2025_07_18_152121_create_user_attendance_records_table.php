<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAttendanceRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // ユーザID
            $table->date('date'); // 日付
            // 出勤時間
            $table->time('clock_in_time')->nullable();
            // 退勤時間
            $table->time('clock_out_time')->nullable();
            // 状態（例：勤務中、休憩中、外出）
            $table->string('status')->default('勤務外'); //例：勤務外、出勤中、休憩中
            $table->timestamps();

            // 外部キー制約
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_attendance_records');
    }
}
