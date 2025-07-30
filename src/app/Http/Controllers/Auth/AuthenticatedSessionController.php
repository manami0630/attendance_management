<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    protected function redirectTo()
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return '/admin';
        }
        return '/dashboard';
    }
}