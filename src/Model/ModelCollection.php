<?php

namespace SlimLittleTools\Model;

/**
 * 「Modelの群れ(配列)」を扱うためのクラス。「modelインスタンス全体にこんなメソッドを叩いた結果をreduceしたい」時にはメソッドを追加
 */

class ModelCollection extends \ArrayObject
{
    /**
     * toArrayメソッドが来たら「各個のオブジェクト毎」に叩いて、その配列をreturnする
     *
     * @return vector<hash> 「持っている各modelインスタンスのtoArray()の結果」の配列
     */
    public function toArray()
    {
        $ret = [];
        foreach ($this as $o) {
            $ret[] = $o->toArray();
        }
        return $ret;
    }
}
