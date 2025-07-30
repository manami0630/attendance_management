<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBreakApplication extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'application', 'status', 'date', 'break_start_time', 'break_end_time'];
}
