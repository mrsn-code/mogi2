<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            #ユーザーID
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            #勤務日
            $table->date('work_date');
            #出勤時刻
            $table->timestamp('clock_in')->nullable();
            #退勤時刻
            $table->timestamp('clock_out')->nullable();
            #備考
            $table->text('note')->nullable();
            #ステータス
            $table->string('status')->default('off');
            
            $table->timestamps();
            #1ユーザー1日1件
            $table->unique(['user_id', 'work_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
