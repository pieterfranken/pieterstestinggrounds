<?php

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ptg_quiz_questions', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('quiz_id')->unsigned();
            $table->text('question_text');
            $table->string('correct_option', 1); // A, B, C, D
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('quiz_id')->references('id')->on('ptg_quiz_quizzes')->onDelete('cascade');
            $table->index(['quiz_id', 'order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ptg_quiz_questions');
    }
};
