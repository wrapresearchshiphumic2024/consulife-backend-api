<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Psychologist;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $psychologists = Psychologist::count();

        $total_patient = Patient::count();

        $ongoing_appointments = Appointment::where('status', 'pending')->count();

        $completed_appointments = Appointment::where('status', 'completed')->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Admin dashboard data',
            'data' => [
                'psychologists' => $psychologists,
                'total_patient' => $total_patient,
                'ongoing_appointments' => $ongoing_appointments,
                'completed_appointments' => $completed_appointments,
            ]
        ], 200);
    }

    public function allPsychologists()
    {
        $psychologists = Psychologist::with('user')->get();

        $psychologists->transform(function ($psychologist) {
            return [
                'id' => $psychologist->id,
                'profile_picture' => $psychologist->user->profile_picture,
                'firstname' => $psychologist->user->firstname,
                'lastname' => $psychologist->user->lastname,
                'profesional_identification_number' => $psychologist->profesional_identification_number,
                'degree' => $psychologist->degree,
                'specialization' => $psychologist->specialization,
                'work_experience' => $psychologist->work_experience,
                'is_verified' => $psychologist->is_verified,
                'approve_url' => route('psychologists.approve', ['id' => $psychologist->id]),
                'reject_url' => route('psychologists.reject', ['id' => $psychologist->id]),
                'detail_url' => route('psychologists.detail', ['id' => $psychologist->id]),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'List of psychologists',
            'data' => $psychologists,
        ], 200);
    }

    public function approvePsychologist(Request $request, string $id)
    {
        $psychologist = Psychologist::find($id);

        if (!$psychologist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Psychologist not found',
            ], 404);
        }

        $psychologist->is_verified = true;
        $psychologist->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Psychologist approved successfully',
            'data' => $psychologist,
        ], 200);
    }

    public function rejectPsychologist(Request $request, string $id)
    {
        $psychologist = Psychologist::find($id);

        if (!$psychologist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Psychologist not found',
            ], 404);
        }

        $psychologist->is_verified = false;
        $psychologist->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Psychologist rejected successfully',
            'data' => $psychologist,
        ], 200);
    }

    public function detailPsychologist(string $id)
    {
        $psychologist = Psychologist::with('user')->find($id);

        if (!$psychologist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Psychologist not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Psychologist details',
            'data' => [
                'psychologist' => $psychologist,
                'approve_url' => route('psychologists.approve', ['id' => $psychologist->id]),
                'reject_url' => route('psychologists.reject', ['id' => $psychologist->id]),
            ],
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
