<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class LicenseService
{
    private static function k()
    { return base64_decode('YnJ4OWtQNXo='); }

    public static function g($n)
    {
        $a = Config::get('v2board.app_name', Config::get('app.name', 'V2Board'));
        $s = self::k();
        $h = md5($s . $a . $s);
        return strtoupper(substr($h, 0, 8) . '-' . substr($h, 8, 8) . '-' . substr($h, 16, 8) . '-' . substr($h, 24, 8));
    }

    public static function v($l)
    { $e = self::g(''); return $l === $e; }

    public static function c()
    {
        $lf = base_path(base64_decode('c3RvcmFnZS9mcmFtZXdvcmsvY2FjaGUvbC5kYXQ='));
        if (!file_exists($lf)) return false;
        $c = @file_get_contents($lf); if (!$c) return false;
        $d = @base64_decode($c); if (!$d) return false;
        return self::v($d);
    }

    public static function w($l)
    {
        $lf = base_path(base64_decode('c3RvcmFnZS9mcmFtZXdvcmsvY2FjaGUvbC5kYXQ='));
        $d = dirname($lf);
        if (!is_dir($d)) { @mkdir($d, 0755, true); }
        return @file_put_contents($lf, base64_encode($l)) !== false;
    }
}
