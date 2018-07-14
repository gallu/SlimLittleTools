<?php

namespace SlimLittleTools\Middleware;

use SlimLittleTools\WithContainerBase;

/**
 * Slim PHP micro frameworkでCookieを読み書きするための、シンプルなミドルウェア
 */

class Cookie extends WithContainerBase
{

    /**
     *
     */
    public function __invoke($request, $response, $next)
    {
        // 事前処理
        // containerにcookieが指定されていなければ、設定しておく
        if (false === $this->container->has('cookie')) {
            $this->container['cookie'] = function () {
                // インスタンス作成
                //$cobj = new \Slim\Http\Cookies($this->container->get('request')->getCookieParams());
                $cobj = new \SlimLittleTools\Libs\Http\Cookies($this->container->get('request')->getCookieParams());

                // XXX ここに「デフォ設定」が書いてあるつもり & 突っ込むつもり
                $settings = $this->container->get('settings');
                if (true === isset($settings['cookie'])) {
                    $cobj->setDefaults($settings['cookie']);
                } else {
                    // これだけは設定しておく
                    $path = $request->getUri()->getBasePath();
                    if ('' === $path) {
                        $path = '/';
                    }
                    $cobj->setDefaults(['httponly' => true, 'path' => $path ]);
                }
                //
                return $cobj;
            };
        }

        // 本処理
        $response = $next($request, $response);

        // 事後処理：setCookieヘッダ群の出力
        foreach ($this->container->get('cookie')->toHeaders() as $cookie_string) {
            $response = $response->withAddedHeader('Set-Cookie', $cookie_string);
        }
        return $response;
    }
}
