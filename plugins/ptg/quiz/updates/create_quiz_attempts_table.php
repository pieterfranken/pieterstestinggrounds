<?php

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ptg_quiz_attempts', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('quiz_id')->unsigned();
            $table->integer('score')->default(0);
            $table->integer('total_questions');
            $table->json('answers')->nullable(); // Store user answers
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_taken')->nullable(); // in seconds
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('quiz_id')->references('id')->on('ptg_quiz_quizzes')->onDelete('cascade');
            $table->index(['user_id', 'quiz_id']);
            $table->index('completed_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ptg_quiz_attempts');
    }
};
