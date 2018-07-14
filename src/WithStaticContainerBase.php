<?php

namespace SlimLittleTools;

use SlimLittleTools\StaticBase;

/**
 * Slim PHP micro frameworkで「静的クラスでcontainerインスタンスを受け取る」クラス用の基底クラス
 *
 * XXX Traitにしようかとも思ったんだけど「parent::が使いにくい」ので、継承に組み込んだ
 */

class WithStaticContainerBase extends StaticBase
{
    public static function setContainer($container)
    {
        static::$container = $container;
    }


    //protected
protected static $container; // 普段ならprivateだが、slimの世界観だとprotectedのほうが「ほかと一緒」なので、そちらに合わせる
}
