<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAttendanceRecord extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'clock_in_time', 'clock_out_time', 'date', 'status'];
}
