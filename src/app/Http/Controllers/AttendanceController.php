<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetailRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\UserAttendanceRecord;
use App\Models\UserBreakTime;
use Illuminate\Support\Facades\Auth;
use App\Models\UserApplication;
use App\Models\UserBreakApplication;

class AttendanceController extends Controller
{
    //出勤画面
    public function attendance_register_user()
    {
        $now = Carbon::now();
        $record = \App\Models\UserAttendanceRecord::where('user_id', auth()->id())
            ->latest()
            ->first();

        return view('attendance_register_user', compact('record','now'));
    }
    //勤怠一覧
    public function attendance_list_user()
    {
        $user = auth()->user();
        $records = \App\Models\UserAttendanceRecord::where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->get();

        return view('attendance_list_user', compact('records','user'));
    }
    //勤怠一覧
    public function getAttendances(Request $request)
    {
        $year = $request->query('year');
        $month = $request->query('month');

        $query = UserAttendanceRecord::where('user_id', auth()->id());

        if ($year) {
            $query->whereYear('date', $year);
        }

        if ($month) {
            $query->whereMonth('date', $month);
        }

        $records = $query->orderBy('date', 'desc')->get();

        return response()->json($records);
    }
    //申請一覧
    public function application_list(Request $request)
    {
        $user = auth()->user();
        $status = $request->query('status', '承認待ち');

        if ($user->role === 'admin') {
            $applications = UserApplication::with('user')
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->get();
            return view('application_list_admin', compact('applications', 'status'));
        } elseif ($user->role === 'user') {
            $applications = $user->userApplications()
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->get();
            return view('application_list_user', compact('applications', 'status'));
        } else {
            abort(403);
        }
    }
    //勤怠詳細
    public function attendance_detail_user($id)
    {
        $user = auth()->user();
        $today = date('Y-m-d');
        $date = request('date');
        $breaks = \App\Models\UserBreakTime::where('user_id', $user->id)
            ->where('date', $today)
            ->get();
        $record = \App\Models\UserAttendanceRecord::where('id', $id)->first();

        $application = \App\Models\UserApplication::where('user_id', $user->id)
        ->where('status', '承認待ち')
        ->first();
        $applicationStatus = $application ? $application->status : null;

        return view('attendance_detail_user', compact('user', 'record', 'breaks', 'applicationStatus'));
    }
    //勤怠詳細
    public function attendance_detail_admin($id)
    {
        $user = auth()->user();
        $today = date('Y-m-d');
        $date = request('date');
        $breaks = \App\Models\UserBreakTime::where('user_id', $user->id)
                ->where('date', $today)
                ->get();
        $record = \App\Models\UserAttendanceRecord::where('id', $id)->first();

        return view('attendance_detail_admin', compact('user', 'record', 'breaks'));
    }

    //勤怠一覧
    public function attendance_list_admin(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $records = \App\Models\UserAttendanceRecord::whereDate('date', $date)
        ->with('user')
        ->get();

        return view('attendance_list_admin', compact('records', 'date'));
    }

    public function attendance_for_date($date)
    {
        $record = \App\Models\UserAttendanceRecord::where('date', $date)->first();

        $breaks = \App\Models\UserBreakTime::where('break_date', $date)->get();

        $break_seconds = 0;
        foreach ($breaks as $break) {
            if ($break->break_start_time && $break->break_end_time) {
                $start = strtotime($break->break_start_time);
                $end = strtotime($break->break_end_time);
                $break_seconds += ($end - $start);
            }
        }

        $work_seconds = 0;
        if ($record && $record->clock_in_time && $record->clock_out_time) {
            $work_seconds = strtotime($record->clock_out_time) - strtotime($record->clock_in_time);
        }

        $total_seconds = $work_seconds - $break_seconds;
        $hours = floor($total_seconds / 3600);
        $minutes = floor(($total_seconds % 3600) / 60);
        $total_time = sprintf('%02d:%02d', $hours, $minutes);

        return view('attendance_list_admin', compact('record', 'breaks', 'total_time'));
    }

    public function staff_list_admin()
    {
        $users = \App\Models\User::all();

        $records = \App\Models\UserAttendanceRecord::where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->get();

        return view('staff_list_admin', compact('records','users'));
    }

    public function attendances_by_staff_admin($id)
    {
        $user = \App\Models\User::findOrFail($id);

        $records = \App\Models\UserAttendanceRecord::where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->get();

        return view('attendances_by_staff_admin', compact('user', 'records'));
    }

    public function showApplicationDetails($id)
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        $application = \App\Models\UserApplication::find($id);

        $breaks = \App\Models\UserBreakTime::where('user_id', $user->id)
            ->where('date', $today)
            ->get();
        $applicationStatus = $application->status;

        return view('application_review_admin', compact('application','breaks','user', 'applicationStatus'));
    }

    public function approve($id)
    {
        $application = \App\Models\UserApplication::findOrFail($id);

        $attendanceRecord = \App\Models\UserAttendanceRecord::where('user_id', $application->user_id)
            ->where('date', $application->target_datetime)
            ->first();
        if ($attendanceRecord) {
            $attendanceRecord->clock_in_time = $application->clock_in_time;
            $attendanceRecord->clock_out_time = $application->clock_out_time;
            $attendanceRecord->save();
        } else {
            $attendanceRecord = new \App\Models\UserAttendanceRecord();
            $attendanceRecord->user_id = $application->user_id;
            $attendanceRecord->date = $application->target_datetime;
            $attendanceRecord->clock_in_time = $application->clock_in_time;
            $attendanceRecord->clock_out_time = $application->clock_out_time;
            $attendanceRecord->save();
        }

        $application->status = '承認済み';
        $application->save();

        return view('application_list_admin');
    }

    //出勤登録画面
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
    //出勤登録画面
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

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
    //修正
    public function update(DetailRequest $request)
    {
        $application = UserApplication::create([
            'user_id' => auth()->id(),
            'user_attendance_record_id' => $request->input('user_attendance_record_id'),
            'clock_in_time' => $request->input('clock_in_time'),
            'clock_out_time' => $request->input('clock_out_time'),
            'reason' => $request->input('remarks'),
            'target_datetime' => \Carbon\Carbon::parse($request->input('date') ?? now()),
            'status' => '承認待ち',
        ]);

        $record = \App\Models\UserAttendanceRecord::findOrFail($request->input('user_attendance_record_id'));

        $record->application_id = $application->id;
        $record->save();

        $breakTimes = (array) $request->input('break_start_time');
        $endTimes = (array) $request->input('break_end_time');

        if (count($breakTimes) === count($endTimes)) {
            for ($i = 0; $i < count($breakTimes); $i++) {
                $breakApplication = new UserBreakApplication();
                $breakApplication->user_id = auth()->id();
                $breakApplication->application_id = $application->id;
                $breakApplication->date = $request->input('date');
                $breakApplication->break_start_time = $breakTimes[$i];
                $breakApplication->break_end_time = $endTimes[$i];
                $breakApplication->save();
            }
            $breakApplication->save();

                // これを使って
            $breakApplicationId = $breakApplication->id;

            // そして、$applicationに保存
            $application->user_break_application_id = $breakApplicationId;
            $application->save();
        } else {
            return redirect('/attendance/list')->withErrors(['break_times' => '休憩時間の開始時間と終了時間の数が一致しません。']);
        }
        return redirect('/attendance/list');
    }
}