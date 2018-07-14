<?php

namespace SlimLittleTools\Libs;

use SlimLittleTools\WithStaticContainerBase;

/**
 * configを広域で楽に使うためのクラス
 */

class Config extends WithStaticContainerBase
{

    // 単純な「一次元」のkey用のラッパー
    public static function get($name, $default = null)
    {
        if (true === static::has($name)) {
            return static::getAll()[$name];
        }
        // else
        return $default;
    }
    //
    public static function has($name)
    {
        return isset(static::getAll()[$name]);
    }

    // setting全体をまとめて欲しい時用
    // XXX メソッド内でキャッシュするかどうかは後で思案する
    public static function getAll()
    {
        return static::$container->get('settings');
    }
}
