<?php
declare(strict_types=1);

namespace SlimLittleTools;

use Psr\Container\ContainerInterface;

/**
 * Slim PHP micro frameworkで「コンストラクタでcontainerインスタンスを受け取る」クラス用の基底クラス
 *
 * XXX Traitにしようかとも思ったんだけど「parent::が使いにくい」ので、継承に組み込んだ
 */

class WithContainerBase
{
    /**
     * コンストラクタ
     *
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    //protected
protected $container; // 普段ならprivateだが、slimの世界観だとprotectedのほうが「ほかと一緒」なので、そちらに合わせる
}
