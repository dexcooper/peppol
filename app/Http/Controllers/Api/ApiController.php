<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ApiController extends Controller
{
    use AuthorizesRequests, ApiResponse;
}
