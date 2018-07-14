<?php

namespace SlimLittleTools\Middleware;

use SlimLittleTools\WithContainerBase;

/**
 * Slim PHP micro frameworkで SlimLittleTools のLibs配下をまとめて設定するときに便利なツール
 */

class SlimLittleToolsUse extends WithContainerBase
{
    // Containerインスタンスを保持したいクラス名の列挙
    private $addContainer = [
        'SlimLittleTools\\Libs\\DB',
        'SlimLittleTools\\Libs\\Config',
    ];

    // 処理
    public function __invoke($request, $response, $next)
    {
        // SlimLittleTools\Libsの各静的classにcontainerを追加
        foreach ($this->addContainer as $class) {
            $class::setContainer($this->container);
        }

        // 本処理
        return $next($request, $response);
    }
}
