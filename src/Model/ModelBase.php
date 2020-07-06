<?php

namespace SlimLittleTools\Model;

use SlimLittleTools\Exception\ModelGuardException;
use SlimLittleTools\Exception\ModelValidateException;
use SlimLittleTools\Exception\DbException;

use SlimLittleTools\Libs\DB;
use SlimLittleTools\Model\ModelCollection;

class ModelBase
{
    /*
    // テーブル名
    protected $table = 'tbl';
    // PKカラム
    protected $pk = 'pk'; // 通常の主キー
    protected $pk = ['pk1', 'pk2']; // 複合主キー

    // (PK以外で)update時に変更を抑止したいカラム：このカラムがupdate時に「引数で入っていて」「既存の値と異なる」場合は、例外を吐く
    protected $guard = ['name', 'name', ...];

    // いわゆるcreated_at / updated_atがあるとき、ここに指定があればそのカラム名に日付を追加で入れる
    // booleanでtrueが入っている場合は、デフォルトの文字列を使う(created_at/updated_at)
    protected $created_at = 'created_at'; // insert時のみ
    protected $updated_at = 'updated_at'; // insert 及び update時

    // PKがAUTO_INCREMENTのみのテーブルで、ここに明示的にtrueがあったら「insertの時にPKが指定されていたら例外を吐く」「insert後、PDO::lastInsertIdでとれる値をPKのカラムに入れる」を行う
    protected $auto_increment = true;

    // DB suffix
    // 接続先が「db(デフォルト)」以外なら、ここにsuffixを記述しておく( SlimLittleTools\Libs\DB を使う前提)
    protected $db_suffix = 'hoge';

    */

    // validate系設定
    // Validator クラス名
    protected static $validate_class = '\SlimLittleTools\Libs\Validator';
    // insert固有
    protected $validate_insert = [
        //'カラム名' => 'ルール',
        //'カラム名' => 'ルール',
    ];
    // update固有
    protected $validate_update = [
        //'カラム名' => 'ルール',
        //'カラム名' => 'ルール',
    ];
    // insert / update共通
    protected $validate = [
        //'カラム名' => 'ルール',
        //'カラム名' => 'ルール',
    ];

    // filterルール設定
    // Filter クラス名
    protected static $filter_class = '\SlimLittleTools\Libs\Filter';
    // insert固有
    protected $filter_insert = [
        //'カラム名' => 'ルール',
        //'カラム名' => 'ルール',
    ];
    // update固有
    protected $filter_update = [
        //'カラム名' => 'ルール',
        //'カラム名' => 'ルール',
    ];
    // insert / update共通
    protected $filter = [
        //'カラム名' => 'ルール',
        //'カラム名' => 'ルール',
    ];

    /**
     * プライマリキー名の取得
     */
    public static function getPkName()
    {
        return (new static())->pk;
    }
    public static function getPrimaryKeyName()
    {
        return static::getPkName();
    }

    /**
     * プテーブル名の取得
     */
    public static function getTableName()
    {
        return (new static())->table;
    }

    /*
     * カラムの一覧
     *
     * XXX 基本的に requestで入ってくるform情報とぶつけてデータを出す用に想定：なのでkeyはformのnameアトリビュート値(略してform名)
     * @deprecated
     */
    // INSERTとUPDATEで共通のカラム
    protected static $columns_list = [
        //'カラム名' => 'form名', // form名が空ならカラム名をそのままform名にする
        //'カラム名' => 'form名', // form名が空ならカラム名をそのままform名にする
    ];
    // INSERT時固有のカラム
    protected static $columns_list_only_insert = [
        //'カラム名' => 'form名', // form名が空ならカラム名をそのままform名にする
        //'カラム名' => 'form名', // form名が空ならカラム名をそのままform名にする
    ];
    // UPDATE時固有のカラム
    protected static $columns_list_only_update = [
        //'カラム名' => 'form名', // form名が空ならカラム名をそのままform名にする
        //'カラム名' => 'form名', // form名が空ならカラム名をそのままform名にする
    ];

    /**
     * 継承先での上書きを想定するメソッド群
     *
     * 継承先で「エラーを追加する」場合は、こんな風に記述する

        // 親をcall
        $res = parent::insertValidate($data);
        // XXXX オリジナルのvalidate
        // validate errorの追加
        $res->setResultFalse();
        $res->addError(['test' => ['hoge'], 'test2' => ['foo']]);

     */
    // insertとupdateで共通の「追加validate処理」用の空メソッド
    protected static function validateAdditionalRule(\SlimLittleTools\Libs\Validator $res)
    {
    }

    /**
     * 「日付カラムで空文字が入ってきたらinsertとupdateの時にデータをNULLにする」チェック
     *
     * 基本的に「Detail」がuseされている前提です。使う時は、軽傷先クラスでメソッドを上書きしてtrueを返すようにしてください
     *
     * @return boolean trueなら「日付型で空文字が入ってきたらNULLに置換する」処理を追加
     */
    protected static function isDateEmptyStringToNull()
    {
        return false;
    }

    /**
     * 「日付カラムで空文字が入ってきたらinsertとupdateの時にデータをNULLにする」用データ処理
     * 
     * 基本的に「Detail」がuseされている前提です。isColumnTypeDate() メソッドが存在しない場合、エラーになります。
     *
     * @param array $data insertまたはupdateしようとしている情報
     * @return array 「カラム型が日付 かつ データが空文字」のkeyをnullにしたdata
     */
    public static function deleteEmptyDates($data)
    {
        // 先にメソッドチェック
        if (false === method_exists(static::class, 'isColumnTypeDate')) {
            throw new  \ErrorException('deleteEmptyDates() がcallされていますが、isColumnTypeDate()が存在していません。Detailをuseしてください。');
        }
        // データのチェックと加工
        foreach($data as $k => $v) {
            if ( (true === static::isColumnTypeDate($k))&&( '' === $v) ) {
                $data[$k] = null;
            }
        }
        //
        return $data;
    }


    //
    public static function insertValidate($data)
    {
        // ルールを把握
        $rules = static::getProperty('validate_insert', []) + static::getProperty('validate', []);

        // 標準のvalidate
        $c = static::$validate_class;
        $res = $c::validate($data, $rules);
        // 追加ルールの処理
        static::validateAdditionalRule($res);

        //
        return $res;
    }
    public static function insertFilter($data)
    {
        // ルールを把握
        $rules = static::getProperty('filter_insert', []) + static::getProperty('filter', []);

        // filterを実行
        $c = static::$filter_class;
        $data = $c::exec($data, $rules);

        // returnした値を実際のvalidateやinsertに使う
        return $data;
    }
    // 仕様でちょっと思案中………
    public static function selectFilter($data)
    {
        // ルールを把握
        $rules = static::getProperty('filter_update', []) + static::getProperty('filter_insert', []) + static::getProperty('filter', []);

        // filterを実行
        $c = static::$filter_class;
        $data = $c::exec($data, $rules);

        // returnした値を実際のvalidateやinsertに使う
        return $data;
    }

    //
    public static function updateValidate($data)
    {
        // ルールを把握
        $rules = static::getProperty('validate_update', []) + static::getProperty('validate', []);

        // 標準のvalidate
        $c = static::$validate_class;
        $res = $c::validate($data, $rules, true);

        // 追加ルールの処理
        static::validateAdditionalRule($res);

        //
        return $res;
    }
    public static function updateFilter($data)
    {
        // ルールを把握
        $rules = static::getProperty('filter_update', []) + static::getProperty('filter', []);

        // filterを実行
        $c = static::$filter_class;
        $data = $c::exec($data, $rules);

        // returnした値を実際のvalidateやinsertに使う
        return $data;
    }

    // ----------------------------------
    /**
     * created_atのチェックと追加
     */
    protected static function setCreatedAt($data)
    {
        $created_at = static::getProperty('created_at');
        if (null !== $created_at) {
            $date_time = date("Y-m-d H:i:s");
            if ((is_string($created_at))&&('' !== $created_at)) {
                $data[$created_at] = $date_time;
            } elseif ((is_bool($created_at))&&(true === $created_at)) {
                $data['created_at'] = $date_time;
            }
        }
        return $data;
    }

    /**
     * updated_atのチェックと追加
     */
    protected static function setUpdatedAt($data)
    {
        $updated_at = static::getProperty('updated_at');
        if (null !== $updated_at) {
            $date_time = date("Y-m-d H:i:s");
            if ((is_string($updated_at))&&('' !== $updated_at)) {
                $data[$updated_at] = $date_time;
            } elseif ((is_bool($updated_at))&&(true === $updated_at)) {
                $data['updated_at'] = $date_time;
            }
        }
        return $data;
    }

    /**
     * DBハンドル取得用サフィックス(接尾語)取得
     */
    public static function getDbSuffix()
    {
        // キャッシュ用の空間を作成
        static $db_suffix = [];

        //
        $class = get_called_class(); // call元のクラス名を把握
        if (false === isset($db_suffix[$class])) {
            // プロパティ使いたいからインスタンス作成
            $db_suffix[$class] = static::getProperty('db_suffix', '');
        }
        //
        return $db_suffix[$class];
    }
    /**
     * DBハンドル取得
     */
    public static function getDbHandle()
    {
        $db_suffix = static::getDbSuffix();
        // XXX read/writeでDB変える、とかやるときはここでギミック入れる
        return DB::getHandle($db_suffix);
    }

    /**
     * 「$dataの中にPKが入っているか」のチェック
     *
     * insertとupdateで文脈が違うので、booleanではなく、int(三値でreturnしたいから)
     *
     * @return int -1: 全く入ってない, 0: 部分的に入ってる(複合主キー時のみ), 1:全て入っている
     */
    public static function checkPk($data)
    {
        // プロパティ使いたいからインスタンス作成
        // XXX ない場合のデフォルトは「テーブル名 + '_id'」とする
        $pk = static::getProperty('pk', static::getProperty('table') . '_id');

        // PKがstring(主キーが１つ)なら、単純に存在確認
        if (is_string($pk)) {
            if (isset($data[$pk])) {
                return 1;
            }
            // else
            return -1;
        }
        // else
        //  複合主キー時
        $pk_count = count($pk);
        $count = 0;
        // 「$dataの中に含まれているpkの数」をカウント
        foreach ($pk as $pk_mono) {
            if (isset($data[$pk_mono])) {
                $count ++;
            }
        }
        // 判定
        if ($pk_count === $count) {
            return 1;
        } elseif (0 === $count) {
            return -1;
        }
        // else
        return 0;
    }

    /**
     * staticなメソッドから「固定値のプロパティ」を見るためのラッパー
     */
    protected static function getProperty($key, $default = null)
    {
        //
        static $selfs = [];

        //
        $class = get_called_class();
        if (false === isset($selfs[$class])) {
            // プロパティ使いたいからインスタンス作成
            $selfs[$class] = new static ;
        }

        //
        if (isset($selfs[$class]->$key)) {
            return $selfs[$class]->$key;
        }
        // else
        return $default;
    }


    // ----------------

    /**
     * insert
     *
     * @param $forced_flg boolean ここが明示的にtrueなら「auto_incrementがtrueであっても、禁止項目チェックをしない」
     * @return ModelBase 成功したらModelBaseの継承インスタンス、失敗したらnull
     */
    public static function insert($data, $forced_flg = false)
    {
        // 禁止項目チェック
        if ((true === static::getProperty('auto_increment'))&&(false === $forced_flg)) {
            // 「AUTO_INCREMENTがtrueで、keyが単一で、$dataにそのkeyが入っている」が全部満たされたらNG
            // XXX 複合主キーでAUTO_INCREMENT、の時は一端ノータッチ: そのうち「カラム名が指定できる」とかにしようかなぁ？
            if ((is_string(static::getProperty('pk')))&&(isset($data[static::getProperty('pk')]))) {
                throw new ModelGuardException();
            }
        }

        // created_at / updated_atチェック
        $data = static::setCreatedAt($data);
        $data = static::setUpdatedAt($data);

        // フィルター
        $data = static::insertFilter($data);

        // validate
        $res = static::insertValidate($data);
        if (false === $res->isValid()) {
            throw (new ModelValidateException())->setErrorObj($res->getError());
        }
        // 削除カラムがあったら消しておく
        foreach ($res->getCheckedColmun() as $del_column) {
            unset($data[$del_column]);
        }

        // 日付カラム用処理
        if (true === static::isDateEmptyStringToNull()) {
            $data = static::deleteEmptyDates($data);
        }

        // insert用パーツ
        list($cols, $vals, $p_data) = static::makeSqlParts($data);

        // DBハンドル取得
        $dbh = static::getDbHandle();

        // insert
        $sql = 'INSERT INTO ' . $dbh->escapeIdentifier(static::getProperty('table')) . '(' . implode(', ', $cols) . ') VALUES(' . implode(', ', $vals) . ');';
        static::$just_before_query = $sql;
        //
        $r = $dbh->preparedQuery($sql, $p_data);
        if (false === $r) {
            return null;
        }
        // else
        $self = new static();
        foreach ($data as $k=> $v) {
            $self->set($k, $v);
        }
        // AUTO_INCREMENT なら、インスタンスに値を取得しておく
        if ((true === static::getProperty('auto_increment')) && (is_string(static::getProperty('pk')))) {
            $self->set(static::getProperty('pk'), $dbh->lastInsertId());
        }

        //
        return $self;
    }

    /**
     * 「主キーが1カラムの時用」の簡単な検索用メソッド
     *
     * @return obj ModelBaseを継承した、Modelクラス
     */
    public static function find($key)
    {
        // 「pkが一つ」の時は$keyがstringだと思われるので、$dataを適切に設定する
        if (is_string($key) || is_int($key)) {
            $pk = static::getProperty('pk');
            if (false === is_string($pk)) {
                throw new ModelGuardException('findメソッドで、複合主キーのテーブルに対してstringまたはintの引数を渡しています.');
            }
            return static::findBy([$pk => $key]);
        }
        // else
        return static::findBy($key);
    }

    /**
     * 簡易的な検索用メソッド(1レコード)
     *
     * @param $p1 string|hash 値がstringの場合「検索のカラム名」となり、$p2がその値となる。値がhashの場合「カラム名と値」の配列となり、ANDで検索する
     * @param $p2 string $p1の値がstringの場合、$p2がその値となる。値がhashの場合は使われない
     * @return obj ModelBaseを継承した、Modelクラスのインスタンス。見つからない場合はnull
     */
    public static function findBy($p1, $p2 = null)
    {
        $r = static::findByAll($p1, $p2);
        // XXX そもそもSQLに失敗しているから、例外投げた方がよいかねぇ？？
        if (null === $r) {
            return null;
        }
        // else
        return (0 === count($r))? null : $r[0];
    }
    /**
     * 簡易的な検索用メソッド(全レコード)
     *
     * @param $p1 string|hash 値がstringの場合「検索のカラム名」となり、$p2がその値となる。値がhashの場合「カラム名と値」の配列となり、ANDで検索する
     * @param $p2 string|vector $p1の値がstringの場合、$p2がその値となる。$p1がhash(またはnull)の場合はOEDER BY用の値と見なす
     * @param $p3 string|vector $p1の値がstringの場合、OEDER BY用の値と見なす
     * @return obj ModelCollectionインスタンス(中にはModelBaseを継承した、Modelクラスのインスタンス)。見つからない場合は空のModelCollectionインスタンス
     */
    public static function findByAll($p1 = null, $p2 = null, $p3 = null)
    {
        // 前処理
        if ([] === $p1) {
            $p1 = null;
        }

        //
        if (is_string($p1)) {
            $where = [$p1 => $p2];
            $order_by = $p3;
        } elseif (is_array($p1)) {
            $where = $p1;
            $order_by = $p2;
        } elseif (null === $p1) {
            $where = null;
            $order_by = $p2;
        } else {
            throw new ModelGuardException('findByメソッドの第一引数がstringでもhashでもないです.');
        }

        // DBハンドル取得
        $dbh = static::getDbHandle();

        // ORDER BY情報の整理
        if (null === $order_by) {
            $order_by = [];
        } else if (is_string($order_by)) {
            // 一旦、配列側に寄せる
            $order_by = [$order_by];
        }
        $order_array = [];
        foreach($order_by as $order_mono) {
            // DESCの判定用準備
            $awk = explode(' ', trim($order_mono));
            // カラム名の取得
            $s = $dbh->escapeIdentifier($awk[0]);
            // DESCの判定
            if ( (isset($awk[1]))&&(0 === strcasecmp($awk[1], 'desc')) ) {
                $s .= ' DESC';
            }
            //
            $order_array[] = $s;
        }

        // select用パーツ
        if (null !== $where) {
            list($cols, $vals, $p_data) = static::makeSqlParts($where);
            $where = [];
            for ($i = 0; $i < count($cols); ++$i) {
                // 実数値が配列で送られてきた場合INで組み立てる
                if(is_array($p_data[$vals[$i]])){
                    $awk = array_values($p_data[$vals[$i]]);
                    $tmp = [];
                    for($c=0; $c < count($awk); $c++){
                        $tmp[] = $vals[$i]. "_". $c;
                        $p_data[$vals[$i]. "_". $c] = $awk[$c];
                    }
                    unset($p_data[$vals[$i]]);
                    $where[] = "{$cols[$i]} in (". join(" , ", $tmp). ")";
                }else{
                    $where[] = "{$cols[$i]} = {$vals[$i]}";
                }
            }
        } else {
            // 下で使うので宣言だけしておく
            $p_data = [];
        }

        //
        $sql = 'SELECT * FROM ' . $dbh->escapeIdentifier(static::getProperty('table')) ;
        if (null !== $where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        // ORDER BY
        if ([] !== $order_array) {
            $sql .= ' ORDER BY '. implode(', ', $order_array);
        }
        // FOR UPDATE
        if (true === $dbh->isTran()) {
            $sql .= ' FOR UPDATE';
        }
        $sql .= ';';
        static::$just_before_query = $sql;
        //
        $r = $dbh->preparedQuery($sql, $p_data);
        if (false === $r) {
            return null;
        }
        // else
        $data = $r->fetchAll(\PDO::FETCH_ASSOC);
        if ((false === $data)||([] === $data)) {
            return new ModelCollection();
        }

        // データがあったぽいのでインスタンス作ってreturn
        $ret = new ModelCollection();
        foreach ($data as $datum) {
            $obj = new static();
            $datum = static::selectFilter($datum); // データを一旦フィルタリング
            foreach ($datum as $k => $v) {
                $obj->set($k, $v);
            }
            $ret[] = $obj;
        }
        //
        return $ret;
    }


    /**
     *
     */
    public function update($data)
    {
        // PKチェック
        $pk = static::getProperty('pk');
        if (is_string($pk)) {
            $pk = [$pk];
        }
        foreach ($pk as $k) {
            // 値の比較は型緩めに行う：後で変えるかも
            if ((isset($data[$k]))&&($data[$k] != $this->get($k))) {
                throw new ModelGuardException('PKをupdateで変更することはできません');
            }
            // else
            $data[$k] = $this->get($k);
        }

        // 禁止項目チェック
        $guard = static::getProperty('guard', []);
        foreach ($guard as $k) {
            //
            if ((isset($data[$k]))&&($data[$k] !== $this->get($k))) {
                //var_dump($data[$k], $this->get($k) );
                throw new ModelGuardException("guardによってガードされた値は変更できません({$k})");
            }
        }

        // updated_atチェック
        $data = static::setUpdatedAt($data);

        // フィルター
        $data = static::updateFilter($data);

        // validate
        $res = static::updateValidate($data);
        if (false === $res->isValid()) {
            throw (new ModelValidateException())->setErrorObj($res->getError());
        }
        // 削除カラムがあったら消しておく
        foreach ($res->getCheckedColmun() as $del_column) {
            unset($data[$del_column]);
        }
        // dataからはpkを消しておく
        foreach ($pk as $k) {
            unset($data[$k]);
        }

        // 日付カラム用処理
        if (true === static::isDateEmptyStringToNull()) {
            $data = static::deleteEmptyDates($data);
        }

        // update用パーツ
        list($cols, $vals, $p_data) = static::makeSqlParts($data);
        $sets = [];
        for ($i = 0; $i < count($cols); ++$i) {
            $sets[] = "{$cols[$i]} = {$vals[$i]}";
        }
        // where情報を取得
        $w = $this->makeWhere();

        // DBハンドル取得
        $dbh = static::getDbHandle();

        // update
        $sql = 'UPDATE ' . $dbh->escapeIdentifier(static::getProperty('table')) . ' SET ' . implode(', ', $sets) . ' WHERE ' . $w[0] . ';';
        static::$just_before_query = $sql;
        //
        $r = $dbh->preparedQuery($sql, $p_data + $w[1]);
        if (false === $r) {
            return false;
        }
        // else
        foreach ($data as $k => $v) {
            $this->set($k, $v);
        }
        return true;
    }

    /**
     *
     */
    public function delete()
    {
        // where情報を取得
        $w = $this->makeWhere();

        // DBハンドル取得
        $dbh = static::getDbHandle();

        // delete
        $sql = 'DELETE FROM ' . static::getProperty('table') . ' WHERE ' . $w[0] . ';';
        static::$just_before_query = $sql;
        return $dbh->preparedQuery($sql, $w[1]);
    }

    /**
     * インスタンスのpk情報からwhere句用の文字列とdataをreturn
     *
     * @return array [where句用の文字列, $data]
     */
    public function makeWhere()
    {
        // pkの情報を取得
        $pk = static::getProperty('pk');
        if (is_string($pk)) {
            // XXX
            $pk_e = $pk; // エスケープとチェック
            $pk_placeholder = static::makePlaceholder($pk);
            $where = "{$pk_e} = {$pk_placeholder}";
            $data = [];
            $data[$pk_placeholder] = $this->$pk;
        } else {
            // XXX 配列前提(nullとかは一旦未想定)
            $data = [];
            $where = [];
            foreach ($pk as $k) {
                // XXX
                $k_e = $k; // エスケープとチェック
                $k_placeholder = static::makePlaceholder($k);
                $where[] = "{$k_e} = {$k_placeholder}";
                $data[$k_placeholder] = $this->$k;
            }
            $where = implode(' AND ', $where);
        }
        return [$where, $data];
    }

    /**
     * 「カラム名からプレースホルダ名を作成する」メソッド
     *
     * 一旦「ほぼ素のまま」
     */
    public static function makePlaceholder($k)
    {
        // XXX 多分ここで「エスケープ処理」とかなんかやる想定
        return ':' . $k;
    }

    /**
     * SQLのパーツ作成(あちこちで共通に使うのでいったん切り出した)
     */
    protected static function makeSqlParts($data)
    {
        // DBハンドル取得
        $dbh = static::getDbHandle();

        // パーツ作成
        $cols = [];
        $vals = [];
        $p_data = [];
        $where = [];
        foreach ($data as $k => $v) {
            // カラム名の積み上げ
            $cols[] = $dbh->escapeIdentifier($k); // エスケープ
            // プレースホルダーと実データの積み上げ
            $p_data[ $vals[] = static::makePlaceholder($k) ] = $v;
        }
        //
        return [$cols, $vals, $p_data];
    }


    /**
     *
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * いったん、オミット
    public function __set($name, $value)
    {
        $this->changed_data[$name] = $value;
    }
     */
    /**
     * 内部むけsetter
     */
    protected function set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     *
     * @return int|string|null 存在しないキーはnull
     */
    public function __get($name)
    {
        return $this->get($name);
    }
    /**
     *
     * @return int|string|null 存在しないキーはnull
     */
    public function get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        // else
        return null;
    }

    /**
     * 「直前に発行したクエリ」を取得(主にデバッグ用途)
     */
    public static function getJustBeforeQuery()
    {
        return static::$just_before_query;
    }

    /**
     * insert用のカラム一覧
     *
     * 配列は「[form名] = 'カラム名'」のフォーマット。form名==カラム名の時は、カラム名が空文字
     * @deprecated
     */
    public static function getInsertColumnsList()
    {
        return static::$columns_list + static::$columns_list_only_insert;
    }
    /**
     * update用のカラム一覧
     *
     * 配列は「[form名] = 'カラム名'」のフォーマット。form名==カラム名の時は、カラム名が空文字
     * @deprecated
     */
    public static function getUpdateColumnsList()
    {
        return static::$columns_list + static::$columns_list_only_update;
    }

    //private
    private $data = [];
    private $changed_data = [];
    protected static $just_before_query = '';
}
