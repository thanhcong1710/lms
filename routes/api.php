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

Route::post('/login', [AuthController::class, 'login']);

Route::apiResource('branches', BranchController::class);
Route::apiResource('teachers', TeacherController::class);
Route::apiResource('classes', ClassController::class);
Route::apiResource('students', StudentController::class);
Route::apiResource('contracts', ContractController::class);
Route::get('/tests', [LmsTestController::class, 'index']);
