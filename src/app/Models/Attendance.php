<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'clock_in_time', 'clock_out_time', 'date', 'status', 'break_id'];

    public function user()
{
    return $this->belongsTo(User::class);
}
}
