<?php

namespace SlimLittleTools\Middleware;

use SlimLittleTools\WithContainerBase;

/**
 * Slim PHP micro frameworkでCookieを読み書きするための、シンプルなミドルウェア
 */

/*
setting
    add_response_header = [
        'X-hoge' => 'string',
        'X-foo' => 'string',
        'X-bar' => 'string',
    ],
 */

class AddHeader extends WithContainerBase
{
    //
    public function __invoke($request, $response, $next)
    {
        // 追加ヘッダのデフォルト
        $default = [
            // セキュリティ関連
            'X-Frame-Options' => 'SAMEORIGIN', // DENY でもよいかなぁ？
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            //'X-Content-Security-Policy' => 'default-src 'self'',
            //'Content-Security-Policy' => 'default-src 'self'',
            'Upgrade-Insecure-Requests' => '1',
            'X-Download-Options' => 'noopen',

            // キャッシュ系は念のために３種類
            'Cache-Control' => 'no-cache, no-store',
            'pragma' => 'no-cache',
            'expires' => '-1',
        ];

        // setting優先でmerge
        if (true === $this->container->has('settings')) {
            //$add = @$this->container->get('settings')['add_response_header'] ?? []; // PHP7以降、なので、一旦 5.5.0 >= だから、この書式使えない orz
            $settings = $this->container->get('settings');
            $add = (isset($settings['add_response_header'])) ? $settings['add_response_header'] : [];
        } else {
            $add = [];
        }
        $headers = $add + $default;

        // headerを追加
        foreach ($headers as $key => $val) {
            $response = $response->withHeader($key, $val);
        }

        // 本処理
        return $next($request, $response);
    }
}
