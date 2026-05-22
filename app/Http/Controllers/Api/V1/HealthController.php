<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Traits\ApiResponse;

class HealthController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->successResponse('System is healthy.', [
            'status' => 'OK',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
