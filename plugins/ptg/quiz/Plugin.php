<?php namespace PTG\Quiz;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public $require = ['RainLab.User'];

    public function pluginDetails()
    {
        return [
            'name'        => 'PTG Quiz',
            'description' => 'Quiz functionality for PTG with progress tracking',
            'author'      => 'PTG Team',
            'icon'        => 'icon-question',
            'version'     => '1.0.0'
        ];
    }

    public function registerComponents()
    {
        return [
            'PTG\Quiz\Components\ProgressDashboard' => 'progressDashboard',
        ];
    }

    public function boot()
    {
        // Extend RainLab User model to add quiz relationships
        \RainLab\User\Models\User::extend(function($model) {
            $model->hasMany['quiz_attempts'] = ['PTG\Quiz\Models\QuizAttempt'];
        });
    }
}
