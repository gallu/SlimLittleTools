<?php

namespace SlimLittleTools\Middleware;

use Slim\Csrf\Guard;
use SlimLittleTools\Libs\Config;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Slim PHP micro frameworkでCookieを読み書きするための、シンプルなミドルウェア
 */


class CsrfGuard extends Guard
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // determineRouteBeforeAppMiddlewareの確認
        if (false === Config::get('determineRouteBeforeAppMiddleware', false)) {
            throw new \ErrorException('settingのdetermineRouteBeforeAppMiddlewareは明示的にtrueである必要があります');
        }

        // null時の処理
        // XXX determineRouteBeforeAppMiddleware=true であっても、groupの時にnullが来る事があるから
        if (null === $request->getAttribute('route')) {
            // 念のため通常の「CSRFチェック」を行う
            return parent::process($request, $handler);
        }

        // route名の把握
        $route_name = $request->getAttribute('route')->getName();

        // 除外リストのチェック
        if (('' !== $route_name)&&(isset($this->not_covered_list[$route_name]))) {
            // CSRFチェックを素通りさせる
            return $handler->handle($request);
        }

        // 通常の「CSRFチェック」を行う
        return parent::process($request, $handler);
    }

    // 除外ルートの設定
    public function setNotCoveredList($list)
    {
        $this->not_covered_list = array_flip($list);
        return $this;
    }

    //private:
    private $not_covered_list = [];
}
