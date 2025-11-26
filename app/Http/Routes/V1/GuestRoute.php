<?php
namespace App\Http\Routes\V1;

use Illuminate\Contracts\Routing\Registrar;

class GuestRoute
{
    public function map(Registrar $router)
    {
        $router->group([
            'prefix' => 'guest',
            // 为本地验证码提供 session 支持（不启用 CSRF）
            'middleware' => [\Illuminate\Session\Middleware\StartSession::class]
        ], function ($router) {
            // Telegram
            $router->post('/telegram/webhook', 'V1\\Guest\\TelegramController@webhook');
            // Payment
            $router->match(['get', 'post'], '/payment/notify/{method}/{uuid}', 'V1\\Guest\\PaymentController@notify');
            // Comm
            $router->get ('/comm/config', 'V1\\Guest\\CommController@config');
            // Local Captcha image
            $router->get('/captcha', 'V1\\Guest\\CaptchaController@image');
        });
    }
}
