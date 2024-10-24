<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\Time;
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
}
