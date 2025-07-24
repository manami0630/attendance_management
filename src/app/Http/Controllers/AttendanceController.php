<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetailRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\UserAttendanceRecord;
use App\Models\UserBreakTime;

class AttendanceController extends Controller
{
    public function attendance_register_user()
    {
        $now = Carbon::now();
        $record = \App\Models\UserAttendanceRecord::where('user_id', auth()->id())
                ->latest()
                ->first();

        return view('attendance_register_user', compact('record','now'));
    }
    public function attendance_list_user()
    {
        $user = auth()->user();
        $records = \App\Models\UserAttendanceRecord::where('user_id', auth()->id())
                ->orderBy('date', 'desc')
                ->get();

        return view('attendance_list_user', compact('records','user'));
    }
    public function getAttendances(Request $request)
    {
        $year = $request->query('year');
        $month = $request->query('month');

        $records = UserAttendanceRecord::where('user_id', auth()->id())
                ->orderBy('date', 'desc')
                ->get();

        return response()->json($records);
    }

    public function application_list_user()
    {
        return view('application_list_user');
    }

    public function attendance_detail_user($id)
    {
        $user = auth()->user();
        $today = date('Y-m-d');
        $date = request('date');
        $breaks = \App\Models\UserBreakTime::where('user_id', $user->id)
                ->where('date', $today)
                ->get();
        $record = \App\Models\UserAttendanceRecord::where('id', $id)->first();

        return view('attendance_detail_user', compact('user', 'record', 'breaks'));
    }

    public function d()
    {
        return view('attendance_register_user');
    }

    public function attendance_list_admin()
    {
        $records = \App\Models\UserAttendanceRecord::where('user_id', auth()->id())
                ->orderBy('date', 'desc')
                ->get();

        return view('attendance_list_admin', compact('records'));
    }
    public function saveAttendance(Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        $statusMap = [
            '出勤' => ['status' => '勤務中', 'time_field' => 'clock_in_time'],
            '退勤' => ['status' => '退勤済', 'time_field' => 'clock_out_time'],
            '休憩入' => ['status' => '休憩中', 'time_field' => 'break_start_time'],
            '休憩戻' => ['status' => '勤務中', 'time_field' => 'break_end_time'],
        ];

        $user = auth()->user();
        $todayDate = date('Y-m-d');
        $statusInfo = $statusMap[$validated['status']] ?? ['status' => '勤務外'];

        if ($validated['status'] === '出勤') {
            $existingRecord = UserAttendanceRecord::where('user_id', $user->id)
                ->where('date', $todayDate)
                ->where('status', '勤務中')
                ->first();
        }

        if ($validated['status'] === '休憩入') {
            $existingBreak = \App\Models\UserBreakTime::where('user_id', $user->id)
                ->where('date', $todayDate)
                ->whereNull('break_end_time')
                ->first();

            \App\Models\UserBreakTime::create([
                'user_id' => $user->id,
                'date' => $todayDate,
                'break_start_time' => now(),
                'break_end_time' => null,
            ]);

            $status = '休憩中';
        }

        if ($validated['status'] === '休憩戻') {
            $breakRecord = \App\Models\UserBreakTime::where('user_id', $user->id)
                ->where('date', $todayDate)
                ->whereNull('break_end_time')
                ->first();

            $breakRecord->break_end_time = now();
            $breakRecord->save();

            $status = '勤務中';
        }

        $timeField = $statusInfo['time_field'] ?? null;

        $updateData = [
            'user_id' => $user->id,
            'date' => $todayDate,
            'status' => $statusInfo['status'],
        ];

        if ($timeField) {
            $updateData[$timeField] = now();
        }

        $record = UserAttendanceRecord::updateOrCreate(
            ['user_id' => $user->id, 'date' => $todayDate],
            $updateData
        );

        $currentStatus = $record->status;

        return response()->json([
            'success' => true,
            'currentStatus' => $currentStatus
        ]);

        $records = [];

        foreach ($yourRecords as $record) {
            $clockIn = $record->clock_in_time ? Carbon::parse($record->clock_in_time) : null;
            $clockOut = $record->clock_out_time ? Carbon::parse($record->clock_out_time) : null;

            $breakDurationMinutes = 0;
            if ($record->break_start_time && $record->break_end_time) {
                $breakStart = Carbon::parse($record->break_start_time);
                $breakEnd = Carbon::parse($record->break_end_time);
                $breakDurationMinutes =         $breakEnd->diffInMinutes($breakStart);
            }

            $totalMinutes = null;
            if ($clockIn && $clockOut) {
                $totalMinutes = $clockOut->diffInMinutes($clockIn) - $breakDurationMinutes;
            }

            $record->break_time = $breakDurationMinutes ? gmdate('H:i', $breakDurationMinutes * 60) : '-';
            $record->total_time = $totalMinutes !== null ? gmdate('H:i', $totalMinutes * 60) : '-';
        }
    }

    public function getCurrentStatus()
    {
        $user = auth()->user();
        $today = date('Y-m-d');

        $record = UserAttendanceRecord::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$record || $record->date !== $today) {
            $record = \App\Models\UserAttendanceRecord::updateOrCreate(
                ['user_id' => $user->id, 'date' => $today],
                ['status' => '勤務外']
            );
        }

        $status = $record ? $record->status : '勤務外';

        return response()->json([
            'success' => true,
            'currentStatus' => $status,
        ]);
    }
}