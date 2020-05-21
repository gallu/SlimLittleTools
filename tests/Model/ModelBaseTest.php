<?php
declare(strict_types=1);

namespace SlimLittleTools\Tests\Model;

use SlimLittleTools\Model\ModelBase;
use SlimLittleTools\Libs\DB;

use SlimLittleTools\Libs\Http\Request;
use Slim\Http\Environment;

/*

テスト用テーブル
DROP TABLE IF EXISTS mode_1;
CREATE TABLE mode_1 (
  mode_1_id VARCHAR(64) NOT NULL,
  val VARCHAR(64),
  val2 VARCHAR(64),
  val_guard VARCHAR(64),
  created_at DATETIME,
  updated_at DATETIME,
  PRIMARY KEY(mode_1_id)
);

DROP TABLE IF EXISTS mode_2;
CREATE TABLE mode_2 (
  mode_2_id INT NOT NULL,
  mode_2_key VARCHAR(64) NOT NULL,
  val VARCHAR(64),
  PRIMARY KEY(mode_2_id, mode_2_key)
);

DROP TABLE IF EXISTS mode_3;
CREATE TABLE mode_3 (
  mode_3_id INT NOT NULL AUTO_INCREMENT,
  val VARCHAR(64),
  created_at DATETIME,
  updated_at DATETIME,
  PRIMARY KEY(mode_3_id)
);

DROP TABLE IF EXISTS model_date;
CREATE TABLE model_date (
  mode_date_id INT NOT NULL AUTO_INCREMENT,
  s varchar(16),
  date_1 DATE DEFAULT NULL,
  date_2 DATETIME DEFAULT '1999-7-31 00:11:22',
  PRIMARY KEY(mode_date_id)
);


*/

// テスト用モデル
class TestModel extends ModelBase
{
    protected $table = 'mode_1';
    protected $pk = 'mode_1_id';
    protected $guard = ['val_guard'];
    protected $created_at = 'created_at';
    protected $updated_at = 'updated_at';
    //
    protected $validate_insert = [
        'val_guard' => 'required|alpha_num|range_length:10-20',
    ];
    // update固有
    protected $validate_update = [
        'val' => 'required|alpha_num|range_length:10-20',
    ];
    // insert / update共通
    protected $validate = [
        'mode_1_id' => 'required|int',
    ];

    // filterルール設定
    // insert固有
    protected $filter_insert = [
    ];
    // update固有
    protected $filter_update = [
    ];
    // 共通
    protected $filter = [
        'mode_1_id' => 'int',
        'val_guard' => 'trim|strtolower',
    ];
    //
    // INSERTとUPDATEで共通のカラム
    protected static $columns_list = [
        'val' => '', // form名が空ならカラム名をそのままform名にする
    ];
    // INSERT時固有のカラム
    protected static $columns_list_only_insert = [
        'mode_1_id' => '', // form名が空ならカラム名をそのままform名にする
        'val_guard' => '', // form名が空ならカラム名をそのままform名にする
    ];
}
// テスト用モデル
class TestModelMultiKey extends ModelBase
{
    protected $table = 'mode_2';
    protected $pk = ['mode_2_id', 'mode_2_key'];
}
// テスト用モデル
class TestModelAutoIncrement extends ModelBase
{
    protected $table = 'mode_3';
    protected $pk = 'mode_3_id';
    protected $auto_increment = true; // AUTO_INCREMENTがPKなテーブル
    protected $created_at = true; // デフォルト名
    protected $updated_at = true; // デフォルト名
}
// テスト用モデル(存在しないテーブル)
class TestModelNotExist extends ModelBase
{
    protected $table = 'mode_no_exist';
}
// テスト用モデル(DB接続先が違う)
class TestModelAnotherDB extends ModelBase
{
    protected $db_suffix = 'hoge';
}
// getInsertColumnsListとgetUpdateColumnsList用テストモデル
class TestColumnsList extends ModelBase
{
    // INSERTとUPDATEで共通のカラム
    protected static $columns_list = [
        'common_1' => '', // form名が空ならカラム名をそのままform名にする
        'common_2' => '', // form名が空ならカラム名をそのままform名にする
    ];
    // INSERT時固有のカラム
    protected static $columns_list_only_insert = [
        'insert_1' => '', // form名が空ならカラム名をそのままform名にする
    ];
    // UPDATE時固有のカラム
    protected static $columns_list_only_update = [
        'update_1' => '', // form名が空ならカラム名をそのままform名にする
    ];
}
// テスト用モデル(validate additional rule)
class TestModelAddRule extends ModelBase
{
    protected $table = 'mode_no_exist';

    // insertとupdateで共通の「追加validate処理」用の空メソッド
    protected static function validateAdditionalRule(\SlimLittleTools\Libs\Validator $res)
    {
        $res->setResultFalse();
        $res->addError(['TestModelAddRule' => ['hoge']]);
    }
}
// テスト用モデル(日付で「空なら入れない」)
class TestModelDate extends ModelBase
{
    protected $table = 'model_date';
    // pk
    protected $pk = 'mode_date_id';
    protected $auto_increment = true;

    // カラム型一覧
    protected $colmuns_type = [
        'mode_date_id' => 'bigint(20) unsigned',
        's' => 'varchar(16)',
        'date_1' => 'date',
        'date_2' => 'datetime',
    ];

    //
    protected static function isDateEmptyStringToNull()
    {
        return true;
    }
    //
    public static function isColumnTypeDate(string $name)
    {
        // 先に確認
        $list = (new static())->colmuns_type;
        if (false === isset($list[$name])) {
            throw new \ErrorException('存在しないカラム名が指定されました');

        }
        // 型の把握
        $type = strtolower($list[$name]);

        // 判定
        // DATE または DATETIME
        if ( ('date' === $type) || ('datetime' === $type) ) {
            return true;
        }
        // "TIMESTAMP"は、RDBによっては「 without time zone」とか「with time zone」とか付くようなので少し配慮
        // あと、このロジックだと timestamptz(PostgreSQL独自拡張)も一応拾える想定
        if (0 === strncmp('timestamp', $type, 9)) {
            return true;
        }

        // 上述以外ならfalse
        return false;
    }

}


// テスト本体
class ModelBaseTest extends \SlimLittleTools\Tests\TestBase
{
    // 一回だけ実行される開始前メソッド
    public static function setUpBeforeClass() : void
    {
        // DBハンドル用の設定をcontainerに入れておく
        $settings = [
            'settings' => [
                //
                'db' => [
                    'driver' => 'mysql',
                    'host' => 'localhost',
                    'database' => 'slim_tools',
                    'user' => 'slim_tools',
                    //'pass' => 'XXXXXX',
                    'pass' => 'slim_tools',
                    'charset' => 'utf8mb4',
                    'options' => [\PDO::ATTR_EMULATE_PREPARES => false],
                ],
            ],
        ];
        $app = static::getApp($settings);
        //
        DB::setContainer($app->getContainer());
    }
    // テストメソッドごとの開始前メソッド
    protected function setUp() : void
    {
        // リアルなDB接続が必要なので、一旦スキップ
        //$this->markTestSkipped();
    }
    // -----
    // テストメソッドごとの終了メソッド
    protected function tearDown() : void
    {
    }
    // 一回だけ実行される終了メソッド
    public static function tearDownAfterClass() : void
    {
    }

    // -----------------------------------------------
    public function testAll()
    {
        //
        $this->assertSame(TestModel::getPkName(), 'mode_1_id');
        $this->assertSame(TestModel::getTableName(), 'mode_1');

        // 先にお掃除
        $dbh = DB::getHandle();
        $dbh->query('TRUNCATE TABLE mode_1;');
        $dbh->query('TRUNCATE TABLE mode_2;');
        $dbh->query('TRUNCATE TABLE mode_3;');
        $dbh->query('TRUNCATE TABLE model_date;');

        // checkPkの確認
        // 単キー 2種
        $this->assertSame(TestModel::checkPk(['mode_1_id' => 'aaa']), 1);
        $this->assertSame(TestModel::checkPk(['test' => 'aaa']), -1);
        // 複合主キー 3種
        $this->assertSame(TestModelMultiKey::checkPk(['mode_2_id' => 'aaa', 'mode_2_key' => 222]), 1);
        $this->assertSame(TestModelMultiKey::checkPk(['mode_2_id' => 'aaa', 'test' => 222]), 0);
        $this->assertSame(TestModelMultiKey::checkPk(['id' => 'aaa']), -1);

        // DB接続
        $this->assertSame(TestModel::getDbSuffix(), '');
        $this->assertSame(TestModelAnotherDB::getDbSuffix(), 'hoge'); // 共通DBではない箇所への接続

        // 単純key: created_at / updated_at あり, compare_withあり(データ削除)
        // insert + filter
        $r = TestModel::insert(['mode_1_id' => '1', 'val' => 'Val0123456789', 'val_guard' => 'ValGuard0123456789', ]);
        $this->assertNotSame($r, null);
        $this->assertSame(get_class($r), TestModel::class);
        $this->assertSame($r->mode_1_id, 1);
        $this->assertSame($r->hoge, null); // 存在しないカラム名のチェック

        // 重複によるエラー
        $r = TestModel::insert(['mode_1_id' => '1', 'val' => 'XXXXXXXXXXXXX', 'val_guard' => 'XXXXXXXXXXXXxxxxxx', ]);
        $this->assertSame($r, null);

        // find(ない)
        $test_model = TestModel::find(123);
        $this->assertSame($test_model, null);
        // find(ある)
        $test_model = TestModel::find(1);
        $this->assertSame(get_class($test_model), TestModel::class);

        // update + filter
        $this->assertSame($test_model->val, 'Val0123456789'); //「修正項目が変わっている」前を確認
        $r = $test_model->update(['val' => 'valval1234', 'val_guard' => 'valguard0123456789']);
        $this->assertNotSame($r, false);
        $this->assertSame($test_model->val, 'valval1234'); //「修正項目が変わっている」事を確認

        // 省略付きのupdate
        $r = $test_model->update(['val2' => 'Val0123456788']);
        $this->assertSame($r, true);

        // findBy
        $obj = TestModel::findBy('val_guard', 'valguard0123456789');
        $this->assertSame(get_class($obj), TestModel::class);
        $this->assertSame($obj->mode_1_id, 1);

        // 複合主キー : created_at / updated_at なし
        // insert
        $r = TestModelMultiKey::insert([
            'mode_2_id' => 1,
            'mode_2_key' => 'key',
            'val' => 'val',
            ]);
        $this->assertNotSame($r, null);
        $this->assertSame(get_class($r), TestModelMultiKey::class);
        $this->assertSame($r->val, 'val');

        // find
        $objMulti = TestModelMultiKey::find(['mode_2_id' => 1,'mode_2_key' => 'key']);
        $this->assertSame(get_class($objMulti), TestModelMultiKey::class);
        // update
        $r = $objMulti->update(['val' => 'valxxx']);
        $this->assertSame($r, true);
        // findBy
        $objMulti2 = TestModelMultiKey::findBy(['val' => 'valxxx']);
        $this->assertSame(get_class($objMulti2), TestModelMultiKey::class);

        // auto_incrementの確認
        // insert
        $obj3 = TestModelAutoIncrement::insert(['val' => 'test']);
        // find
        $this->assertSame(is_numeric($obj3->mode_3_id), true);

        //
        $data = TestModelAutoIncrement::findByAll('val', 'test');
        $this->assertLessThanOrEqual(count($data->toArray()), 1);
        //
        $data = TestModelAutoIncrement::findByAll(['val' => 'testdummy']);
        $this->assertSame(count($data->toArray()), 0);

        // トランザクションの確認
        $test_model = TestModel::find(1);
        $this->assertSame(strpos(strtoupper(TestModel::getJustBeforeQuery()), ' FOR UPDATE'), false );
        $this->assertSame($dbh->isTran(), false); // isTran()
        // begin
        $dbh->beginTransaction();
        $this->assertSame($dbh->isTran(), true); // isTran()
        {
            // find
            $test_model = TestModel::find(1);
            $this->assertNotSame(strpos(strtoupper(TestModel::getJustBeforeQuery()), ' FOR UPDATE'), false );
        }
        // commit
        $dbh->commit();
        $this->assertSame($dbh->isTran(), false); // isTran()

        // 「空のfindByAll」
        $r = TestModel::findByAll();
        $this->assertNotSame($r, null);
        $r = TestModel::findByAll([]);
        $this->assertNotSame($r, null);

        // 複合主キー delete
        $r = $objMulti->delete();
        $this->assertNotSame($r, false);
        $obj = TestModelMultiKey::find(['mode_2_id' => 1,'mode_2_key' => 'key']);
        $this->assertSame($obj, null);

        // 単純key delete
        $r = $test_model->delete();
        $this->assertNotSame($r, false);
        $obj = TestModel::find(1);
        $this->assertSame($obj, null);

        // auto_increment delete
        $obj3_id = $obj3->mode_3_id;
        $r = $obj3->delete();
        $this->assertNotSame($r, false);
        $obj = TestModelAutoIncrement::find($obj3_id);
        $this->assertSame($obj, null);

        // getInsertColumnsListとgetUpdateColumnsListのテスト
        $insert_list = TestColumnsList::getInsertColumnsList();
        $this->assertArrayHasKey('common_1', $insert_list);
        $this->assertArrayHasKey('common_2', $insert_list);
        $this->assertArrayHasKey('insert_1', $insert_list);
        $this->assertArrayNotHasKey('update_1', $insert_list);
        $update_list = TestColumnsList::getUpdateColumnsList();
        $this->assertArrayHasKey('common_1', $update_list);
        $this->assertArrayHasKey('common_2', $update_list);
        $this->assertArrayHasKey('update_1', $update_list);
        $this->assertArrayNotHasKey('insert_1', $update_list);

        // order byの確認
        $dbh->query('delete from mode_1;');
        $r = TestModel::insert(['mode_1_id' => '22', 'val' => 'aVal012345', 'val_guard' => 'valguard0123456789', ]);
        $r = TestModel::insert(['mode_1_id' => '999', 'val' => 'valval1234', 'val_guard' => 'valguard0123456789', ]);
        $r = TestModel::insert(['mode_1_id' => '99', 'val' => 'valval1234', 'val_guard' => 'valguard0123456789', ]);
        $r = TestModel::insert(['mode_1_id' => '55', 'val' => 'valval1235', 'val_guard' => 'valguard0123456789', ]);
        //
        $data = TestModel::findByAll('val_guard', 'valguard0123456789', 'mode_1_id');
        //var_dump( DB::getHandle()::getSql() );
        //var_dump( DB::getHandle()::getData() );
        $ids = [22, 55, 99, 999];
        foreach($data as $no => $datum) {
            $this->assertSame($datum->mode_1_id, $ids[$no]);
        }
        $data = TestModel::findByAll(['val_guard' => 'valguard0123456789'], ['mode_1_id']);
        foreach($data as $no => $datum) {
            $this->assertSame($datum->mode_1_id, $ids[$no]);
        }
        $data = TestModel::findByAll(['val_guard' => 'valguard0123456789'], 'mode_1_id');
        foreach($data as $no => $datum) {
            $this->assertSame($datum->mode_1_id, $ids[$no]);
        }

        //
        $data = TestModel::findByAll('val_guard', 'valguard0123456789', ['val DESC', 'mode_1_id DESC']);
        $ids = [55, 999, 99, 22];
        foreach($data as $no => $datum) {
            $this->assertSame($datum->mode_1_id, $ids[$no]);
        }

        // validate additional ruleのテスト
        try {
            $r = TestModelAddRule::insert([]);
        } catch (\SlimLittleTools\Exception\ModelValidateException $e) {
            $o = $e->getErrorObj();
            $this->assertSame(isset($o['TestModelAddRule']), true);
            $this->assertSame($o['TestModelAddRule'], ['hoge']);
        }


        // 「日付で空文字なら削除」処理のテスト
        $data = [
            's' => 'aaa',
            'date_1' => '1970-1-1',
            'date_2' => '1970-1-1',
        ];
        $data = TestModelDate::deleteEmptyDates($data);
        // データが消えていない事を一応確認
        $this->assertSame($data['date_1'], '1970-1-1');
        $this->assertSame($data['date_2'], '1970-1-1');
        // データがnullになる事を確認
        $data = [
            's' => '',
            'date_1' => '',
            'date_2' => '',
        ];
        $data = TestModelDate::deleteEmptyDates($data);
        $this->assertSame($data['s'], '');
        $this->assertSame($data['date_1'], null);
        $this->assertSame($data['date_2'], null);

        // insertでNULLになる事を確認
        $obj = TestModelDate::insert(['s' => 'abc', 'date_1' => '', 'date_2' => '']);
        $id = $obj->mode_date_id;
        $obj = TestModelDate::find($id);
        $this->assertSame($obj->date_1, null);
        $this->assertSame($obj->date_2, null);

        // updateで一端日付を入れる
        $obj->update(['date_1' => '1970-1-1', 'date_2' => '1970-1-1 00:11:22']);
        $obj = TestModelDate::find($id);
        $this->assertSame($obj->date_1, '1970-01-01');
        $this->assertSame($obj->date_2, '1970-01-01 00:11:22');

        // updateで空文字を渡してNULLになる事を確認
        $obj->update(['date_1' => '', 'date_2' => '']);
        $obj = TestModelDate::find($id);
        $this->assertSame($obj->date_1, null);
        $this->assertSame($obj->date_2, null);

    }

    // 違うDBハンドルを使うクラスの確認
    // XXX

    /**
     * validateの例外
     *
     * expectedException \SlimLittleTools\Exception\ModelValidateException
     */
    public function testModelValidateException()
    {
        //TestModel::insert(['val' => 'abc']);
        try {
            TestModel::insert(['val' => 'abc']);
        } catch (\SlimLittleTools\Exception\ModelValidateException $e) {
            $this->assertTrue(true);
            return ;
        }
        // else
        $this->assertTrue(false);
    }

    /**
     * updateでガード句の例外
     *
     * @depends testAll
     * expectedException \SlimLittleTools\Exception\ModelGuardException
     */
    public function testModelGuardException()
    {
        //$r = TestModel::insert(['mode_1_id' => '10', 'val' => 'Val0123456789', 'val_guard' => 'ValGuard0123456789', ]);
        //$r->update(['val_guard' => 'XXXXXXXXXXXXXXXX']);
        try {
            $r = TestModel::insert(['mode_1_id' => '10', 'val' => 'Val0123456789', 'val_guard' => 'ValGuard0123456789', ]);
            $r->update(['val_guard' => 'XXXXXXXXXXXXXXXX']);
        } catch (\SlimLittleTools\Exception\ModelGuardException $e) {
            $this->assertTrue(true);
            return ;
        }
        // else
        $this->assertTrue(false);
    }

    /**
     * insertでauto_incrementの例外
     *
     * @depends testAll
     * expectedException \SlimLittleTools\Exception\ModelGuardException
     */
    public function testModelGuardException2()
    {
        //$obj3 = TestModelAutoIncrement::insert(['mode_3_id' => 100, 'val' => 'test']);
        try {
            $obj3 = TestModelAutoIncrement::insert(['mode_3_id' => 100, 'val' => 'test']);
        } catch (\SlimLittleTools\Exception\ModelGuardException $e) {
            $this->assertTrue(true);
            return ;
        }
        // else
        $this->assertTrue(false);
    }

    /**
     * 単純なSQLのエラー例外: 存在しないカラム
     *
     * expectedException \SlimLittleTools\Exception\DbException
     * @expectedException \Error
XXXXXX
    public function testDbException()
    {
        $r = TestModel::insert(['mode_1_id' => '10', 'val' => 'Val0123456789', 'val_guard' => 'ValGuard0123456789', 'dummy' => '999']);
    }
     */

    /**
     * 単純なSQLのエラー例外: 存在しないテーブル
     *
     * @expectedException \SlimLittleTools\Exception\DbException
XXXXXX
    public function testDbException2()
    {
        $this->assertSame('', '');
    }
     */

    /**
     * 複合主キーの時にfindでstring(またはint)を指定したケース
     *
     * @expectedException \SlimLittleTools\Exception\ModelGuardException
XXXXXX
    public function testDbException2()
    {
        $this->assertSame('', '');
    }
     */
}
