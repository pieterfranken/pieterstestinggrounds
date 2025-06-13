<?php namespace PTG\Quiz\Models;

use Model;
use Carbon\Carbon;

class QuizAttempt extends Model
{
    use \October\Rain\Database\Traits\Validation;

    protected $table = 'ptg_quiz_attempts';

    protected $fillable = [
        'user_id',
        'quiz_type',
        'quiz_identifier',
        'score',
        'total_questions',
        'percentage',
        'questions_data',
        'completed_at'
    ];

    protected $dates = [
        'completed_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'questions_data' => 'array',
        'percentage' => 'decimal:2'
    ];

    public $rules = [
        'user_id' => 'required|integer',
        'quiz_type' => 'required|string|max:50',
        'score' => 'required|integer|min:0',
        'total_questions' => 'required|integer|min:1',
        'percentage' => 'required|numeric|min:0|max:100'
    ];

    public $belongsTo = [
        'user' => ['RainLab\User\Models\User']
    ];

    /**
     * Get the grade letter based on percentage
     */
    public function getGradeAttribute()
    {
        if ($this->percentage >= 90) return 'A';
        if ($this->percentage >= 80) return 'B';
        if ($this->percentage >= 70) return 'C';
        if ($this->percentage >= 60) return 'D';
        return 'F';
    }

    /**
     * Get the performance level
     */
    public function getPerformanceLevelAttribute()
    {
        if ($this->percentage >= 90) return 'Excellent';
        if ($this->percentage >= 80) return 'Good';
        if ($this->percentage >= 70) return 'Average';
        if ($this->percentage >= 60) return 'Below Average';
        return 'Needs Improvement';
    }

    /**
     * Scope for recent attempts
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('completed_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Scope for specific quiz type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('quiz_type', $type);
    }
}
