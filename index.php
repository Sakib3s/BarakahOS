<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');

require_once APP_PATH . '/config/bootstrap.php';

use App\Router;

$router = new Router();

$router->get('/login', 'AuthController@showLogin', 'GuestMiddleware');
$router->post('/login', 'AuthController@login', 'GuestMiddleware');
$router->get('/register', 'AuthController@showRegister', 'GuestMiddleware');
$router->post('/register', 'AuthController@register', 'GuestMiddleware');
$router->post('/logout', 'AuthController@logout', 'AuthMiddleware');

$router->get('/', 'HomeController@index', 'AuthMiddleware');
$router->post('/tasks', 'HomeController@store', 'AuthMiddleware');
$router->post('/tasks/complete', 'HomeController@complete', 'AuthMiddleware');
$router->post('/tasks/delete', 'HomeController@deleteTask', 'AuthMiddleware');
$router->get('/routine-templates', 'RoutineTemplateController@index', 'AuthMiddleware');
$router->get('/routine-templates/create', 'RoutineTemplateController@create', 'AuthMiddleware');
$router->post('/routine-templates/create', 'RoutineTemplateController@store', 'AuthMiddleware');
$router->get('/routine-templates/edit', 'RoutineTemplateController@edit', 'AuthMiddleware');
$router->post('/routine-templates/update', 'RoutineTemplateController@update', 'AuthMiddleware');
$router->post('/routine-templates/delete', 'RoutineTemplateController@delete', 'AuthMiddleware');
$router->get('/daily-checklist', 'DailyChecklistController@index', 'AuthMiddleware');
$router->get('/daily-checklist/create', 'DailyChecklistController@create', 'AuthMiddleware');
$router->post('/daily-checklist/create', 'DailyChecklistController@store', 'AuthMiddleware');
$router->get('/daily-checklist/edit', 'DailyChecklistController@edit', 'AuthMiddleware');
$router->post('/daily-checklist/update', 'DailyChecklistController@update', 'AuthMiddleware');
$router->post('/daily-checklist/status', 'DailyChecklistController@updateStatus', 'AuthMiddleware');
$router->get('/fixed-task-tracking', 'FixedTaskTrackingController@index', 'AuthMiddleware');
$router->post('/fixed-task-tracking/update', 'FixedTaskTrackingController@update', 'AuthMiddleware');
$router->get('/focus-sessions', 'FocusSessionController@index', 'AuthMiddleware');
$router->get('/focus-sessions/create', 'FocusSessionController@create', 'AuthMiddleware');
$router->post('/focus-sessions/start', 'FocusSessionController@start', 'AuthMiddleware');
$router->post('/focus-sessions/end', 'FocusSessionController@end', 'AuthMiddleware');
$router->post('/focus-sessions/delete', 'FocusSessionController@delete', 'AuthMiddleware');
$router->get('/distractions', 'DistractionController@index', 'AuthMiddleware');
$router->post('/distractions', 'DistractionController@store', 'AuthMiddleware');
$router->post('/distractions/update', 'DistractionController@update', 'AuthMiddleware');
$router->post('/distractions/delete', 'DistractionController@delete', 'AuthMiddleware');
$router->get('/prayers', 'PrayerController@index', 'AuthMiddleware');
$router->post('/prayers/update', 'PrayerController@update', 'AuthMiddleware');
$router->get('/sleep-tracker', 'SleepTrackerController@index', 'AuthMiddleware');
$router->post('/sleep-tracker/save', 'SleepTrackerController@save', 'AuthMiddleware');
$router->post('/sleep-tracker/delete', 'SleepTrackerController@delete', 'AuthMiddleware');
$router->get('/daily-review', 'DailyReviewController@index', 'AuthMiddleware');
$router->post('/daily-review/save', 'DailyReviewController@save', 'AuthMiddleware');
$router->get('/weekly-score', 'WeeklyScoreController@index', 'AuthMiddleware');
$router->get('/reports/daily', 'ReportController@daily', 'AuthMiddleware');
$router->get('/reports/weekly', 'ReportController@weekly', 'AuthMiddleware');
$router->get('/reports/monthly', 'ReportController@monthly', 'AuthMiddleware');

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/'
);
