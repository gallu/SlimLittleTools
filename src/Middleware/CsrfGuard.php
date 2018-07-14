<?php

namespace SlimLittleTools\Middleware;

use Slim\Csrf\Guard;

/**
 * Slim PHP micro frameworkでCookieを読み書きするための、シンプルなミドルウェア
 */


class CsrfGuard extends Guard
{
    public function __invoke($request, $response, callable $next)
    {
        // route名の把握
        $route_name = $request->getAttribute('route')->getName();

        // 除外リストのチェック
        if (('' !== $route_name)&&(isset($this->not_covered_list[$route_name]))) {
            // CSRFチェックを素通りさせる
            return $next($request, $response);
        }

        // 通常の「CSRFチェック」を行う
        return parent::__invoke($request, $response, $next);
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
