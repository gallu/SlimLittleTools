<?php

namespace SlimLittleTools\Libs;

use SlimLittleTools\WithStaticContainerBase;

/**
 * containerを広域で楽に使うためのクラス
 */

class Container extends WithStaticContainerBase
{
    /**
     * コンテナインスタンス自体の取得
     *
     * @return object コンテナインスタンス
     */
    public static function getContainer()
    {
        return static::$container;
    }

    /**
     * getメソッドのラッパー
     *
     * XXX 他にも使うメソッドがあったら __callStatic()とかでもよいかもだけど、一端getくらいかなぁと思われるので固定で書く
     *
     * @param $id string containerでentryを探すためのkey
     * @return mixed containerに設定されているもの
     */
    public static function get($id)
    {
        return static::getContainer()->get($id);
    }
}
