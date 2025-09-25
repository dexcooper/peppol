<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class IsAliveController extends Controller
{
    use ApiResponse;
    public function isAlive(Request $request)
    {
        return $this->success([], 'Request successful');
    }
}
