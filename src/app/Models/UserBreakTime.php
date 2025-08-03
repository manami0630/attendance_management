<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBreakTime extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'user_attendance_record_id', 'break_start_time', 'break_end_time', 'date'];
}
