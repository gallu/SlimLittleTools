<?php

declare(strict_types=1);

namespace SlimLittleTools\Middleware;

use SlimLittleTools\WithContainerBase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response; // これは「実際にレスポンスを自力で作って返す」時に使う
use Psr\Http\Server\MiddlewareInterface;
use SlimLittleTools\Libs\Container;

/**
 * Slim PHP micro frameworkでCookieを読み書きするための、シンプルなミドルウェア
 */
class Cookie extends WithContainerBase implements MiddlewareInterface
{
    /*
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 事前処理
        // containerにcookieが指定されていなければ、設定しておく
        if (false === $this->container->has('cookie')) {
            $this->container->set('cookie', function () use ($request) {
                // インスタンス作成
                $cobj = new \SlimLittleTools\Libs\Http\Cookies($request->getCookieParams());

                // XXX ここに「デフォ設定」が書いてあるつもり & 突っ込むつもり
                $settings = $this->container->get('settings');
                if (true === isset($settings['cookie'])) {
                    $cobj->setDefaults($settings['cookie']);
                } else {
                    // これだけは設定しておく
                    $path = $this->container->get('request')->getUri()->getBasePath();
                    if ('' === $path) {
                        $path = '/';
                    }
                    $cobj->setDefaults(['httponly' => true, 'path' => $path ]);
                }
                //
                return $cobj;
            });
        }

        // 呼び出し
        $response = $handler->handle($request);

        // 事後処理：setCookieヘッダ群の出力
        foreach ($this->container->get('cookie')->toHeaders() as $cookie_string) {
            $response = $response->withAddedHeader('Set-Cookie', $cookie_string);
        }

        return $response;
    }
}

