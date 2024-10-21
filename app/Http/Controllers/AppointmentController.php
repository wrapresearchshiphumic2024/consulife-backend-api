<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Psychologist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $appointments = Appointment::with(['patient.user', 'psychologist.user'])->get();
        return response()->json([
            'status' => 'success',
            'data' => $appointments,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'patient_id' => 'required|exists:patients,id',
            'psychologist_id' => 'required|exists:psychologists,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $appointment = Appointment::create([
            'date' => $request->date,
            'time' => $request->time,
            'patient_id' => $request->patient_id,
            'psychologist_id' => $request->psychologist_id,
            'status' => 'waiting',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment created successfully',
            'data' => $appointment,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $appointment = Appointment::with(['patient.user', 'psychologist.user'])->find($id);
        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $appointment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'sometimes|date',
            'time' => 'sometimes|date_format:H:i',
            'status' => 'sometimes|in:waiting,ongoing,completed,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('date')) $appointment->date = $request->date;
        if ($request->has('time')) $appointment->time = $request->time;
        if ($request->has('status')) $appointment->status = $request->status;

        $appointment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment updated successfully',
            'data' => $appointment,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found',
            ], 404);
        }

        $appointment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment deleted successfully',
        ]);
    }

    /**
     * Get all appointments for a specific psychologist.
     */
    public function getByPsychologist($psychologist_id)
    {
        $psychologist = Psychologist::find($psychologist_id);
        if (!$psychologist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Psychologist not found',
            ], 404);
        }

        $appointments = Appointment::where('psychologist_id', $psychologist_id)->with('patient.user')->get();
        return response()->json([
            'status' => 'success',
            'data' => $appointments,
        ]);
    }

    /**
     * Get all appointments for a specific patient.
     */
    public function getByPatient($patient_id)
    {
        $patient = Patient::find($patient_id);
        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found',
            ], 404);
        }

        $appointments = Appointment::where('patient_id', $patient_id)->with('psychologist.user')->get();
        return response()->json([
            'status' => 'success',
            'data' => $appointments,
        ]);
    }
}
