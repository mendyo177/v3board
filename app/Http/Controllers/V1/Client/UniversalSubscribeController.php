<?php

namespace App\Http\Controllers\V1\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Protocols\ClashMeta;
use App\Protocols\ClashNyanpasu;
use App\Protocols\ClashVerge;
use App\Protocols\Singbox\Singbox as SingboxBuilder;
use App\Services\ServerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UniversalSubscribeController extends Controller
{
    public function handle(Request $request)
    {
        $flag = strtolower($request->input('flag', 'meta'));
        $user = $this->resolveUser($request);
        if (!$user) { return response('unauthorized', 401); }
        $service = new ServerService();
        $servers = $service->getAvailableServers($user);
        switch ($flag) {
            case 'meta': case 'clash.meta': $builder = new ClashMeta($user, $servers); break;
            case 'nyanpasu': case 'nyan': $builder = new ClashNyanpasu($user, $servers); break;
            case 'verge': case 'clash': $builder = new ClashVerge($user, $servers); break;
            case 'sing': case 'singbox': $builder = new SingboxBuilder($user, $servers); break;
            default: $builder = new ClashMeta($user, $servers); break;
        }
        $result = $builder->handle();
        if ($result instanceof \Illuminate\Http\Response) { return $result; }
        $appName = config('v2board.app_name', 'V2Board');
        return response($result, 200)
            ->header('Content-Type', 'text/yaml; charset=utf-8')
            ->header('subscription-userinfo', "upload={$user['u']}; download={$user['d']}; total={$user['transfer_enable']}; expire={$user['expired_at']}")
            ->header('profile-update-interval', '24')
            ->header('content-disposition', "attachment; filename*=UTF-8''" . rawurlencode($appName));
    }

    private function resolveUser(Request $request)
    {
        $u = $request->attributes->get('user');
        if ($u instanceof User) { return $u; }
        if (is_array($u) && isset($u['id'])) { return $u; }
        $token = $request->input('token'); if (!$token) return null;
        $origin = Cache::get("otpn_{$token}"); if ($origin) { $token = $origin; }
        $user = User::where('token', $token)
            ->select(['id','uuid','u','d','transfer_enable','expired_at','speed_limit','device_limit','banned','group_id'])
            ->first();
        if (!$user || $user->banned) return null; return $user;
    }
}
