<?php

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ptg_quiz_question_options', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('question_id')->unsigned();
            $table->string('option_key', 1); // A, B, C, D
            $table->text('option_text');
            $table->timestamps();

            $table->foreign('question_id')->references('id')->on('ptg_quiz_questions')->onDelete('cascade');
            $table->index(['question_id', 'option_key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ptg_quiz_question_options');
    }
};
