<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// -------------------------------- AUTH ROUTES ---------------------------

Route::post('login', 'App\Http\Controllers\Api\LoginController@login');


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    // Users routes
    Route::post('users', 'App\Http\Controllers\Api\UserController@create');
    Route::post('users/{id}', 'App\Http\Controllers\Api\UserController@update');
    Route::post('users/{id}/profile', 'App\Http\Controllers\Api\UserController@profile');
    Route::get('users/search', 'App\Http\Controllers\Api\UserController@search');

    // User user roles routes 
    Route::post('user-roles', 'App\Http\Controllers\Api\UserRoleController@create');
    Route::post('user-roles/{id}', 'App\Http\Controllers\Api\UserRoleController@update');

    // Cargo routes
    Route::get('roles/by_job_position/{id}', 'App\Http\Controllers\Api\RolController@by_job_position');

    // Edas routes
    Route::post('edas', 'App\Http\Controllers\Api\EdaController@create');
    Route::post('edas/close/{id}', 'App\Http\Controllers\Api\EdaController@close');

    // Goals routes
    Route::post('goals/sent', 'App\Http\Controllers\Api\GoalController@sent');
    Route::post('goals/approve', 'App\Http\Controllers\Api\GoalController@approve');
    Route::post('goals/update/{id}', 'App\Http\Controllers\Api\GoalController@update');
    Route::get('goals/by-eda/{id}', 'App\Http\Controllers\Api\GoalController@byEda');
    Route::get('goals/by-evaluation/{id}', 'App\Http\Controllers\Api\GoalController@byEvaluation');

    // evaluations routes
    Route::post('evaluation/self-qualification', 'App\Http\Controllers\Api\EvaluationController@selfQualification');
    Route::post('evaluation/average', 'App\Http\Controllers\Api\EvaluationController@average');
    Route::post('evaluation/close', 'App\Http\Controllers\Api\EvaluationController@close');

    // Areas routes
    Route::post('areas', 'App\Http\Controllers\AreaController@store');
    Route::post('areas/{id}', 'App\Http\Controllers\AreaController@update');

    // Departments routes
    Route::post('departments', 'App\Http\Controllers\DepartmentController@store');
    Route::post('departments/{id}', 'App\Http\Controllers\DepartmentController@update');

    // Job Positions routes
    Route::post('job-positions', 'App\Http\Controllers\JobPositionController@store');
    Route::post('job-positions/{id}', 'App\Http\Controllers\JobPositionController@update');

    // Roles routes
    Route::post('roles', 'App\Http\Controllers\RoleController@store');
    Route::post('roles/{id}', 'App\Http\Controllers\RoleController@update');

    // Branches routes
    Route::post('branches', 'App\Http\Controllers\BranchController@store');
    Route::post('branches/{id}', 'App\Http\Controllers\BranchController@update');

    // Template routes
    Route::post('templates', 'App\Http\Controllers\Api\TemplateController@create');
    Route::post('templates/{id}', 'App\Http\Controllers\Api\TemplateController@update');
    Route::post('templates/{id}/change-in-use', 'App\Http\Controllers\Api\TemplateController@changeInUse');

    // Questions routes
    Route::get('questions/by-template/{id}', 'App\Http\Controllers\Api\QuestionController@by_template');

    // years routes
    Route::post('years', 'App\Http\Controllers\YearController@create');
    Route::post('years/{id}', 'App\Http\Controllers\YearController@update');
    Route::post('years/open/{id}', 'App\Http\Controllers\YearController@open');
    Route::post('years/close/{id}', 'App\Http\Controllers\YearController@close');



    // Emails routes
    Route::post('emails', 'App\Http\Controllers\Api\EmailController@assign');
    Route::post('emails/{id}', 'App\Http\Controllers\Api\EmailController@update');
    Route::post('emails/discharge/{id}', 'App\Http\Controllers\Api\EmailController@discharge');

    // Schedules routes
    Route::post('schedules/delete/{id}', 'App\Http\Controllers\Api\ScheduleController@remove');
    Route::post('schedules/archive/{id}', 'App\Http\Controllers\Api\ScheduleController@archive');

    Route::post('schedules/group', 'App\Http\Controllers\Api\ScheduleController@group');
    Route::post('schedules/group/{id}', 'App\Http\Controllers\Api\ScheduleController@update');
    Route::post('schedules/group/{id}/add', 'App\Http\Controllers\Api\ScheduleController@add');
    Route::post('schedules/{id}', 'App\Http\Controllers\Api\ScheduleController@updateSchedule');


    Route::get('schedules/group/{id}', 'App\Http\Controllers\Api\ScheduleController@schedules');
});
