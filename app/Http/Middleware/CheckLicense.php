<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;

class CheckLicense
{
    public function handle($request, Closure $next)
    {
        if (!LicenseService::c()) {
            return response()->json([
                's' => false,
                'm' => base64_decode('UGx1Z2luIGxpY2Vuc2UgaW52YWxpZCBvciBleHBpcmVk'),
                'd' => null
            ], 403);
        }
        return $next($request);
    }
}
