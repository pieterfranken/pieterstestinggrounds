<?php namespace PTG\Quiz\Models;

use Model;

/**
 * QuestionOption Model
 */
class QuestionOption extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string table associated with the model
     */
    protected $table = 'ptg_quiz_question_options';

    /**
     * @var array guarded attributes aren't mass assignable
     */
    protected $guarded = ['*'];

    /**
     * @var array fillable attributes are mass assignable
     */
    protected $fillable = [
        'question_id',
        'option_key',
        'option_text'
    ];

    /**
     * @var array rules for validation
     */
    public $rules = [
        'question_id' => 'required|integer|exists:ptg_quiz_questions,id',
        'option_key' => 'required|in:A,B,C,D',
        'option_text' => 'required|string',
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [
        'question_id' => 'integer',
    ];

    /**
     * @var array belongsTo relations
     */
    public $belongsTo = [
        'question' => Question::class
    ];

    /**
     * Check if this option is the correct answer
     */
    public function getIsCorrectAttribute()
    {
        return $this->question && $this->option_key === $this->question->correct_option;
    }

    /**
     * Scope: By question
     */
    public function scopeByQuestion($query, $questionId)
    {
        return $query->where('question_id', $questionId);
    }

    /**
     * Scope: By option key
     */
    public function scopeByKey($query, $key)
    {
        return $query->where('option_key', $key);
    }
}
