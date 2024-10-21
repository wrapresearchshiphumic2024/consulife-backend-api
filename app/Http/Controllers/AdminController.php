<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Psychologist;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
