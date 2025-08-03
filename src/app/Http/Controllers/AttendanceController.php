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
    public function attendance_detail(Request $request,$user_id)
    {
        $user = auth()->user();

        $date = $request->query('date') ?? date('Y-m-d');

        if ($user->role === 'user') {
            $record = \App\Models\UserAttendanceRecord::find($user_id);

            if (!$record) {
                return view('attendance_detail_user', [
                    'user' => $user,
                    'record' => null,
                    'breaks' => collect(),
                    'applicationStatus' => null,
                    'date' => $date,
                ]);
            }

            $breaks = \App\Models\UserBreakTime::where('user_id', $record->user_id)
                ->where('date', $record->date)
                ->get();

            $application = null;
            $applicationStatus = null;

            if ($record->application_id) {
                $application = \App\Models\UserApplication::find($record->application_id);
                $applicationStatus = $application ? $application->status : null;
            }

            $breakApplications = \App\Models\UserBreakApplication::where('user_id', $record->user_id)
                ->where('date', $record->date)
                ->get();

            return view('attendance_detail_user', compact('user', 'record', 'breaks', 'applicationStatus', 'application','date', 'breakApplications'));

        } elseif ($user->role === 'admin') {
            $targetUser = \App\Models\User::findOrFail($user_id);

            $record = \App\Models\UserAttendanceRecord::where('user_id', $targetUser->id)
                ->where('date', $date)
                ->first();

            $breaks = \App\Models\UserBreakTime::where('user_id', $targetUser->id)
                ->where('date', $date)
                ->get();

            return view('attendance_detail_admin', compact('targetUser', 'record', 'breaks', 'date'));

        } else {
            abort(403);
        }
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

    public function getAttendance(Request $request)
    {
        $date = $request->query('date');
        $records = UserAttendanceRecord::whereDate('date', $date)->with('user')->get();

        return response()->json(['records' => $records]);
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

    public function staff_list_admin(Request $request)
    {
        $users = \App\Models\User::all();

        $records = \App\Models\UserAttendanceRecord::where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->get();

        return view('staff_list_admin', compact('records','users'));
    }

    public function attendances_by_staff_admin(Request $request,$id)
    {
        $user = \App\Models\User::findOrFail($id);

        $date = $request->query('date', date('Y-m-d'));

        $records = \App\Models\UserAttendanceRecord::whereDate('date', $date)
        ->with('user')
        ->get();

        return view('attendances_by_staff_admin', compact('user', 'records', 'date'));
    }


    public function showApplicationDetails($id)
    {
        $user = auth()->user();

        $application = \App\Models\UserApplication::find($id);

        $breakApplications = \App\Models\UserBreakApplication::where('application_id', $application->id)
            ->get();
        $applicationStatus = $application->status;

        return view('application_review_admin', compact('application','breakApplications','user', 'applicationStatus'));
    }

    public function approve($id)
    {
        $application = \App\Models\UserApplication::findOrFail($id);

        $attendanceRecord = \App\Models\UserAttendanceRecord::where('user_id', $application->user_id)
            ->where('date', $application->target_date)
            ->first();
        if ($attendanceRecord) {
            $attendanceRecord->clock_in_time = $application->clock_in_time;
            $attendanceRecord->clock_out_time = $application->clock_out_time;
            $attendanceRecord->save();
        }
        $application->status = '承認済み';
        $application->save();

        $breakApplications = \App\Models\UserBreakApplication::where('application_id', $id)->get();

        foreach ($breakApplications as $breakApplication) {
            if ($breakApplication->break_time_id) {
                \App\Models\UserBreakTime::updateOrCreate(
                    ['id' => $breakApplication->break_time_id],
                    [
                        'user_id' => $breakApplication->user_id,
                        'date' => $application->target_date,
                        'break_start_time' => $breakApplication->break_start_time,
                        'break_end_time' => $breakApplication->break_end_time,
                    ]
                );
            }
            return view('application_list_admin');
        }
    }
    //出勤登録画面
    public function saveAttendance(Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        $statusMap = [
            '出勤' => ['status' => '出勤中', 'time_field' => 'clock_in_time'],
            '退勤' => ['status' => '退勤済', 'time_field' => 'clock_out_time'],
            '休憩入' => ['status' => '休憩中', 'time_field' => 'break_start_time'],
            '休憩戻' => ['status' => '出勤中', 'time_field' => 'break_end_time'],
        ];

        $user = auth()->user();
        $todayDate = date('Y-m-d');
        $statusInfo = $statusMap[$validated['status']] ?? ['status' => '勤務外'];

        $record = UserAttendanceRecord::where('user_id', $user->id)
            ->where('date', $todayDate)
            ->first();

        if (!$record) {
            $record = UserAttendanceRecord::create([
                'user_id' => $user->id,
                'date' => $todayDate,
                'status' => '勤務外',
            ]);
        }

        $user_attendance_record_id = $record->id;

        if ($validated['status'] === '出勤') {
            $record->update([
                'status' => '出勤中',
                'clock_in_time' => now(),
            ]);
        }

        if ($validated['status'] === '休憩入') {
            $existingBreak = \App\Models\UserBreakTime::where('user_id', $user->id)
                ->where('date', $todayDate)
                ->whereNull('break_end_time')
                ->first();

            \App\Models\UserBreakTime::create([
                'user_id' => $user->id,
                'user_attendance_record_id' => $user_attendance_record_id,
                'date' => $todayDate,
                'break_start_time' => now(),
                'break_end_time' => null,
            ]);
            $record->update(['status' => '休憩中']);
        }

        if ($validated['status'] === '休憩戻') {
            $breakRecord = \App\Models\UserBreakTime::where('user_id', $user->id)
                ->where('date', $todayDate)
                ->whereNull('break_end_time')
                ->first();

            if ($breakRecord) {
                $breakRecord->update([
                    'break_end_time' => now(),
                ]);
            }
            $record->update(['status' => '出勤中']);
        }

        $timeField = $statusInfo['time_field'] ?? null;

        $updateData = [
            'status' => $statusInfo['status'],
        ];

        if ($timeField) {
            $updateData[$timeField] = now();
        }

        $record->update($updateData);

        $currentStatus = $record->status;

        return response()->json([
            'success' => true,
            'currentStatus' => $currentStatus
        ]);
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
            'reason' => $request->input('reason'),
            'target_date' => \Carbon\Carbon::parse($request->input('date') ?? now()),
            'status' => '承認待ち',
        ]);

        $record = \App\Models\UserAttendanceRecord::findOrFail($request->input('user_attendance_record_id'));
        $record->application_id = $application->id;
        $record->save();

        $breakTimes = (array) $request->input('break_start_time');
        $endTimes = (array) $request->input('break_end_time');

        if (count($breakTimes) === count($endTimes)) {
            for ($i = 0; $i < count($breakTimes); $i++) {

                $breakTime = \App\Models\UserBreakTime::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'date' => $request->input('date'),
                ]
                );

                $breakApplication = new UserBreakApplication();
                $breakApplication->user_id = auth()->id();
                $breakApplication->application_id = $application->id;
                $breakApplication->date = $request->input('date');
                $breakApplication->break_start_time = $breakTimes[$i];
                $breakApplication->break_end_time = $endTimes[$i];
                $breakApplication->break_time_id = $breakTime->id;
                $breakApplication->save();
            }
            $breakApplication->save();

            $breakApplicationId = $breakApplication->id;

            $application->save();
        } else {
            return redirect('/attendance/list')->withErrors(['break_times' => '休憩時間の開始時間と終了時間の数が一致しません。']);
        }
        return redirect('/attendance/list');
    }

    public function save(DetailRequest $request, $id)
    {
        $record = \App\Models\UserAttendanceRecord::findOrFail($id);

        $record->clock_in_time = $request->input('clock_in_time');
        $record->clock_out_time = $request->input('clock_out_time');
        $record->save();

        $attendance = $record;

        $break_start_times = $request->input('break_start_time', []);
        $break_end_times = $request->input('break_end_time', []);

        $breaks = \App\Models\UserBreakTime::where('user_attendance_record_id', $attendance->id)->get();

        foreach ($break_start_times as $index => $start_time) {
            if (isset($breaks[$index])) {
                $breaks[$index]->break_start_time = $start_time;
                $breaks[$index]->break_end_time = $break_end_times[$index] ?? '';
                $breaks[$index]->save();
            } else {
                \App\Models\UserBreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start_time' => $start_time,
                    'break_end_time' => $break_end_times[$index] ?? '',
                ]);
            }
        }

        $application = \App\Models\UserApplication::create([
            'user_id' => $attendance->user_id,
            'user_attendance_record_id' => $attendance->id,
            'status' => '承認済み',
            'target_date' => $attendance->date,
            'reason' => $request->input('reason'),
            'clock_in_time' => $attendance->clock_in_time,
            'clock_out_time' => $attendance->clock_out_time,
        ]);

        foreach ($break_start_times as $index => $start_time) {
            \App\Models\UserBreakApplication::create([
                'user_id' => $attendance->user_id,
                'application_id' => $application->id,
                'date' => $attendance->date,
                'break_start_time' => $start_time,
                'break_end_time' => $break_end_times[$index] ?? '',
            ]);
        }
        $record->application_id = $application->id;
        $record->save();

        return redirect('admin/attendance/list');
    }
}