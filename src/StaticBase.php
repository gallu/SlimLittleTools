<?php

namespace SlimLittleTools;

/**
 * Slim PHP micro frameworkで「静的クラス専用のクラス」を作りたいとき用のクラス
 *
 * XXX 一旦、__cloneは抑止を入れてない。必要そうなら後で追加する
 */

class StaticBase
{
    /**
     * newの抑止
     */
    public function __construct()
    {
        throw new \ErrorException('You can not new this class. Class name is ' . get_class($this));
    }
    /**
     * unserializeの抑止
     */
    public function __wakeup()
    {
        throw new \ErrorException('You can not unserialize this class. Class name is ' . get_class($this));
    }
}
