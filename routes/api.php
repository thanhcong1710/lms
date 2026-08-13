<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\LmsTestController;
use App\Http\Controllers\IgbhConfigController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OptionsController;
use App\Http\Controllers\Api\UcreaEvaluationController;
use App\Http\Controllers\Api\IgbhEvaluationController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\DashboardController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/single-sign-on', [AuthController::class, 'singleSignOn']);
Route::get('/get-login-redirect', [AuthController::class, 'getLoginRedirect']);
Route::get('/me', [AuthController::class, 'me']);
Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

Route::apiResource('branches', BranchController::class);
Route::apiResource('teachers', TeacherController::class);
Route::apiResource('classes', ClassController::class);
Route::apiResource('students', StudentController::class);
Route::apiResource('contracts', ContractController::class);
Route::apiResource('users', UserController::class);
Route::get('/tests', [LmsTestController::class, 'index']);

// Options endpoints for form select boxes
Route::get('/options/branches', [OptionsController::class, 'branches']);
Route::get('/options/teachers', [OptionsController::class, 'teachers']);
Route::get('/options/classes', [OptionsController::class, 'classes']);
Route::get('/options/students', [OptionsController::class, 'students']);

Route::get('/ucrea/results', [UcreaEvaluationController::class, 'getResults']);
Route::get('/ucrea/init-data', [UcreaEvaluationController::class, 'getInitData']);
Route::post('/ucrea/results', [UcreaEvaluationController::class, 'createResult']);
Route::get('/ucrea/results/{id}', [UcreaEvaluationController::class, 'getResultDetail']);
Route::post('/ucrea/results/{id}/grade', [UcreaEvaluationController::class, 'saveGrade']);

Route::get('/igbh/results', [IgbhEvaluationController::class, 'getResults']);
Route::get('/igbh/init-data', [IgbhEvaluationController::class, 'getInitData']);
Route::post('/igbh/results', [IgbhEvaluationController::class, 'createResult']);
Route::get('/igbh/evaluations/{id}', [IgbhEvaluationController::class, 'show']);

// IGBH Config
Route::get('/igbh/tests/{testSeq}/config', [IgbhConfigController::class, 'getConfig']);
Route::put('/igbh/tests/{testSeq}/config', [IgbhConfigController::class, 'updateConfig']);

Route::get('/igbh/results/{id}', [IgbhEvaluationController::class, 'getResultDetail']);
Route::post('/igbh/results/{id}/grade', [IgbhEvaluationController::class, 'saveGrade']);

Route::get('/igbh/weekly/results', [\App\Http\Controllers\Api\IgbhWeeklyEvaluationController::class, 'getResults']);
Route::get('/igbh/weekly/init-data', [\App\Http\Controllers\Api\IgbhWeeklyEvaluationController::class, 'getInitData']);
Route::post('/igbh/weekly/results', [\App\Http\Controllers\Api\IgbhWeeklyEvaluationController::class, 'createResult']);
Route::get('/igbh/weekly/results/{id}', [\App\Http\Controllers\Api\IgbhWeeklyEvaluationController::class, 'getResultDetail']);
Route::post('/igbh/weekly/results/{id}/grade', [\App\Http\Controllers\Api\IgbhWeeklyEvaluationController::class, 'saveGrade']);


Route::get('/igbh/summative/results', [\App\Http\Controllers\Api\IgbhSummativeEvaluationController::class, 'getResults']);
Route::get('/igbh/summative/results/{id}', [\App\Http\Controllers\Api\IgbhSummativeEvaluationController::class, 'getReport']);
Route::get('/igbh/summative/form-data/{id}', [\App\Http\Controllers\Api\IgbhSummativeEvaluationController::class, 'getFormData']);
Route::post('/igbh/summative/save/{id}', [\App\Http\Controllers\Api\IgbhSummativeEvaluationController::class, 'saveFormData']);

Route::prefix('v1')->group(function () {
    Route::post('auth/sys/token.do', [IntegrationController::class, 'getToken']);
    Route::post('user/centerRegAction.do', [IntegrationController::class, 'centerRegAction']);
    Route::post('user/centerModAction.do', [IntegrationController::class, 'centerModAction']);
    Route::post('user/teacherRegAction.do', [IntegrationController::class, 'teacherRegAction']);
    Route::post('user/teacherModAction.do', [IntegrationController::class, 'teacherModAction']);
    Route::post('cntr/classRegAction.do', [IntegrationController::class, 'classRegAction']);
    Route::post('cntr/classModAction.do', [IntegrationController::class, 'classModAction']);
    Route::post('cntr/studentRegAction.do', [IntegrationController::class, 'studentRegAction']);
    Route::post('cntr/studentModAction.do', [IntegrationController::class, 'studentModAction']);
});
