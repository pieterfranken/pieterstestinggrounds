<?php

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ptg_quiz_quizzes', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('difficulty')->default('Beginner'); // Beginner, Intermediate, Advanced
            $table->text('story_content')->nullable(); // For story-based quizzes
            $table->string('type')->default('general'); // general, story
            $table->boolean('is_active')->default(true);
            $table->integer('time_limit')->nullable(); // in minutes
            $table->integer('questions_count')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ptg_quiz_quizzes');
    }
};
