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

    /**
     * 郵便番号系の基底メソッド
     *
     */
    protected static function zipBase($zip, $separator)
    {
        // ざっくり分解
        $r = preg_match('/\A(\d{3})[- ]{0,1}(\d{4})\z/', $zip, $mat);
        // 郵便番号じゃなければ入力値をそのままreturn
        if (0 === $r) {
            return $zip;
        }
        // else
        // 整形してreturn
        return "{$mat[1]}{$separator}{$mat[2]}";
    }
    /**
     * 郵便番号をハイフン付きに整形
     */
    protected static function filterZip_hyphen($zip)
    {
        return static::zipBase($zip, '-');
    }
    /**
     * 郵便番号をスペース付きに整形
     */
    protected static function filterZip_space($zip)
    {
        return static::zipBase($zip, ' ');
    }
    /**
     * 郵便番号を数字7桁のみ、に整形
     */
    protected static function filterZip_shorten($zip)
    {
        return static::zipBase($zip, '');
    }

    /**
     * カタカナひらがなを全角カタカナに変換
     */
    public static function filterKatakana($s)
    {
        return mb_convert_kana($s, 'KCV', 'UTF-8');
    }

    /**
     * カタカナひらがなを全角ひらがなに変換
     */
    public static function filterHirakana($s)
    {
        return mb_convert_kana($s, 'HcV', 'UTF-8');
    }

    /**
     * 空文字をnullに変換
     */
    public static function filterEmpty_string_to_null($s)
    {
        return '' === $s ? null : $s;
    }

}
