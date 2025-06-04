<?php namespace PTG\Quiz\Models;

use Model;

/**
 * Quiz Model
 */
class Quiz extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string table associated with the model
     */
    protected $table = 'ptg_quiz_quizzes';

    /**
     * @var array guarded attributes aren't mass assignable
     */
    protected $guarded = ['*'];

    /**
     * @var array fillable attributes are mass assignable
     */
    protected $fillable = [
        'title',
        'description',
        'difficulty',
        'story_content',
        'type',
        'is_active',
        'time_limit',
        'questions_count'
    ];

    /**
     * @var array rules for validation
     */
    public $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'difficulty' => 'required|in:Beginner,Intermediate,Advanced',
        'type' => 'required|in:general,story',
        'time_limit' => 'nullable|integer|min:1',
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [
        'is_active' => 'boolean',
        'questions_count' => 'integer',
        'time_limit' => 'integer',
    ];

    /**
     * @var array hasMany relations
     */
    public $hasMany = [
        'questions' => [
            Question::class,
            'order' => 'order asc'
        ],
        'attempts' => [
            QuizAttempt::class
        ]
    ];

    /**
     * Get active questions for this quiz
     */
    public function getActiveQuestionsAttribute()
    {
        return $this->questions()->where('is_active', true)->orderBy('order')->get();
    }

    /**
     * Get random questions for quiz taking
     */
    public function getRandomQuestions($count = 10)
    {
        return $this->questions()
            ->where('is_active', true)
            ->with('options')
            ->inRandomOrder()
            ->limit($count)
            ->get();
    }

    /**
     * Update questions count
     */
    public function updateQuestionsCount()
    {
        $this->questions_count = $this->questions()->where('is_active', true)->count();
        $this->save();
    }

    /**
     * Scope: Active quizzes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
