<?php

namespace App\Http\Controllers;

use Log;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\Appointment;
use App\Models\Psychologist;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $psychologists = Psychologist::where('is_verified', true)->count();

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

    public function verifiedPsychologists(Request $request)
    {
        $psychologistsQuery = Psychologist::with('user')
            ->where('is_verified', true)
            ->where('is_rejected', false);

        if ($request->has('name')) {
            $name = $request->input('name');
            $psychologistsQuery->whereHas('user', function ($query) use ($name) {
                $query->where('firstname', 'like', "%{$name}%")
                      ->orWhere('lastname', 'like', "%{$name}%");
            });
        }
    
        $psychologists = $psychologistsQuery->get();

        $psychologists->transform(function ($psychologist) {
            return [
                'id' => $psychologist->id,
                'user_id' => $psychologist->user->id,
                'profile_picture' => $psychologist->user->profile_picture,
                'firstname' => $psychologist->user->firstname,
                'lastname' => $psychologist->user->lastname,
                'gender' => $psychologist->user->gender,
                'profesional_identification_number' => $psychologist->profesional_identification_number,
                'degree' => $psychologist->degree,
                'specialization' => $psychologist->specialization,
                'work_experience' => $psychologist->work_experience,
                'is_verified' => $psychologist->is_verified,
                'detail_url' => route('psychologists.detail', ['id' => $psychologist->user->id]),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'List of verified psychologists',
            'data' => $psychologists,
        ], 200);
    }

    public function notVerifiedPsychologists()
    {
        $psychologists = Psychologist::with('user')
        ->where('is_verified', false)
        ->where('is_rejected', false) 
        ->get();

        $psychologists->transform(function ($psychologist) {
            return [
                'id' => $psychologist->id,  
                'user_id' => $psychologist->user->id, 
                'profile_picture' => $psychologist->user->profile_picture,
                'firstname' => $psychologist->user->firstname,
                'lastname' => $psychologist->user->lastname,
                'profesional_identification_number' => $psychologist->profesional_identification_number,
                'degree' => $psychologist->degree,
                'specialization' => $psychologist->specialization,
                'work_experience' => $psychologist->work_experience,
                'is_verified' => $psychologist->is_verified,
                'is_rejected' => $psychologist->is_rejected,
                'approve_url' => route('psychologists.approve', ['id' => $psychologist->user->id]), 
                'reject_url' => route('psychologists.reject', ['id' => $psychologist->user->id]), 
                'detail_url' => route('psychologists.detail', ['id' => $psychologist->user->id]),  
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'List of unverified psychologists',
            'data' => $psychologists,
        ], 200);
    }

    public function approvePsychologist(Request $request, string $id)
    {
        $psychologist = Psychologist::whereHas('user', function($query) use ($id) {
            $query->where('id', $id);
        })->first();

        if (!$psychologist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Psychologist not found',
            ], 404);
        }

        $psychologist->is_verified = true;
        $psychologist->save();

        Schedule::create([
            'psychologist_id' => $psychologist->id,
            'status' => 'inactive',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Psychologist approved successfully',
            'data' => $psychologist,
        ], 200);
    }

    public function rejectPsychologist(Request $request, string $id)
    {
        $psychologist = Psychologist::whereHas('user', function($query) use ($id) {
            $query->where('id', $id);
        })->first();

        if (!$psychologist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Psychologist not found',
            ], 404);
        }

        $psychologist->is_verified = false;
        $psychologist->is_rejected = true;
        $psychologist->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Psychologist rejected successfully',
            'data' => $psychologist,
        ], 200);
    }

    public function detailPsychologist(string $id)
    {
        $psychologist = Psychologist::with('user')->whereHas('user', function($query) use ($id) {
            $query->where('id', $id);
        })->first();

        if (!$psychologist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Psychologist not found',
            ], 404);
        }

        $psychologist->user_id = $psychologist->user->id;

        if (!$psychologist->is_verified) {
            $psychologist->approve_url = route('psychologists.approve', ['id' => $psychologist->user->id]);
            $psychologist->reject_url = route('psychologists.reject', ['id' => $psychologist->user->id]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Psychologist details',
            'data' => $psychologist,
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
