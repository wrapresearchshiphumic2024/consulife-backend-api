<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AiAnalyzer;
use App\Models\Psychologist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PatientController extends Controller
{
    public function appointments()
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login required',
            ], 404);
        }

        $userId = Auth::id();
        $patient = Patient::where('user_id', $userId)->first();

        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found',
            ], 404);
        }

        $appointments = Appointment::where('patient_id', $patient->id)->get();
        $now = \Carbon\Carbon::now('Asia/Jakarta');

        foreach ($appointments as $appointment) {
            $appointmentStartDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', "{$appointment->date} {$appointment->start_time}", 'Asia/Jakarta');
            $appointmentEndDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', "{$appointment->date} {$appointment->end_time}", 'Asia/Jakarta');

            if ($appointment->status === 'waiting' && $now->between($appointmentStartDateTime, $appointmentEndDateTime)) {
                $appointment->status = 'ongoing';
                $appointment->save();
            } elseif (in_array($appointment->status, ['waiting', 'ongoing']) && $now->greaterThan($appointmentEndDateTime)) {
                $appointment->status = 'completed';
                $appointment->save();
            }
        }

        $upcomingAppointments = $appointments->filter(function ($appointment) {
            return in_array($appointment->status, ['waiting', 'ongoing']);
        })->sort(function ($a, $b) {
            return ($a->date <=> $b->date) ?: ($a->start_time <=> $b->start_time);
        })->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'channel_id' => $appointment->channel_id,
                'date' => $appointment->date,
                'start_time' => \Carbon\Carbon::parse($appointment->start_time, 'Asia/Jakarta')->format('H:i'),
                'end_time' => \Carbon\Carbon::parse($appointment->end_time, 'Asia/Jakarta')->format('H:i'),
                'status' => $appointment->status,
                'psychologist' => [
                    'id' => $appointment->psychologist->id,
                    'user_id' => $appointment->psychologist->user->id,
                    'firstname' => $appointment->psychologist->user->firstname,
                    'lastname' => $appointment->psychologist->user->lastname,
                    'email' => $appointment->psychologist->user->email,
                    'phone_number' => $appointment->psychologist->user->phone_number,
                ],
                'detail_url' => route('patients.appointment.detail', ['id' => $appointment->id]),
            ];
        })->values();

        $historyAppointments = $appointments->filter(function ($appointment) {
            return in_array($appointment->status, ['completed', 'canceled']);
        })->sort(function ($a, $b) {
            return ($b->date <=> $a->date) ?: ($b->start_time <=> $a->start_time);
        })->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'channel_id' => $appointment->channel_id,
                'date' => $appointment->date,
                'start_time' => \Carbon\Carbon::parse($appointment->start_time, 'Asia/Jakarta')->format('H:i'),
                'end_time' => \Carbon\Carbon::parse($appointment->end_time, 'Asia/Jakarta')->format('H:i'),
                'status' => $appointment->status,
                'psychologist' => [
                    'id' => $appointment->psychologist->id,
                    'user_id' => $appointment->psychologist->user->id,
                    'firstname' => $appointment->psychologist->user->firstname,
                    'lastname' => $appointment->psychologist->user->lastname,
                    'email' => $appointment->psychologist->user->email,
                    'phone_number' => $appointment->psychologist->user->phone_number,
                ],
                'detail_url' => route('patients.appointment.detail', ['id' => $appointment->id]),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'upcoming_appointments' => $upcomingAppointments,
                'history' => $historyAppointments,
            ],
        ]);
    }

    /**
     * Display the specified appointment.
     */

    public function appointmentDetail($id)
    {
        $appointment = Appointment::with(['psychologist.user'])->find($id);

        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment not found',
            ], 404);
        }

        $psychologist = $appointment->psychologist;
        $startTime = \Carbon\Carbon::parse($appointment->start_time);
        $endTime = \Carbon\Carbon::parse($appointment->end_time);
        $duration = $startTime->diffInMinutes($endTime);

        $note = '';
        switch ($appointment->status) {
            case 'waiting':
                $note = 'Make sure you have a stable internet connection and are in a quiet place during the consultation session.';
                break;
            case 'ongoing':
                $note = 'You have an ongoing session with ' . $psychologist->user->firstname . ' ' . $psychologist->user->lastname . '.';
                break;
            case 'completed':
                $note = 'You have successfully completed a consultation session with ' . $psychologist->user->firstname . ' ' . $psychologist->user->lastname . '. This session is now considered complete.';
                break;
            case 'canceled':
                $note = $appointment->note;
                break;
            default:
                $note = 'Appointment details';
                break;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment details',
            'data' => [
                'id' => $appointment->id,
                'channel_id' => $appointment->channel_id,
                'date' => $appointment->date,
                'start_time' => $startTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'duration' => $duration . ' minutes',
                'status' => $appointment->status,
                'note' => $note,
                'psychologist' => [
                    'user_id' => $psychologist->user->id,
                    'firstname' => $psychologist->user->firstname,
                    'lastname' => $psychologist->user->lastname,
                    'gender' => $psychologist->user->gender,
                    'email' => $psychologist->user->email,
                    'specialization' => $psychologist->specialization,
                    'work_experience' => $psychologist->work_experience,
                ]
            ]
        ]);
    }


    /**
     * Display All Psychologosts.
     */
    public function psychologists(Request $request)
    {
        $userId = Auth::id();
        $patient = Patient::where('user_id', $userId)->first();

        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found',
            ], 404);
        }

        $hasAiAnalysis = AiAnalyzer::where('patient_id', $patient->id)->exists();

        $psychologistsQuery = Psychologist::with('user')
            ->where('is_verified', true)
            ->whereHas('schedule', function ($query) {
                $query->where('status', 'active');
            });

        if ($request->has('name')) {
            $name = $request->input('name');
            $psychologistsQuery->whereHas('user', function ($query) use ($name) {
                $query->where('firstname', 'like', "%{$name}%")
                    ->orWhere('lastname', 'like', "%{$name}%");
            });
        }

        if ($request->has('gender')) {
            $gender = $request->input('gender');
            $psychologistsQuery->whereHas('user', function ($query) use ($gender) {
                $query->where('gender', $gender);
            });
        }

        $psychologists = $psychologistsQuery->get();

        $psychologists->transform(function ($psychologist) use ($hasAiAnalysis) {
            return [
                'id' => $psychologist->id,
                'user_id' => $psychologist->user->id,
                'profile_picture' => $psychologist->user->profile_picture,
                'firstname' => $psychologist->user->firstname,
                'lastname' => $psychologist->user->lastname,
                'gender' => $psychologist->user->gender,
                'specialization' => $psychologist->specialization,
                'work_experience' => $psychologist->work_experience,
                'is_verified' => $psychologist->is_verified,
                'detail_url' => route('patients.psychologist.detail', ['id' => $psychologist->user->id]),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'List of Psychologists',
            'patient_has_aianalysis' => $hasAiAnalysis,
            'data' => $psychologists,
        ], 200);
    }

    /**
     * Display specified psychologosts.
     */
    public function psychologistDetail(string $id)
    {
        // $psychologist = Psychologist::with('user')->where('id', $id)->get();
        $psychologist = Psychologist::with(['user', 'schedule.days.times'])->whereHas('user', function ($query) use ($id) {
            $query->where('id', $id);
        })->first();

        if (!$psychologist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Psychologist not found',
            ], 404);
        }

        $scheduleData = [];
        if ($psychologist->schedule) {
            foreach ($psychologist->schedule->days as $day) {
                $scheduleData[] = [
                    'day' => $day->day,
                    'times' => $day->times->map(function ($time) {
                        return [
                            'start' => $time->start,
                            'end' => $time->end,
                        ];
                    }),
                ];
            }
        }

        $startDate = today();
        $endDate = today()->addWeeks(2);

        $upcomingAppointments = Appointment::where('psychologist_id', $psychologist->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date') // Urutkan berdasarkan tanggal
            ->get(['date', 'start_time', 'end_time'])
            ->groupBy('date')
            ->map(function ($appointments, $date) {
                return [
                    'date' => $date,
                    'times' => $appointments->map(function ($appointment) {
                        return [
                            'start_time' => \Carbon\Carbon::parse($appointment->start_time)->format('H:i'),
                            'end_time' => \Carbon\Carbon::parse($appointment->end_time)->format('H:i'),
                        ];
                    })->values()
                ];
            })
            ->values();



        $psychologist->book = route('patients.psychologist.book', ['id' => $psychologist->user->id]);
        return response()->json([
            'status' => 'success',
            'message' => 'Psychologist Details',
            'data' => $psychologist,
            'upcoming_appointments' => $upcomingAppointments,
        ], 200);
    }

    /**
     * Book specified psychologist acording to the schedule that patient choose.
     */
    public function psychologistBook(Request $request, string $id)
    {
        $psychologist = Psychologist::whereHas('user', function ($query) use ($id) {
            $query->where('id', $id);
        })->first();

        if (!$psychologist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Psychologist not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'channel_id' => 'required|string',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login required',
            ], 401);
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = Auth::id();
        $patient = Patient::where('user_id', $userId)->first();

        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found',
            ], 404);
        }

        $appointmentDate = $request->input('date');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        $now = \Carbon\Carbon::now('Asia/Jakarta');
        $appointmentStartDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', "$appointmentDate $startTime", 'Asia/Jakarta');

        if ($appointmentStartDateTime->lessThan($now)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot book an appointment for a past time slot.',
            ], 422);
        }

        $dayOfWeek = strtolower(date('l', strtotime($appointmentDate)));

        $daySchedule = $psychologist->schedule->days()->where('day', $dayOfWeek)->first();

        if (!$daySchedule) {
            return response()->json([
                'status' => 'error',
                'message' => 'Psychologist is not available on the selected day.',
            ], 422);
        }

        $timeAvailable = $daySchedule->times()
            ->where('start', '<=', $startTime)
            ->where('end', '>=', $endTime)
            ->exists();

        if (!$timeAvailable) {
            return response()->json([
                'status' => 'error',
                'message' => 'The selected time slot is not available.',
            ], 422);
        }

        $existingAppointment = Appointment::where('psychologist_id', $psychologist->id)
            ->where('date', $appointmentDate)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->exists();

        if ($existingAppointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'This time slot is already booked. Please select another time slot.',
            ], 409);
        }

        $appointment = new Appointment();
        $appointment->patient_id = $patient->id;
        $appointment->psychologist_id = $psychologist->id;
        $appointment->channel_id = $request->input('channel_id');
        $appointment->date = $appointmentDate;
        $appointment->start_time = $startTime;
        $appointment->end_time = $endTime;
        $appointment->status = 'waiting';
        $appointment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment booked successfully',
            'data' => $appointment,
        ], 201);
    }

    /**
     * Analyze text using the SVM model.
     */
    public function aiAnalyzeSVM(Request $request)
    {
        $patient = Patient::where('user_id', Auth::id())->first();
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found.',
            ], 404);
        }
        $validatedData = $request->validate([
            'text' => 'required|string|max:1000',
        ]);

        $text = $validatedData['text'];

        try {
            $response = Http::withOptions(['verify' => false])->post('https://localhost:5000/predict', [
                'text' => $text,
            ]);

            if ($response->successful()) {
                $results = $response->json();

                $formattedResults = array_map(function ($value) {
                    return round($value * 100, 2);
                }, $results);

                $aiAnalyzer = new AiAnalyzer();
                $aiAnalyzer->patient_id = $patient->id;
                $aiAnalyzer->complaint = $text;
                $aiAnalyzer->stress = $formattedResults['Stress'] ?? null;
                $aiAnalyzer->anxiety = $formattedResults['Anxiety'] ?? null;
                $aiAnalyzer->depression = $formattedResults['Depression'] ?? null;
                $aiAnalyzer->save();

                return response()->json([
                    'success' => true,
                    'complaint' => $text,
                    'results' => $formattedResults,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process text analysis.',
                    'error' => $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while analyzing the text.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display AI analysis history for the specified patient.
     */
    public function aiAnalysisHistory()
    {
        $patient = Patient::where('user_id', Auth::id())->first();
        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found',
            ], 404);
        }

        $analysis = AiAnalyzer::where('patient_id', $patient->id)->get();
        return response()->json([
            'status' => 'success',
            'user_id' => Auth::id(),
            'data' => $analysis,
        ]);
    }
}
