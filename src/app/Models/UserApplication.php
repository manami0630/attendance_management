<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserApplication extends Model
{
    use HasFactory;

    protected $table = 'user_applications';

    protected $fillable = [
        'user_id', 'user_attendance_record_id','status', 'target_date', 'reason', 'clock_in_time','clock_out_time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userAttendanceRecord()
    {
        return $this->belongsTo(UserAttendanceRecord::class, 'user_attendance_record_id');
    }

    public function records()
    {
        return $this->hasMany(UserAttendanceRecord::class, 'application_id');
    }

    public function attendanceRecord()
    {
        return $this->hasOne(\App\Models\UserAttendanceRecord::class, 'application_id');
    }
}