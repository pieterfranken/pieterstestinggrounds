<?php namespace PTG\Quiz\Models;

use Model;
use RainLab\User\Models\User;

/**
 * QuizAttempt Model
 */
class QuizAttempt extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string table associated with the model
     */
    protected $table = 'ptg_quiz_attempts';

    /**
     * @var array guarded attributes aren't mass assignable
     */
    protected $guarded = ['*'];

    /**
     * @var array fillable attributes are mass assignable
     */
    protected $fillable = [
        'user_id',
        'quiz_id',
        'score',
        'total_questions',
        'answers',
        'started_at',
        'completed_at',
        'time_taken'
    ];

    /**
     * @var array rules for validation
     */
    public $rules = [
        'user_id' => 'required|integer|exists:users,id',
        'quiz_id' => 'required|integer|exists:ptg_quiz_quizzes,id',
        'score' => 'integer|min:0',
        'total_questions' => 'required|integer|min:1',
        'time_taken' => 'nullable|integer|min:0',
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [
        'user_id' => 'integer',
        'quiz_id' => 'integer',
        'score' => 'integer',
        'total_questions' => 'integer',
        'time_taken' => 'integer',
        'answers' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * @var array belongsTo relations
     */
    public $belongsTo = [
        'user' => User::class,
        'quiz' => Quiz::class
    ];

    /**
     * Get percentage score
     */
    public function getPercentageAttribute()
    {
        if ($this->total_questions == 0) {
            return 0;
        }
        return round(($this->score / $this->total_questions) * 100, 2);
    }

    /**
     * Get grade based on percentage
     */
    public function getGradeAttribute()
    {
        $percentage = $this->percentage;
        
        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'F';
    }

    /**
     * Check if attempt is completed
     */
    public function getIsCompletedAttribute()
    {
        return !is_null($this->completed_at);
    }

    /**
     * Get formatted time taken
     */
    public function getFormattedTimeAttribute()
    {
        if (!$this->time_taken) {
            return 'N/A';
        }

        $minutes = floor($this->time_taken / 60);
        $seconds = $this->time_taken % 60;
        
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Scope: Completed attempts
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * Scope: By user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: By quiz
     */
    public function scopeByQuiz($query, $quizId)
    {
        return $query->where('quiz_id', $quizId);
    }

    /**
     * Calculate and update score based on answers
     */
    public function calculateScore($questions)
    {
        $score = 0;
        $answers = $this->answers ?? [];

        foreach ($questions as $question) {
            $userAnswer = $answers[$question->id] ?? null;
            if ($userAnswer && $question->isCorrectAnswer($userAnswer)) {
                $score++;
            }
        }

        $this->score = $score;
        $this->save();

        return $score;
    }
}
