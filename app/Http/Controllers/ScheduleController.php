<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\Time;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function storeDayAndTimes(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'schedule_id' => 'required|integer',
                'days' => 'required|array',
                'days.*' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'times' => 'required|array',
                'times.*.start' => 'required|string',
                'times.*.end' => 'required|string'
            ]);

            $daysData = [];

            foreach ($validatedData['days'] as $dayName) {
                $day = Day::create([
                    'schedule_id' => $validatedData['schedule_id'],
                    'day' => $dayName,
                    'status' => 'active'
                ]);

                $timesData = [];

                foreach ($validatedData['times'] as $time) {
                    $newTime = Time::create([
                        'day_id' => $day->id,
                        'start' => $time['start'],
                        'end' => $time['end'],
                        'status' => 'active'
                    ]);

                    $timesData[] = $newTime;
                }

                $day->load('times');
                $daysData[] = [
                    'day' => $day,
                    'times' => $timesData
                ];
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Schedule added successfully',
                'data' => $daysData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateDaysAndTimes(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'schedule_id' => 'required|integer',
                'days' => 'required|array',
                'days.*' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'times' => 'required|array',
                'times.*.start' => 'required|string',
                'times.*.end' => 'required|string'
            ]);

            foreach ($validatedData['days'] as $dayName) {
                $day = Day::where('schedule_id', $validatedData['schedule_id'])
                    ->where('day', $dayName)
                    ->first();

                if (!$day) {
                    $day = Day::create([
                        'schedule_id' => $validatedData['schedule_id'],
                        'day' => $dayName,
                        'status' => 'active'
                    ]);
                } else {
                    $day->update([
                        'status' => 'active'
                    ]);
                }

                Time::where('day_id', $day->id)->delete();

                $timesData = [];
                foreach ($validatedData['times'] as $time) {
                    $newTime = Time::create([
                        'day_id' => $day->id,
                        'start' => $time['start'],
                        'end' => $time['end'],
                        'status' => 'active'
                    ]);
                    $timesData[] = $newTime;
                }

                $day->load('times');
                $daysData[] = [
                    'day' => $day,
                    'times' => $timesData
                ];
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Schedules updated successfully',
                'data' => $daysData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getPsychologistSchedule()
    {
        try {
            $user = Auth::user();

            if (!$user->isPsychologist()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            $psychologist = $user->psychologist;

            if (!$psychologist) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No psychologist profile found'
                ], 404);
            }

            $schedules = Schedule::where('psychologist_id', $psychologist->id)
                ->with(['days.times'])
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Schedules retrieved successfully',
                'data' => $schedules
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function openSchedule(Request $request)
    {
        $psychologist = Auth::user()->psychologist;

        if (!$psychologist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Psychologist not found.'
            ], 404);
        }

        Schedule::where('psychologist_id', $psychologist->id)
            ->update(['status' => 'active']);

        return response()->json([
            'status' => 'success',
            'message' => 'Schedule opened successfully.'
        ]);
    }

    public function closeSchedule(Request $request)
    {
        $psychologist = Auth::user()->psychologist;

        if (!$psychologist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Psychologist not found.'
            ], 404);
        }

        Schedule::where('psychologist_id', $psychologist->id)
            ->update(['status' => 'inactive']);

        return response()->json([
            'status' => 'success',
            'message' => 'Schedule closed successfully.'
        ]);
    }
}
