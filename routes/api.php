<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AcademicRiskController;
use App\Http\Controllers\ResumeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/students/{student_id}/academic-risk', [AcademicRiskController::class, 'getRiskPrediction']);
Route::get('/students/{student_id}/resume/download', [ResumeController::class, 'download']);
