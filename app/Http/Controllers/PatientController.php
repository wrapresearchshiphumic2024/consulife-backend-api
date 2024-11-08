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

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $patients = Patient::with('user')->get();
    //     return response()->json([
    //         'status' => 'success',
    //         'data' => $patients,
    //     ]);
    // }
    /**
     * Display all appointments for the specified patient.
     */
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
    public function psychologists()
    {
        // $psychologists = Psychologist::with('user')->where('is_verified', true)->get();
        $psychologists = Psychologist::with('user')
        ->where('is_verified', true)
        ->whereHas('schedule', function ($query) {
            $query->where('status', 'active');
        })
        ->get();

        $psychologists->transform(function ($psychologist) {
            return [
                'id' => $psychologist->id,
                'user_id' => $psychologist->user->id,
                'profile_picture' => $psychologist->user->profile_picture,
                'firstname' => $psychologist->user->firstname,
                'lastname' => $psychologist->user->lastname,
                'gender' => $psychologist->user->gender,
                // 'profesional_identification_number' => $psychologist->profesional_identification_number,
                // 'degree' => $psychologist->degree,
                'specialization' => $psychologist->specialization,
                'work_experience' => $psychologist->work_experience,
                'is_verified' => $psychologist->is_verified,
                'detail_url' => route('patients.psychologist.detail', ['id' => $psychologist->user->id]),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'List of Psychologists',
            'data' => $psychologists,
        ], 200);
    }

    /**
     * Display specified psychologosts.
     */
    public function psychologistDetail(string $id)
    {
        // $psychologist = Psychologist::with('user')->where('id', $id)->get();
        $psychologist = Psychologist::with(['user', 'schedule.days.times'])->whereHas('user', function($query) use ($id) {
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
     * Display AI analysis results for the specified patient.
     */
    public function aiAnalysis($id)
    {
        $patient = Patient::where('user_id', $id)->first();
        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found',
            ], 404);
        }

        $analysis = AiAnalyzer::where('patient_id', $patient->id)->get();
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
    // public function show(string $id)
    // {
    //     $patient = Patient::with('user')->find($id);
    //     if (!$patient) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Patient not found',
    //         ], 404);
    //     }

    //     return response()->json([
    //         'status' => 'success',
    //         'data' => $patient,
    //     ]);
    // }

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
        $patient = Patient::where('user_id', $id)->first();
        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'firstname' => 'sometimes|string',
            'lastname' => 'sometimes|string',
            'email' => 'sometimes|string|email|unique:users,email,' . $patient->user_id,
            'password' => 'sometimes|string|min:8',
            'phone_number' => 'sometimes|string',
            'gender' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($patient->user_id);
        if ($request->has('firstname')) $user->firstname = $request->firstname;
        if ($request->has('lastname')) $user->lastname = $request->lastname;
        if ($request->has('email')) $user->email = $request->email;
        if ($request->has('password')) $user->password = Hash::make($request->password);
        if ($request->has('phone_number')) $user->phone_number = $request->phone_number;
        if ($request->has('gender')) $user->gender = $request->gender;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Patient updated successfully',
            'data' => $patient,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(string $id)
    // {
    //     $patient = Patient::find($id);
    //     if (!$patient) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Patient not found',
    //         ], 404);
    //     }

    //     // Delete associated user
    //     $user = User::find($patient->user_id);
    //     if ($user) {
    //         $user->delete();
    //     }

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Patient deleted successfully',
    //     ]);
    // }
}
