<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AIAnalyzerController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PsychologistController;

Route::post('register', [AuthController::class, 'register']);
Route::post('register/psychologist', [AuthController::class, 'RegisterPsychologist']);
Route::post('login', [AuthController::class, 'login']);


Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::middleware('role:psychologist')->group(function () {
        Route::get('psychologist/analitics', [PsychologistController::class, 'getConsultationsByPsychologist']);
        Route::post('psychologist/create/schedule', [ScheduleController::class, 'storeDayAndTimes']);
        Route::put('psychologist/schedule/update', [ScheduleController::class, 'updateDaysAndTimes']);
        Route::get('psychologist/schedule', [ScheduleController::class, 'getPsychologistSchedule']);
        Route::post('psychologist/schedule/open', [ScheduleController::class, 'openSchedule']);
        Route::post('psychologist/schedule/close', [ScheduleController::class, 'closeSchedule']);
        Route::get('psychologist/appointment-history', [ScheduleController::class, 'appoimentHistory']);
    });

    Route::middleware('role:patient')->group(function () {
        Route::get('patients/appointments', [PatientController::class, 'appointments']);
        Route::get('patients/psychologists-list', [PatientController::class, 'psychologists']);
        Route::get('patients/psychologists/{id}', [PatientController::class, 'psychologistDetail'])->name('patients.psychologist.detail');
        Route::post('patients/psychologists/{id}/book', [PatientController::class, 'psychologistBook'])->name('patients.psychologist.book');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('admin/profile', [AuthController::class, 'profile']);
        Route::get('admin/home', [AdminController::class, 'index']);
        Route::get('admin/psychologists', [AdminController::class, 'verifiedPsychologists']);
        Route::get('admin/psychologists-notverified', [AdminController::class, 'notVerifiedPsychologists']);
        Route::post('admin/psychologists/{id}/approve', [AdminController::class, 'approvePsychologist'])->name('psychologists.approve');
        Route::post('admin/psychologists/{id}/reject', [AdminController::class, 'rejectPsychologist'])->name('psychologists.reject');
        Route::get('admin/psychologists/{id}', [AdminController::class, 'detailPsychologist'])->name('psychologists.detail');
    });

    Route::post('logout', [AuthController::class, 'logout']);
});

// Patients routes
Route::get('/patients', [PatientController::class, 'index']);
// Route::get('/patients/{id}', [PatientController::class, 'show']);
// Route::put('/patients/{id}', [PatientController::class, 'update']);
// Route::delete('/patients/{id}', [PatientController::class, 'destroy']);
// Route::get('/patients/{id}/appointments', [PatientController::class, 'appointments']);
// Route::get('patients/psychologists-list', [PatientController::class, 'psychologists']);
// Route::get('patients/psychologists/{id}', [PatientController::class, 'psychologistDetail'])->name('patients.psychologist.detail');
// Route::post('patients/psychologists/{id}/book', [PatientController::class, 'psychologistBook'])->name('patients.psychologist.book');
Route::get('/patients/{id}/ai-analysis', [PatientController::class, 'aiAnalysis']);

// Appointment routes
Route::get('/appointments', [AppointmentController::class, 'index']);
Route::post('/appointments', [AppointmentController::class, 'store']);
Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
Route::put('/appointments/{id}', [AppointmentController::class, 'update']);
Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']);
Route::get('/appointments/psychologist/{psychologist_id}', [AppointmentController::class, 'getByPsychologist']);
Route::get('/appointments/patient/{patient_id}', [AppointmentController::class, 'getByPatient']);

//AI Analyzer routes
Route::post('/ai-analysis/analyze', [AIAnalyzerController::class, 'analyzeText']);
Route::get('/ai-analysis', [AIAnalyzerController::class, 'index']);
Route::get('/ai-analysis/{id}', [AIAnalyzerController::class, 'show']);
Route::delete('/ai-analysis/{id}', [AIAnalyzerController::class, 'destroy']);
Route::get('/ai-analysis/patient/{patient_id}', [AIAnalyzerController::class, 'getByPatient']);

// Route::get('admin/psychologists', [AdminController::class, 'verifiedPsychologists']);
// Route::get('admin/psychologists-notverified', [AdminController::class, 'notVerifiedPsychologists']);
// Route::get('psychologists/{id}/approve', [AdminController::class, 'approvePsychologist'])->name('psychologists.approve');
// Route::get('psychologists/{id}/reject', [AdminController::class, 'rejectPsychologist'])->name('psychologists.reject');
// Route::get('psychologists/{id}/detail', [AdminController::class, 'detailPsychologist'])->name('psychologists.detail');
