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
        $psychologistId = Auth::user()->psychologist->id;

        $appointments = Appointment::where('psychologist_id', $psychologistId)
            ->whereIn('status', ['ongoing', 'waiting'])
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
                'fisrtname' => $appointment->patient->user->firstname,
                'lastname' => $appointment->patient->user->lastname,
                'date' => $appointment->date,
                'start_time' => Carbon::parse($appointment->start_time)->format('H:i'),
                'end_time' => Carbon::parse($appointment->end_time)->format('H:i'),
                'status' => $appointment->status,
                'detail_url' => route('appointments.detail', ['id' => $appointment->id]),
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
        $psychologistId = Auth::user()->psychologist->id;

        $now = Carbon::now('Asia/Jakarta');

        $appointments = Appointment::where('psychologist_id', $psychologistId)
            ->whereIn('status', ['waiting', 'ongoing'])
            ->get();

        $patients = $appointments->map(function ($appointment) use ($now) {
            $appointmentStartDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', "{$appointment->date} {$appointment->start_time}", 'Asia/Jakarta');
            $appointmentEndDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', "{$appointment->date} {$appointment->end_time}", 'Asia/Jakarta');

            if ($appointment->status === 'waiting' && $now->between($appointmentStartDateTime, $appointmentEndDateTime)) {
                $appointment->status = 'ongoing';
                $appointment->save();
            } elseif (in_array($appointment->status, ['waiting', 'ongoing']) && $now->greaterThan($appointmentEndDateTime)) {
                $appointment->status = 'completed';
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
                'fisrtname' => $appointment->patient->user->firstname,
                'lastname' => $appointment->patient->user->lastname,
                'phone' => $appointment->patient->user->phone_number,
                'date' => $appointment->date,
                'start_time' => Carbon::parse($appointment->start_time)->format('H:i'),
                'end_time' => Carbon::parse($appointment->end_time)->format('H:i'),
                'status' => $appointment->status,
            ] + $actionUrls;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Patients retrieved successfully',
            'data' => [
                'patients' => $patients,
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
        $appointment = Appointment::with(['patient.user', 'patient.aiAnalyzers'])->find($id);

        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found',
            ], 404);
        }

        $latestAiAnalyzer = $appointment->patient->aiAnalyzers->sortByDesc('created_at')->first();

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
