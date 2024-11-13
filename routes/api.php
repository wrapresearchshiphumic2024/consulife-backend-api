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
        Route::get('psychologist/appointments-schedule', [PsychologistController::class, 'getAllPatientsByPsychologist']);
        Route::post('psychologist/appointment-done/{id}', [PsychologistController::class, 'getDoneUrl'])->name('appointments.update.done');
        Route::post('psychologist/appointment-cancel/{id}', [PsychologistController::class, 'getCancelUrl'])->name('appointments.update.cancel');
        Route::post('psychologist/appointment-accept/{id}', [PsychologistController::class, 'getAcceptUrl'])->name('appointments.update.accept');
        Route::get('psychologist/appointment/detail/{id}', [PsychologistController::class, 'getAppointmentDetails'])->name('appointments.detail');
        Route::post('psychologist/create/schedule', [ScheduleController::class, 'storeDayAndTimes']);
        Route::put('psychologist/schedule/update', [ScheduleController::class, 'updateDaysAndTimes']);
        Route::get('psychologist/schedule', [ScheduleController::class, 'getPsychologistSchedule']);
        Route::post('psychologist/schedule/open', [ScheduleController::class, 'openSchedule']);
        Route::post('psychologist/schedule/close', [ScheduleController::class, 'closeSchedule']);
        Route::get('psychologist/appointment-history', [ScheduleController::class, 'appoimentHistory']);
        Route::get('psychologist/patients/{id}/ai-analysis', [AIAnalyzerController::class, 'getByPatient'])->name('patients.ai-analysis');
    });

    Route::middleware('role:patient')->group(function () {
        Route::get('patients/appointments', [PatientController::class, 'appointments']);
        Route::get('patients/appointments/{id}/detail', [PatientController::class, 'appointmentDetail'])->name('patients.appointment.detail');
        Route::get('patients/psychologists-list', [PatientController::class, 'psychologists']);
        Route::get('patients/psychologists/{id}', [PatientController::class, 'psychologistDetail'])->name('patients.psychologist.detail');
        Route::post('patients/psychologists/{id}/book', [PatientController::class, 'psychologistBook'])->name('patients.psychologist.book');
        Route::get('patients/ai-analysis', [PatientController::class, 'aiAnalysis']);
        // Route::get('patients/ai-analysis/history', [PatientController::class, 'aiAnalysis']);
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
