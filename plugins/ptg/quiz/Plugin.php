<?php namespace PTG\Quiz;

use System\Classes\PluginBase;
use Backend;

/**
 * PTG Quiz Plugin
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = ['RainLab.User'];

    /**
     * pluginDetails
     */
    public function pluginDetails()
    {
        return [
            'name' => 'PTG Quiz',
            'description' => 'Quiz management system for PTG (Pieters Testing Grounds)',
            'author' => 'PTG',
            'icon' => 'icon-question-circle',
            'homepage' => ''
        ];
    }

    /**
     * register method, called when the plugin is first registered.
     */
    public function register()
    {
        //
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
    {
        //
    }

    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
        return [
            'PTG\Quiz\Components\QuizList' => 'quizList',
            'PTG\Quiz\Components\QuizTaker' => 'quizTaker',
            'PTG\Quiz\Components\QuizResults' => 'quizResults',
        ];
    }

    /**
     * registerPermissions used by the backend.
     */
    public function registerPermissions()
    {
        return [
            'ptg.quiz.access_quizzes' => [
                'tab' => 'PTG Quiz',
                'label' => 'Manage Quizzes'
            ],
            'ptg.quiz.access_questions' => [
                'tab' => 'PTG Quiz',
                'label' => 'Manage Questions'
            ],
            'ptg.quiz.view_attempts' => [
                'tab' => 'PTG Quiz',
                'label' => 'View Quiz Attempts'
            ],
        ];
    }

    /**
     * registerNavigation used by the backend.
     */
    public function registerNavigation()
    {
        return [
            'quiz' => [
                'label' => 'PTG Quiz',
                'url' => Backend::url('ptg/quiz/quizzes'),
                'icon' => 'icon-question-circle',
                'permissions' => ['ptg.quiz.*'],
                'order' => 500,

                'sideMenu' => [
                    'quizzes' => [
                        'label' => 'Quizzes',
                        'icon' => 'icon-list',
                        'url' => Backend::url('ptg/quiz/quizzes'),
                        'permissions' => ['ptg.quiz.access_quizzes']
                    ],
                    'questions' => [
                        'label' => 'Questions',
                        'icon' => 'icon-question',
                        'url' => Backend::url('ptg/quiz/questions'),
                        'permissions' => ['ptg.quiz.access_questions']
                    ],
                    'attempts' => [
                        'label' => 'Quiz Attempts',
                        'icon' => 'icon-bar-chart',
                        'url' => Backend::url('ptg/quiz/attempts'),
                        'permissions' => ['ptg.quiz.view_attempts']
                    ],
                ]
            ]
        ];
    }
}
