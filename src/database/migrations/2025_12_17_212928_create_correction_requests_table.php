<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('correction_requests', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('attendance_id');
            $table->unsignedBigInteger('breaks_id')->nullable();

            $table->dateTime('requested_start_time');
            $table->dateTime('requested_end_time');

            $table->text('reason');
            $table->tinyInteger('status');

            $table->timestamps();

            // 外部キー制約（余裕があれば）
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
            $table->foreign('breaks_id')->references('id')->on('breaks')->onDelete('set null');
        });
    }
}