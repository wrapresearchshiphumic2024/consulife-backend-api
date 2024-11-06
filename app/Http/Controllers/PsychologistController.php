<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Support\Str;
use App\Models\Psychologist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PsychologistController extends Controller
{
    public function getConsultationsByPsychologist()
    {
        $psychologistId = Auth::id();

        $appointments = Appointment::with(['patient.user', 'psychologist.user'])
            ->where('psychologist_id', $psychologistId)
            ->where('status', 'ongoing')
            ->get();

        $totalweeklyConsultation = Appointment::where('psychologist_id', $psychologistId)
            ->where('created_at', '>=', now()->subWeek())
            ->count();

        $totalConsultation = Appointment::where('psychologist_id', $psychologistId)
            ->count();

        $todayongoingConsultation = Appointment::where('psychologist_id', $psychologistId)
            ->where('status', 'ongoing')
            ->whereDate('created_at', now())
            ->count();

        $consultations = $appointments->map(function ($appointment) {
            return [
                'id' => $appointment->patient->user->id,
                'name' => $appointment->patient->user->firstname . ' ' . $appointment->patient->user->lastname,
                'date' => $appointment->date,
                'time' => $appointment->start_time . ' - ' . $appointment->end_time,
                'status' => $appointment->status,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Consultations retrieved successfully',
            'data' => [
                'consultations' => $consultations,
                'total_weekly_consultation' => $totalweeklyConsultation,
                'total_consultation' => $totalConsultation,
                'today_ongoing_consultation' => $todayongoingConsultation,
            ]
        ], 200);
    }

    public function getAllPatientsByPsychologist()
    {
        $psychologistId = Auth::id();

        $currentDate = Carbon::now()->format('Y-m-d');
        $currentTime = Carbon::now()->format('H:i');

        $appointments = Appointment::with(['patient.user', 'psychologist.user'])
            ->where('psychologist_id', $psychologistId)
            ->get();

        $patients = $appointments->map(function ($appointment) use ($currentDate, $currentTime) {
            if ($appointment->status === 'waiting' && $appointment->date->format('Y-m-d') === $currentDate && $appointment->time->format('H:i') === $currentTime) {
                $appointment->status = 'ongoing';
                $appointment->save();
            }

            $actionUrls = [
                'cancel_url' => route('appointments.update.cancel', ['id' => $appointment->id]),
                'detail_url' => route('appointments.detail', ['id' => $appointment->id]),
            ];

            if ($appointment->status === 'waiting') {
                $actionUrls['accept_url'] = route('appointments.update.accept', ['id' => $appointment->id]);
            } elseif ($appointment->status === 'ongoing') {
                $actionUrls['done_url'] = route('appointments.update.done', ['id' => $appointment->id]);
            }

            return [
                'id' => $appointment->patient->user->id,
                'name' => $appointment->patient->user->firstname . ' ' . $appointment->patient->user->lastname,
                'phone' => $appointment->patient->user->phone_number,
                'date' => $appointment->date,
                'time' => $appointment->start_time . ' - ' . $appointment->end_time,
                'status' => $appointment->status,
            ] + $actionUrls;
        });

        $groupedPatients = $patients->groupBy('status');

        return response()->json([
            'status' => 'success',
            'message' => 'Patients retrieved successfully',
            'data' => [
                'patients' => $groupedPatients,
            ]
        ], 200);
    }

    public function getDoneUrl($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found',
            ], 404);
        }

        $appointment->status = 'completed';
        $appointment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment completed successfully',
        ], 200);
    }

    public function getAcceptUrl($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found',
            ], 404);
        }

        $appointment->status = 'ongoing';
        $appointment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment accepted successfully',
        ], 200);
    }

    public function getCancelUrl(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found',
            ], 404);
        }

        $note = $request->input('note', null);
        $appointment->status = 'cancelled';

        if ($note) {
            $appointment->note = $note;
        }

        $appointment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment cancelled successfully',
            'data' => [
                'note' => $appointment->note,
            ]
        ], 200);
    }

    public function getAppointmentDetails($id)
    {
        $appointment = Appointment::with(['patient.user', 'patient.aiAnalyzer'])->find($id);

        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found',
            ], 404);
        }

        $latestAiAnalyzer = $appointment->patient->aiAnalyzer->sortByDesc('created_at')->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment retrieved successfully',
            'data' => [
                'id' => $appointment->patient->user->id,
                'name' => $appointment->patient->user->firstname . ' ' . $appointment->patient->user->lastname,
                'gender' => $appointment->patient->user->gender,
                'phone' => $appointment->patient->user->phone_number,
                'email' => $appointment->patient->user->email,
                'date' => $appointment->date,
                'time' => $appointment->start_time . ' - ' . $appointment->end_time,
                'channel_id' => $appointment->channel_id,
                'status' => $appointment->status,
                'done_url' => route('appointments.update.done', ['id' => $appointment->id]),
                'cancel_url' => route('appointments.update.cancel', ['id' => $appointment->id]),
                'ai_analysis_url' => route('patients.ai-analysis', ['id' => $appointment->patient->user->id]),
                'ai_analyzer' => $latestAiAnalyzer ? [
                    'complaint' => $latestAiAnalyzer->complaint,
                    'stress' => $latestAiAnalyzer->stress,
                    'anxiety' => $latestAiAnalyzer->anxiety,
                    'depression' => $latestAiAnalyzer->depression,
                ] : null,
            ]
        ], 200);
    }
}
