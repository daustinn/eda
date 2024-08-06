<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\AssistsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BusinessUnitController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EdaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobPositionController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\QuestionnaireTemplateController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\YearController;

// -------------------------------- AUTH ROUTES ---------------------------

Route::get('login/azure', [LoginController::class, 'redirectToAzure']);
Route::get('login/azure/callback', [LoginController::class, 'handleAzureCallback']);
Auth::routes();

// -------------------------------- DASHBOARD ROUTES ---------------------------

Route::group(['middleware' => 'authMiddleware'], function () {

    // Home routes
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/home', [HomeController::class, 'index']);

    // Module routes
    Route::get('/', [ModuleController::class, 'index']);

    // User roles routes
    Route::get('users/user-roles', [UserRoleController::class, 'index']);
    Route::get('users/user-roles/create', [UserRoleController::class, 'create']);
    Route::get('users/user-roles/{id}', [UserRoleController::class, 'slug']);

    // Users routes
    Route::get('users', [UserController::class, 'index']);

    Route::get('users/schedules', [ScheduleController::class, 'index']);
    Route::get('users/schedules/create', [ScheduleController::class, 'create']);
    Route::get('users/schedules/{id}', [ScheduleController::class, 'schedule']);

    Route::get('users/emails', [UserController::class, 'emails']);

    Route::get('users/domains', [UserController::class, 'domains']);

    Route::get('users/job-positions', [UserController::class, 'jobPositions']);

    Route::get('users/roles', [UserController::class, 'roles']);

    Route::get('users/create', [UserController::class, 'create']);
    Route::get('users/edit/{id}', [UserController::class, 'edit']);

    Route::get('users/{id}', [UserController::class, 'slug']);
    Route::get('users/{id}/organization', [UserController::class, 'slug_organization']);
    Route::get('users/{id}/schedules', [UserController::class, 'slug_schedules']);
    Route::get('users/{id}/assists', [UserController::class, 'slug_assists']);


    // Edas routes
    Route::get('edas', [EdaController::class, 'index']);
    Route::get('edas/years', [YearController::class, 'index']);
    Route::get('edas/questionnaires-templates', [QuestionnaireTemplateController::class, 'index']);
    Route::get('edas/questionnaires-templates/create', [QuestionnaireTemplateController::class, 'create']);
    Route::get('edas/questionnaires-templates/{id}/questions', [QuestionnaireTemplateController::class, 'questions']);

    Route::get('edas/me', [EdaController::class, 'me']);

    Route::get('edas/{id_user}/eda', [EdaController::class, 'user']);
    Route::get('edas/{id_user}/eda/{id_year}', [EdaController::class, 'year']);
    Route::get('edas/{id_user}/eda/{id_year}/goals', [EdaController::class, 'goals']);
    Route::get('edas/{id_user}/eda/{id_year}/evaluation/{id_evaluation}', [EdaController::class, 'evaluation']);
    Route::get('edas/{id_user}/eda/{id_year}/questionnaires', [EdaController::class, 'questionnaires']);

    // Assists routes
    Route::get('assists', [AssistsController::class, 'index']);

    // -------- SETTINGS ROUTES ---------------------------
    Route::get('settings', [SettingController::class, 'index']);
    Route::get('settings/departments', [DepartmentController::class, 'index']);
    Route::get('settings/branches', [BranchController::class, 'index']);
    Route::get('settings/business-units', [BusinessUnitController::class, 'index']);
    Route::post('settings/business-units', [BusinessUnitController::class, 'store']);
    Route::post('settings/business-units/{id}', [BusinessUnitController::class, 'updated']);

    // routes if no route is found
    Route::fallback(function () {
        return view('+404');
    });
});
