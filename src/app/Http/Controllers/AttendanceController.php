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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function attendance_detail(Request $request,$id)
    {
        $user = auth()->user();

        $dateParam = $request->query('date');
        $date = $dateParam ?? date('Y-m-d');

        if ($user->role === 'user') {
            $record = \App\Models\UserAttendanceRecord::find($id);

            if (!$record) {
                return view('attendance_detail_user', [
                    'user' => $user,
                    'record' => null,
                    'breaks' => collect(),
                    'applicationStatus' => null,
                    'date' => $date,
                    'application' => null,
                ]);
            }

            $breaks = \App\Models\UserBreakTime::where('user_id', $record->user_id)
                ->where('date', $record->date)
                ->orderBy('created_at', 'desc')
                ->get()
                ->sortBy(function($b) {
                    return $b->break_start_time ?? '9999-12-31 23:59:59';
                });

            $applicationBreaks = \App\Models\UserBreakApplication::where('user_id', $record->user_id)
                ->where('date', $record->date)
                ->orderBy('created_at', 'desc')
                ->get();


            $recordDate = $record->date;
            $clockInTime = isset($record->clock_in_time) ? \Carbon\Carbon::parse($record->clock_in_time)->format('H:i') : '';
            $clockOutTime = isset($record->clock_out_time) ? \Carbon\Carbon::parse($record->clock_out_time)->format('H:i') : '';

            $applications = \App\Models\UserApplication::where('user_attendance_record_id', $record->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $application = $applications->first();
            $pendingApplication = $applications->firstWhere('status', '承認待ち');

            if ($pendingApplication) {
                $applicationStatus = '承認待ち';
            } else {
                $applicationStatus = $applications->isNotEmpty() ? $applications->first()->status : null;
            }
            $applicationReason = $application ? $application->reason : null;

            $breakApplications = \App\Models\UserBreakApplication::where('user_id', $record->user_id)
                ->orderBy('updated_at', 'desc')
                ->get()
                ->groupBy('user_attendance_record_id')
                ->map(function ($group) {
                    $maxUpdated = $group->max('updated_at');
                    return $group->where('updated_at', $maxUpdated);
                })
                ->values()
                ->flatMap(function ($items) { return $items; });

            return view('attendance_detail_user', compact('user', 'record', 'breaks', 'applicationStatus', 'applications','date', 'breakApplications','recordDate', 'clockInTime', 'clockOutTime', 'application', 'applicationReason', 'applicationBreaks'));

        } elseif ($user->role === 'admin') {
            $targetUser = \App\Models\User::findOrFail($id);

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

    public function attendances_by_staff_admin($id)
    {
        $user = \App\Models\User::findOrFail($id);

        $records = \App\Models\UserAttendanceRecord::where('user_id', $id)
            ->orderBy('date', 'desc')
            ->get();

        return view('attendances_by_staff_admin', compact('records','user'));
    }

    public function get(Request $request)
    {
        $year = $request->query('year');
        $month = $request->query('month');
        $userId = $request->query('user_id');

        $query = UserAttendanceRecord::query();

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('user_id', auth()->id());
        }

        if ($year) {
            $query->whereYear('date', $year);
        }

        if ($month) {
            $query->whereMonth('date', $month);
        }

        $records = $query->orderBy('date', 'desc')->get();

        return response()->json($records);
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

    public function approve(Request $request, $id)
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

        $breaks = $request->get('breaks', []);
        if (is_array($breaks) && !empty($breaks)) {
            DB::beginTransaction();
            try {
                foreach ($breaks as $breakData) {
                    $potentialId = $breakData['id'] ?? null;

                    if (!$potentialId) continue;

                    $breakTime = \App\Models\UserBreakTime::where('id', $potentialId)
                        ->where('user_id', $application->user_id)
                        ->first();

                    if (!$breakTime) {
                        $appBreak = \App\Models\UserBreakApplication::where('id', $potentialId)
                            ->where('user_id', $application->user_id)
                            ->first();

                        if ($appBreak) {
                            $breakTime = \App\Models\UserBreakTime::where('id', $appBreak->break_time_id)
                                ->where('user_id', $application->user_id)
                                ->first();
                        }
                    }

                    if (!$breakTime) {
                        Log::warning('BreakTime not found for update (fallback)', [
                            'break_time_id_or_app_id' => $potentialId,
                            'user_id' => $application->user_id,
                        ]);
                        continue;
                    }

                    if (array_key_exists('start_time', $breakData)) {
                        $breakTime->break_start_time = $breakData['start_time'] ?? null;
                    }
                    if (array_key_exists('end_time', $breakData)) {
                        $breakTime->break_end_time = $breakData['end_time'] ?? null;
                    }

                    $breakTime->save();
                    Log::info('BreakTime updated', ['break_time_id' => $breakTime->id]);
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Breaks update failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                abort(500, 'Breaks update failed.');
            }
        }
        return redirect()->route('application.details', $application->id);
    }

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

        $breakTimesStarts = (array) $request->input('break_start_time');
        $breakTimesEnds   = (array) $request->input('break_end_time');
        $breakTimeIds       = (array) $request->input('break_time_ids', []);

        if (count($breakTimesStarts) !== count($breakTimesEnds)) {
            return redirect('/attendance/list')->withErrors(['break_times' => '休憩時間の開始時間と終了時間の数が一致しません。']);
        }

        foreach ($breakTimesStarts as $i => $start) {
            $existingBreakTimeId = $breakTimeIds[$i] ?? null;

            if (!$existingBreakTimeId) {
                return redirect('/attendance/list')->withErrors([
                    'break_times' => 'break_time_id が指定されていません。既存の break_time を再利用してください。'
                ]);
            }

            $breakTime = \App\Models\UserBreakTime::find($existingBreakTimeId);

            if (!$breakTime) {
                return redirect('/attendance/list')->withErrors([
                    'break_times' => '指定された break_time_id が見つかりません。'
                ]);
            }

            $breakApplication = new UserBreakApplication();
            $breakApplication->user_id = auth()->id();
            $breakApplication->application_id = $application->id;
            $breakApplication->date = $request->input('date');
            $breakApplication->break_start_time = $start;
            $breakApplication->break_end_time = $breakTimesEnds[$i] ?? null;
            $breakApplication->break_time_id = $breakTime->id;
            $breakApplication->save();
        }

        $application->save();

        return redirect('/attendance/list');
    }

    public function save(DetailRequest $request, $id)
    {
        $record = \App\Models\UserAttendanceRecord::findOrFail($id);

        $record->clock_in_time  = $request->input('clock_in_time');
        $record->clock_out_time = $request->input('clock_out_time');
        $record->save();

        $attendance = $record;

        $break_start_times = $request->input('break_start_time', []);
        $break_end_times   = $request->input('break_end_time', []);

        $existingBreaks = \App\Models\UserBreakTime::where('user_attendance_record_id', $attendance->id)
            ->orderBy('id')
            ->get();

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
            $endTime = $break_end_times[$index] ?? '';

            if (isset($existingBreaks[$index])) {
                $existingBreaks[$index]->break_start_time = $start_time;
                $existingBreaks[$index]->break_end_time   = $endTime;
                $existingBreaks[$index]->save();

                $breakTimeId = $existingBreaks[$index]->id;
            } else {
                $newBreak = \App\Models\UserBreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start_time' => $start_time,
                    'break_end_time' => $endTime,
                ]);
                $breakTimeId = $newBreak->id;
            }

            \App\Models\UserBreakApplication::create([
                'user_id' => $attendance->user_id,
                'application_id' => $application->id,
                'break_time_id' => $breakTimeId,
                'date' => $attendance->date,
                'break_start_time' => $start_time,
                'break_end_time' => $endTime,
            ]);
        }

        $record->save();

        return redirect('admin/attendance/list');
    }

    public function exportCsv(Request $request)
    {
        $year   = (int) $request->query('year');
        $month  = (int) $request->query('month');
        $userId = (int) $request->query('user_id');

        if ($year <= 0 || $month < 1 || $month > 12 || $userId <= 0) {
            return response()->json(['error' => 'Invalid parameters'], 400);
        }

        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate   = date('Y-m-t', strtotime($startDate));

        $records = UserAttendanceRecord::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get();

        $columns = ['日付', '出勤時間', '退勤時間', '休憩時間', '合計', 'ステータス'];

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"attendance_{$userId}_${year}_${month}.csv\"",
        ];

        $callback = function() use ($records, $columns) {
            $handle = fopen('php://output', 'w');

            fprintf($handle, "%s\n", "\xEF\xBB\xBF");

            fputcsv($handle, $columns);

            foreach ($records as $rec) {
                $row = [
                    $rec->date,
                    $rec->clock_in_time ?? '',
                    $rec->clock_out_time ?? '',
                    $rec->user_break_times ?? '',
                    $rec->net_work_time ?? '',
                    $rec->status ?? '',
                ];
                fputcsv($handle, $row);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}