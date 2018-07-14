<?php

namespace SlimLittleTools\Libs;

/**
 * フィルタリングクラス
 *
 * ルールを追加したい時は「filter+ルール名」のメソッドを追加してください
 */

class Filter
{
    /**
     * 「palinなPHP関数でフィルタリングをする時」用の、使える関数の一覧
     *
     * 追加したい場合は、クラス＆メソッドを継承して追加してくださいませ
     */
    protected static function palin_method()
    {
        return [
            'trim',
            'strtolower',
            'strtoupper',
            'floor' ,
            'ceil' ,
            'abs' ,
        ];
    }

    /**
     * フィルター処理メイン
     */
    public static function exec($data, $rules)
    {
        // 「現状」のメソッド名一覧を把握
        $method_array = array_flip(array_map('strtolower', get_class_methods(get_called_class())))
                        + array_flip(array_map(function ($s) {
                            return 'filter' . $s;
                        }, static::palin_method()));

        // ルールを分解して処理をする
        foreach ($rules as $name => $v) {
            // データを軽く確認
            if (false === isset($data[$name])) {
                //throw new \ErrorException('カラム名 ' . $name . ' が、データに含まれていません');
                continue; // XXX 「ルールにあるけどデータがない」は、いったん、許容する
            }
            // ルールに沿って処理
            $rwk = explode('|', $v);
            foreach ($rwk as $rule) {
                // メソッド名作成
                $method = 'filter' . ucfirst(trim($rule));
                // 軽くチェック
                if (false === isset($method_array[strtolower($method)])) {
                    throw new \ErrorException('ルール ' . $rule . ' は、未定義のルールです');
                }
                // filterを呼んでルールを適用する
                $data[$name] = static::$method($data[$name]);
            }
        }

        //
        return $data;
    }

    // filter
    // -------------------------------
    /**
     * 「plainなPHP関数」使ってフィルタリング処理する箇所
     *
     * trim, strtolower, strtoupper, floor, ceil, abs
     */
    public static function __callStatic($name, $arguments)
    {
        // メソッド名から'filter'を抜き取って本体を取り出す
        $name = str_replace('filter', '', strtolower($name));
        // XXX 存在チェックは事前にされている前提
        return $name($arguments[0]);
    }

    /**
     * intへのキャスト
     */
    protected static function filterInt($datum)
    {
        return (int)$datum;
    }

    /**
     * stringへのキャスト
     */
    protected static function filterString($datum)
    {
        return (string)$datum;
    }
}
