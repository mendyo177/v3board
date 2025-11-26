<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSecurePathReferer
{
    public function handle(Request $request, Closure $next)
    {
        $securePath = config('v2board.secure_path', config('v2board.frontend_admin_path', hash('crc32b', config('app.key'))));
        $referer = $request->headers->get('referer');
        if (!$referer) {
            if ($request->isMethod('GET') || $request->isMethod('HEAD') || $request->isMethod('OPTIONS')) {
                return $next($request);
            }
            abort(403, 'Forbidden: missing referer');
        }
        $ref = @parse_url($referer);
        if (!is_array($ref)) { abort(403, 'Forbidden: invalid referer'); }
        $sameHost = ($ref['host'] ?? '') === $request->getHost();
        $path = $ref['path'] ?? '/';
        $prefix = '/' . ltrim($securePath, '/');
        $pathOk = strpos($path, $prefix) === 0;
        if (!$sameHost || !$pathOk) { abort(403, 'Forbidden: referer not allowed'); }
        return $next($request);
    }
}
