<?php

namespace App\Http\Controllers;

use App\Models\AiAnalyzer;
use App\Models\Patient;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AIAnalyzerController extends Controller
{
    public function analyzeText(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'complaint' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $complaint = $request->complaint;

        // Kirim teks keluhan ke Flask API untuk dianalisis oleh model AI
        try {
            $response = Http::post('http://localhost:5000/analyze', [
                'text' => $complaint,
            ]);

            if ($response->failed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to analyze text with AI model',
                ], 500);
            }

            $result = $response->json();  // Ambil hasil dari Flask API (stress, anxiety, depression)

            // Simpan hasil analisis dan teks keluhan di database
            $analysis = AiAnalyzer::create([
                'patient_id' => $request->patient_id,
                'complaint' => $complaint,
                'stress' => $result['stress'],
                'anxiety' => $result['anxiety'],
                'depression' => $result['depression'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Text analyzed successfully',
                'data' => $analysis,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $analysis = AiAnalyzer::with('patient.user')->get();
        return response()->json([
            'status' => 'success',
            'data' => $analysis,
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $analysis = AiAnalyzer::with('patient.user')->find($id);
        if (!$analysis) {
            return response()->json([
                'status' => 'error',
                'message' => 'AI analysis result not found',
            ], 404);
        }

        // Kembalikan data hasil analisis
        return response()->json([
            'status' => 'success',
            'data' => $analysis,
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $analysis = AiAnalyzer::find($id);
        if (!$analysis) {
            return response()->json([
                'status' => 'error',
                'message' => 'AI analysis result not found',
            ], 404);
        }

        $analysis->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'AI analysis result deleted successfully',
        ]);
    }

    /**
     * Get AI analysis results for a specific patient.
     */
    public function getByPatient($id)
    {
        $patient = Patient::find($id);
        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found',
            ], 404);
        }
        $userId = $patient->user->id ?? null;

        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found for this patient',
            ], 404);
        }

        $analysis = AiAnalyzer::where('patient_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($analysis->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No AI analysis found for this patient',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'AI analysis retrieved successfully',
            'data' => [
                'user_id' => $userId,
                'analysis' => $analysis,
            ],
        ]);
    }
}
