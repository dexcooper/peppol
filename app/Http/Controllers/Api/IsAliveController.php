<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class IsAliveController extends ApiController
{
    public function isAlive(Request $request)
    {
        return $this->success([], 'Request successful');
    }
}
