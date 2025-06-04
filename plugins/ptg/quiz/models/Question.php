<?php namespace PTG\Quiz\Models;

use Model;

/**
 * Question Model
 */
class Question extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string table associated with the model
     */
    protected $table = 'ptg_quiz_questions';

    /**
     * @var array guarded attributes aren't mass assignable
     */
    protected $guarded = ['*'];

    /**
     * @var array fillable attributes are mass assignable
     */
    protected $fillable = [
        'quiz_id',
        'question_text',
        'correct_option',
        'order',
        'is_active'
    ];

    /**
     * @var array rules for validation
     */
    public $rules = [
        'quiz_id' => 'required|integer|exists:ptg_quiz_quizzes,id',
        'question_text' => 'required|string',
        'correct_option' => 'required|in:A,B,C,D',
        'order' => 'integer|min:0',
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [
        'quiz_id' => 'integer',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * @var array belongsTo relations
     */
    public $belongsTo = [
        'quiz' => Quiz::class
    ];

    /**
     * @var array hasMany relations
     */
    public $hasMany = [
        'options' => [
            QuestionOption::class,
            'order' => 'option_key asc'
        ]
    ];

    /**
     * Get the correct answer text
     */
    public function getCorrectAnswerAttribute()
    {
        return $this->options()
            ->where('option_key', $this->correct_option)
            ->first()
            ->option_text ?? '';
    }

    /**
     * Check if given answer is correct
     */
    public function isCorrectAnswer($answer)
    {
        return strtoupper($answer) === strtoupper($this->correct_option);
    }

    /**
     * Get options as array (for backward compatibility)
     */
    public function getOptionsArrayAttribute()
    {
        $options = [];
        foreach ($this->options as $option) {
            $options[$option->option_key] = $option->option_text;
        }
        return $options;
    }

    /**
     * Scope: Active questions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By quiz
     */
    public function scopeByQuiz($query, $quizId)
    {
        return $query->where('quiz_id', $quizId);
    }

    /**
     * After save, update quiz questions count
     */
    public function afterSave()
    {
        if ($this->quiz) {
            $this->quiz->updateQuestionsCount();
        }
    }

    /**
     * After delete, update quiz questions count
     */
    public function afterDelete()
    {
        if ($this->quiz) {
            $this->quiz->updateQuestionsCount();
        }
    }
}
