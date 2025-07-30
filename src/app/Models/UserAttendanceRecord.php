<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAttendanceRecord extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'application', 'clock_in_time', 'clock_out_time', 'date', 'status'];

    protected $appends = ['user_break_times', 'net_work_time'];

    public function getUserBreakTimesAttribute()
    {
        $breaks = \DB::table('user_break_times')
            ->where('user_id', $this->user_id)
            ->where('date', $this->date)
            ->get();

        $totalMinutes = 0;
        foreach ($breaks as $break) {
            if ($break->break_start_time && $break->break_end_time) {
                $start = \strtotime($break->break_start_time);
                $end = \strtotime($break->break_end_time);
                $diffMinutes = ($end - $start) / 60;
                $totalMinutes += $diffMinutes;
            }
        }
        return gmdate('H:i', $totalMinutes * 60);
    }

    public function getNetWorkTimeAttribute()
    {
        if (!$this->clock_in_time || !$this->clock_out_time) {
            return '';
        }

        $clockInMinutes = $this->convertTimeToMinutes($this->clock_in_time);
        $clockOutMinutes = $this->convertTimeToMinutes($this->clock_out_time);
        $workMinutes = $clockOutMinutes - $clockInMinutes;

        $breakTime = $this->user_break_times;
        $breakMinutes = ($breakTime !== '-') ? $this->convertTimeToMinutes($breakTime) : 0;

        $netMinutes = $workMinutes - $breakMinutes;
        if ($netMinutes < 0) {
            $netMinutes = 0;
        }

        return gmdate('H:i', $netMinutes * 60);
    }

    private function convertTimeToMinutes($time)
    {
        if (!$time || $time == '-') {
            return 0;
        }
        list($h, $m) = explode(':', $time);
        return ((int)$h) * 60 + ((int)$m);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    public function application()
    {
        return $this->belongsTo(UserApplication::class, 'application_id');
    }
}


