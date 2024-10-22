<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Psychologist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

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
                'time' => $appointment->created_at->format('d M Y, H:i'),
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



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
