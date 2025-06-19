<?php namespace PTG\Quiz\Components;

use Cms\Classes\ComponentBase;
use PTG\Quiz\Services\AIQuizService;
use PTG\Quiz\Models\QuizAttempt;
use Auth;
use Session;
use Redirect;

class AIQuizGenerator extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'AI Quiz Generator',
            'description' => 'Generate and take AI-powered quizzes on any topic'
        ];
    }

    public function defineProperties()
    {
        return [
            'defaultTopic' => [
                'title' => 'Default Topic',
                'description' => 'Default topic for quiz generation',
                'type' => 'string',
                'default' => 'General Knowledge'
            ],
            'defaultCount' => [
                'title' => 'Default Question Count',
                'description' => 'Default number of questions to generate',
                'type' => 'string',
                'default' => '10'
            ]
        ];
    }

    public function onRun()
    {
        $this->addCss('/plugins/ptg/quiz/assets/css/ai-quiz.css');
        $this->addJs('/plugins/ptg/quiz/assets/js/ai-quiz.js');
        
        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->page['defaultTopic'] = $this->property('defaultTopic');
        $this->page['defaultCount'] = $this->property('defaultCount');
        $this->page['currentQuiz'] = Session::get('ai_generated_quiz', []);
        $this->page['showQuiz'] = !empty($this->page['currentQuiz']);
    }

    /**
     * Generate new AI quiz
     */
    public function onGenerateQuiz()
    {
        $topic = post('topic', $this->property('defaultTopic'));
        $difficulty = post('difficulty', 'medium');
        $count = (int) post('count', $this->property('defaultCount'));

        // Validate inputs
        if (empty($topic)) {
            throw new \ValidationException(['topic' => 'Please enter a topic']);
        }

        if ($count < 1 || $count > 20) {
            throw new \ValidationException(['count' => 'Question count must be between 1 and 20']);
        }

        try {
            $aiService = new AIQuizService();
            $questions = $aiService->generateQuestions($topic, $count, $difficulty);
            
            // Add IDs to questions for form handling
            foreach ($questions as $index => &$question) {
                $question['id'] = $index + 1;
            }
            
            // Store in session
            Session::put('ai_generated_quiz', $questions);
            Session::put('ai_quiz_topic', $topic);
            Session::put('ai_quiz_difficulty', $difficulty);
            
            $this->page['currentQuiz'] = $questions;
            $this->page['showQuiz'] = true;
            
            return [
                '#ai-quiz-container' => $this->renderPartial('ai-quiz/quiz-form'),
                '#quiz-status' => '<div class="alert alert-primary">Generated ' . count($questions) . ' questions about "' . $topic . '"!</div>'
            ];

        } catch (\Exception $e) {
            return [
                '#quiz-status' => '<div class="alert alert-danger">Failed to generate quiz: ' . $e->getMessage() . '</div>'
            ];
        }
    }

    /**
     * Submit AI-generated quiz
     */
    public function onSubmitAIQuiz()
    {
        $answers = post('answers', []);
        $questions = Session::get('ai_generated_quiz', []);
        $topic = Session::get('ai_quiz_topic', 'Unknown');
        $difficulty = Session::get('ai_quiz_difficulty', 'medium');

        if (empty($questions)) {
            return Redirect::to('/ai-quiz');
        }

        $score = 0;
        $results = [];
        $aiService = new AIQuizService();

        foreach ($questions as $question) {
            $questionId = $question['id'];
            $userAnswer = isset($answers[$questionId]) ? $answers[$questionId] : null;
            $isCorrect = ($userAnswer === $question['correct']);

            if ($isCorrect) {
                $score++;
            }

            // Generate AI explanation for each answer
            $explanation = '';
            try {
                $explanation = $aiService->generateExplanation(
                    $question['question'],
                    $question['correct'],
                    $userAnswer ?: 'No answer',
                    $question['options']
                );
            } catch (\Exception $e) {
                $explanation = 'Explanation temporarily unavailable.';
            }

            $results[$questionId] = [
                'question' => $question['question'],
                'userAnswer' => $userAnswer,
                'correctAnswer' => $question['correct'],
                'isCorrect' => $isCorrect,
                'options' => $question['options'],
                'explanation' => $explanation
            ];
        }

        $totalQuestions = count($questions);
        $percentage = ($score / $totalQuestions) * 100;

        // Save to database
        $user = Auth::user();
        if ($user) {
            try {
                QuizAttempt::create([
                    'user_id' => $user->id,
                    'quiz_type' => 'AI',
                    'quiz_identifier' => $topic . ' (' . $difficulty . ')',
                    'score' => $score,
                    'total_questions' => $totalQuestions,
                    'percentage' => $percentage,
                    'questions_data' => $results,
                    'completed_at' => now()
                ]);
            } catch (\Exception $e) {
                // Silently fail if database issues
            }
        }

        // Clear session
        Session::forget(['ai_generated_quiz', 'ai_quiz_topic', 'ai_quiz_difficulty']);

        $this->page['results'] = $results;
        $this->page['score'] = $score;
        $this->page['totalQuestions'] = $totalQuestions;
        $this->page['percentage'] = $percentage;
        $this->page['topic'] = $topic;
        $this->page['difficulty'] = $difficulty;

        return [
            '#ai-quiz-container' => $this->renderPartial('ai-quiz/results'),
            '#quiz-status' => ''
        ];
    }

    /**
     * Reset quiz generator
     */
    public function onResetQuiz()
    {
        Session::forget(['ai_generated_quiz', 'ai_quiz_topic', 'ai_quiz_difficulty']);
        
        $this->prepareVars();
        
        return [
            '#ai-quiz-container' => $this->renderPartial('ai-quiz/generator-form'),
            '#quiz-status' => ''
        ];
    }


}
