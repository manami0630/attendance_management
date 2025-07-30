<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\RegisterResponse;

class RedirectAfterRegister implements RegisterResponse
{
    public function toResponse($request)
    {
        return redirect('/attendance');
    }
}

