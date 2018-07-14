<?php

namespace SlimLittleTools\Libs;

/**
 * Validator
 *
 * ルールを追加したい時は「validateExec+ルール名」のメソッドを追加してください
 */
class Validator
{
    /**
     * validate処理
     */
    public static function validate($data, $rules)
    {
        // 自身のインスタンスを作成(戻り値用)
        $self = new static;
        // dataとrulesを格納
        $self->data = $data;
        $self->rules = $rules;

        // 「現状」のメソッド名一覧を把握
        $method_array = array_flip(array_map('strtolower', get_class_methods(get_called_class())));

        // 値格納用領域
        $self->checked_colmun = [];
        $self->error = [];
        $self->result_flg = true;
        //
        foreach ($rules as $con_name => $rules_string) {
            //
            $error_mono = [];
            // ルールを分割して
            $rules_array = explode('|', $rules_string);
            // ルール単体毎に処理
            foreach ($rules_array as $rule) {
                // 空文字があったらはじく
                $rule = trim($rule);
                if ('' === $rule) {
                    continue;
                }

                // compare_withだけ処理が例外なのではじく
                if ('compare_with' === $rule) {
                    //
                    $r = $self->validateExecCompare_with($data, $con_name);
                } else {
                    // パラメタの切り分け
                    @list($rule, $param) = explode(':', $rule, 2); // XXX エラーチェックはざっくり

                    // 処理関数の動的な作成
                    $method = 'validateExec' . ucfirst($rule);
                    // 軽くチェック
                    if (false === isset($method_array[strtolower($method)])) {
                        throw new \ErrorException('ルール ' . $rule . ' は、未定義のルールです');
                    }

                    // 処理関数の実行
                    // XXX 必須じゃないときは「keyすらも存在していない」可能性があるので、dataチェックは入れない
                    $r = static::$method((string)@$data[$con_name], $param);
                }
                //
                if (false === $r) {
                    // validateでエラーだった時用の処理
                    $self->result_flg = false;
                    $error_mono[] = $rule;
                }
            }
            //
            if ([] !== $error_mono) {
                $self->error[$con_name] = $error_mono;
            }
        }

        //
        return $self;
    }


    /**
     * validateの結果
     *
     * @return boolean 全てvalid(正しい)ならtrue、一つでもinvalidがあったらfalse
     */
    public function isValid()
    {
        return $this->result_flg;
    }
    /**
     * 外部から「結果をfalse」にできるように(基本的にはあんまり使わないんだけど)
     */
    public function setResultFalse()
    {
        $this->result_flg = false;
    }

    /**
     * 除外対象カラム名の取得
     *
     * @return array 除外対象カラム名の配列：ない時は空配列
     */
    public function getCheckedColmun()
    {
        return $this->checked_colmun;
    }

    /**
     * エラー(invalid)時の、細かい情報
     *
     *  $error = [
     *      'カラム名' => [こけたルール名, こけたルール名, こけたルール名],
     *      'カラム名' => [こけたルール名, こけたルール名, こけたルール名],
     *      'カラム名' => [こけたルール名, こけたルール名, こけたルール名],
     *  ];
     *
     * @return array エラー(invald)時の情報：ない時は空配列
     */
    public function getError()
    {
        return $this->error;
    }
    /**
     * 外部から「エラー内容を足せる」ように(基本的にはあんまり使わないんだけど)
     */
    public function addError($add)
    {
        foreach($add as $k => $v) {
            //
            if (false === isset($this->error[$k])) {
                $this->error[$k] = [];
            }
            $this->error[$k] = array_merge($this->error[$k], $v);
        }
    }

    //protected:
    //
    protected static function getParamToNum($param)
    {
        $i = filter_var($param, FILTER_VALIDATE_INT);
        if (false === $i) {
            throw new ErrorException('パラメタには数値が期待されますが、 ' . $param . ' が入ってきました');
        }
        return $i;
    }
    //
    protected static function getParamToNumRange($param)
    {
        @list($min, $max) = explode('-', $param);
        if (null === $max) {
            throw new ErrorException('パラメタには数値の範囲期待されますが、 ' . $param . ' が入ってきました(ハイフンがありません)');
        }
        $min = filter_var($min, FILTER_VALIDATE_INT);
        $max = filter_var($max, FILTER_VALIDATE_INT);
        if (false === $min) {
            throw new ErrorException('パラメタの左辺には数値が期待されますが、 ' . $min . ' が入ってきました');
        }
        if (false === $max) {
            throw new ErrorException('パラメタの右辺には数値が期待されますが、 ' . $max . ' が入ってきました');
        }
        //
        return [$min, $max];
    }

    // -------------------------
    // 各種単体チェックメソッド
    // -------------------------
    /**
     * 必須チェック
     */
    public static function validateExecRequired($datum, $param)
    {
        return ('' !== $datum);
    }

    /**
     * 日付フォーマットかチェック
     */
    public static function validateExecDatetime($datum, $param)
    {
        if (false === strtotime($datum)) {
            return false;
        }
        // else
        return true;
    }

    /**
     * アルファベットかチェック
     */
    public static function validateExecAlpha($datum, $param)
    {
        return ctype_alpha($datum);
    }

    /**
     * アルファベットまたは数値かチェック
     */
    public static function validateExecAlpha_num($datum, $param)
    {
        return ctype_alnum($datum);
    }
    /**
     * (文字としての)最低文字数(バイト長)。引数の数未満ならエラー
     */
    public static function validateExecMin_length($datum, $param)
    {
        return (strlen($datum) >= static::getParamToNum($param));
    }
    /**
     * (文字としての)最大文字数(バイト長)。引数の数を超えるならエラー
     */
    public static function validateExecMax_length($datum, $param)
    {
        return (strlen($datum) <= static::getParamToNum($param));
    }
    /**
     *  (文字としての)範囲(バイト長)。引数は「ｎ以上ｍ以下」。範囲外ならエラー
     */
    public static function validateExecRange_length($datum, $param)
    {
        $range = static::getParamToNumRange($param);
        $len = strlen($datum);
        return ($range[0] <= $len)&&($len <= $range[1]);
    }
    /**
     * (文字としての)最低文字数(文字数(mb_length))。引数の数未満ならエラー
     */
    public static function validateExecMin_m_length($datum, $param)
    {
        return (mb_strlen($datum, 'UTF8') >= static::getParamToNum($param));
    }
    /**
     *  (文字としての)範囲(文字数(mb_length))。引数は「ｎ以上ｍ以下」。範囲外ならエラー
     */
    public static function validateExecMax_m_length($datum, $param)
    {
        return (mb_strlen($datum, 'UTF8') <= static::getParamToNum($param));
    }
    /**
     *  (文字としての)最大文字数(文字数(mb_length))。引数の数を超えるならエラー
     */
    public static function validateExecRange_m_length($datum, $param)
    {
        $range = static::getParamToNumRange($param);
        $len = mb_strlen($datum, 'UTF8');
        return ($range[0] <= $len)&&($len <= $range[1]);
    }
    /**
     * (数値としての)最低値。引数の数未満ならエラー
     */
    public static function validateExecMin_number($datum, $param)
    {
        return ($datum >= static::getParamToNum($param));
    }
    /**
     * (数値としての)最低値。引数の数を超えるならエラー
     */
    public static function validateExecMax_number($datum, $param)
    {
        return ($datum <= static::getParamToNum($param));
    }
    /**
     *  (数値としての)範囲。引数は「ｎ以上ｍ以下」。範囲外ならエラー
     */
    public static function validateExecRange_number($datum, $param)
    {
        $range = static::getParamToNumRange($param);
        return ($range[0] <= $datum)&&($datum <= $range[1]);
    }
    /**
     * 自身のカラム名＋'_check' を探して、値をチェック。等しくなければエラー。パスワードとかemailで使う想定
     *
     * XXX このメソッドだけ 'staticではない' ので注意
     */
    public function validateExecCompare_with($data, $col_name)
    {
        // 確認用カラム名の把握
        $check_con_name = "{$col_name}_check";

        // 元と確認の各項目を存在確認
        if ((false === isset($data[$col_name]))||(false === isset($data[$check_con_name]))) {
            return false;
        }

        // カラム名を把握しておく(多分、後で削除したいだろうから)
        $this->checked_colmun[] = $check_con_name;

        // 元と確認は一致しているか？？
        return ($data[$col_name] === $data[$check_con_name]);
    }
    /**
     * int型、もしくは「intとして認識可能な数字だけの文字列」であること
     */
    public static function validateExecInt($datum, $param)
    {
        $r = filter_var($datum, FILTER_VALIDATE_INT);
        if (false === $r) {
            return false;
        }
        return true;
    }
    /**
     * float型、もしくは「floatとして認識可能な数字だけの文字列」であること
     */
    public static function validateExecFloat($datum, $param)
    {
        $r = filter_var($datum, FILTER_VALIDATE_FLOAT);
        if (false === $r) {
            return false;
        }
        return true;
    }


    //private:
    private $result_flg;
    private $checked_colmun;
    private $error;
}
