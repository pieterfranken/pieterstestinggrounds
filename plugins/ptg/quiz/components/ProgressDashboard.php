<?php namespace PTG\Quiz\Components;

use Cms\Classes\ComponentBase;
use PTG\Quiz\Models\QuizAttempt;
use RainLab\User\Models\User;
use Carbon\Carbon;
use Auth;

class ProgressDashboard extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Progress Dashboard',
            'description' => 'Displays user quiz progress and statistics'
        ];
    }

    public function defineProperties()
    {
        return [
            'showRecentDays' => [
                'title' => 'Recent Activity Days',
                'description' => 'Number of days to show for recent activity',
                'type' => 'string',
                'default' => '30'
            ]
        ];
    }

    public function onRun()
    {
        $this->addCss('/plugins/ptg/quiz/assets/css/progress-dashboard.css');
        $this->addJs('/plugins/ptg/quiz/assets/js/progress-dashboard.js');
        
        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $recentDays = (int) $this->property('showRecentDays', 30);

        // Get all user attempts
        $allAttempts = QuizAttempt::where('user_id', $user->id)
            ->orderBy('completed_at', 'desc')
            ->get();

        // Recent attempts
        $recentAttempts = $allAttempts->where('completed_at', '>=', Carbon::now()->subDays($recentDays));

        // Statistics
        $this->page['totalAttempts'] = $allAttempts->count();
        $this->page['recentAttempts'] = $recentAttempts->count();
        $this->page['averageScore'] = $allAttempts->avg('percentage') ?: 0;
        $this->page['bestScore'] = $allAttempts->max('percentage') ?: 0;
        $this->page['recentAverageScore'] = $recentAttempts->avg('percentage') ?: 0;

        // Quiz type breakdown
        $this->page['quizTypeStats'] = $allAttempts->groupBy('quiz_type')->map(function ($attempts, $type) {
            return [
                'type' => $type,
                'count' => $attempts->count(),
                'average' => $attempts->avg('percentage'),
                'best' => $attempts->max('percentage')
            ];
        });

        // Recent activity (last 10 attempts)
        $this->page['recentActivity'] = $allAttempts->take(10);

        // Performance trend (last 30 days)
        $this->page['performanceTrend'] = $this->getPerformanceTrend($user->id, 30);

        // Achievement data
        $this->page['achievements'] = $this->calculateAchievements($allAttempts);

        // Study streak
        $this->page['studyStreak'] = $this->calculateStudyStreak($allAttempts);
    }

    protected function getPerformanceTrend($userId, $days)
    {
        $trend = [];
        $startDate = Carbon::now()->subDays($days);

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dayAttempts = QuizAttempt::where('user_id', $userId)
                ->whereDate('completed_at', $date)
                ->get();

            $trend[] = [
                'date' => $date->format('Y-m-d'),
                'attempts' => $dayAttempts->count(),
                'average_score' => $dayAttempts->avg('percentage') ?: 0
            ];
        }

        return $trend;
    }

    protected function calculateAchievements($attempts)
    {
        $achievements = [];

        // First Quiz
        if ($attempts->count() >= 1) {
            $achievements[] = [
                'name' => 'First Steps',
                'description' => 'Completed your first quiz',
                'icon' => 'bi-trophy',
                'earned' => true
            ];
        }

        // Perfect Score
        if ($attempts->where('percentage', 100)->count() > 0) {
            $achievements[] = [
                'name' => 'Perfect Score',
                'description' => 'Achieved 100% on a quiz',
                'icon' => 'bi-star-fill',
                'earned' => true
            ];
        }

        // Quiz Master (10 quizzes)
        if ($attempts->count() >= 10) {
            $achievements[] = [
                'name' => 'Quiz Master',
                'description' => 'Completed 10 quizzes',
                'icon' => 'bi-award',
                'earned' => true
            ];
        }

        // Consistent Performer (average > 80%)
        if ($attempts->avg('percentage') >= 80) {
            $achievements[] = [
                'name' => 'Consistent Performer',
                'description' => 'Maintain 80%+ average score',
                'icon' => 'bi-graph-up',
                'earned' => true
            ];
        }

        return $achievements;
    }

    protected function calculateStudyStreak($attempts)
    {
        if ($attempts->isEmpty()) {
            return 0;
        }

        // Get unique dates when user took quizzes, sorted by date descending
        $uniqueDates = $attempts
            ->map(function ($attempt) {
                return Carbon::parse($attempt->completed_at)->startOfDay()->format('Y-m-d');
            })
            ->unique()
            ->sort()
            ->reverse()
            ->values();

        if ($uniqueDates->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $today = Carbon::now()->startOfDay();
        $yesterday = Carbon::now()->subDay()->startOfDay();

        // Check if user has activity today or yesterday to start counting
        $latestDate = Carbon::parse($uniqueDates->first())->startOfDay();

        if (!$latestDate->eq($today) && !$latestDate->eq($yesterday)) {
            // No recent activity, streak is 0
            return 0;
        }

        // Start from the most recent date and count consecutive days
        $expectedDate = $latestDate;

        foreach ($uniqueDates as $dateString) {
            $currentDate = Carbon::parse($dateString)->startOfDay();

            if ($currentDate->eq($expectedDate)) {
                $streak++;
                $expectedDate = $expectedDate->subDay();
            } else {
                // Gap found, break the streak
                break;
            }
        }

        return $streak;
    }
}
