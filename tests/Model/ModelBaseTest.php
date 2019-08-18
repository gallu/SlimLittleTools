<?php

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


// テスト本体
class ModelTest extends \PHPUnit\Framework\TestCase
{
    // 一回だけ実行される開始前メソッド
    public static function setUpBeforeClass()
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
                    'pass' => 'XXXXXX',
                    'charset' => 'utf8mb4',
                    'options' => [\PDO::ATTR_EMULATE_PREPARES => false],
                ],
            ],
        ];
        $app = new \Slim\App($settings);
        //
        DB::setContainer($app->getContainer());
    }
    // テストメソッドごとの開始前メソッド
    protected function setUp()
    {
        // リアルなDB接続が必要なので、一旦スキップ
        //$this->markTestSkipped();
    }
    // -----
    // テストメソッドごとの終了メソッド
    protected function tearDown()
    {
    }
    // 一回だけ実行される終了メソッド
    public static function tearDownAfterClass()
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
        $dbh->query('delete from mode_1;');
        $dbh->query('delete from mode_2;');

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
        $this->assertNotContains(' FOR UPDATE', strtoupper(TestModel::getJustBeforeQuery()));
        $this->assertSame($dbh->isTran(), false); // isTran()
        // begin
        $dbh->beginTransaction();
        $this->assertSame($dbh->isTran(), true); // isTran()
        {
            // find
            $test_model = TestModel::find(1);
            $this->assertContains(' FOR UPDATE', strtoupper(TestModel::getJustBeforeQuery()));
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

        // 実際のinsert
        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/?mode_1_id=99&val=Val0123456789&val_guard=ValGuard0123456789',
            ]
        );
        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);
        // その１
        $obj = TestModel::insertFromRequest($request);
        $this->assertNotSame($obj, null);
        $this->assertSame(get_class($obj), TestModel::class);
        $this->assertSame($obj->mode_1_id, 99);

        // 実際のupdate
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/?val=valval1234',
            ]
        );
        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);
        //
        $r = $obj->updateFromRequest($request);
        $this->assertNotSame($r, false);
        $this->assertSame($test_model->val, 'valval1234'); //「修正項目が変わっている」事を確認

        // 実際のinsert: formの名前が違うケース
        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/?id=999&form_val=Val0123456789&val_guard=ValGuard0123456789&dummy=123456',
            ]
        );
        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);
        // その１
        $list = TestModel::getInsertColumnsList();
        $list['mode_1_id'] = 'id';
        $list['val'] = 'form_val';
        $obj = TestModel::insertFromRequest($request, $list);
        $this->assertNotSame($obj, null);
        $this->assertSame(get_class($obj), TestModel::class);
        $this->assertSame($obj->mode_1_id, 999);


        // 実際のupdate: formの名前が違うケース
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/?form_val=valval1234&dummy=123456',
            ]
        );
        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);
        //
        $list = TestModel::getUpdateColumnsList();
        $list['val'] = 'form_val';
        $r = $obj->updateFromRequest($request, $list);
        $this->assertNotSame($r, false);
        $this->assertSame($test_model->val, 'valval1234'); //「修正項目が変わっている」事を確認

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

    }

    // 違うDBハンドルを使うクラスの確認
    // XXX

    /**
     * validateの例外
     *
     * @expectedException \SlimLittleTools\Exception\ModelValidateException
     */
    public function testModelValidateException()
    {
        TestModel::insert(['val' => 'abc']);
    }

    /**
     * updateでガード句の例外
     *
     * @depends testAll
     * @expectedException \SlimLittleTools\Exception\ModelGuardException
     */
    public function testModelGuardException()
    {
        $r = TestModel::insert(['mode_1_id' => '10', 'val' => 'Val0123456789', 'val_guard' => 'ValGuard0123456789', ]);
        $r->update(['val_guard' => 'XXXXXXXXXXXXXXXX']);
    }

    /**
     * insertでauto_incrementの例外
     *
     * @depends testAll
     * @expectedException \SlimLittleTools\Exception\ModelGuardException
     */
    public function testModelGuardException2()
    {
        $obj3 = TestModelAutoIncrement::insert(['mode_3_id' => 100, 'val' => 'test']);
    }

    /**
     * 単純なSQLのエラー例外: 存在しないカラム
     *
     * expectedException \SlimLittleTools\Exception\DbException
     * @expectedException \Error
    public function testDbException()
    {
        $r = TestModel::insert(['mode_1_id' => '10', 'val' => 'Val0123456789', 'val_guard' => 'ValGuard0123456789', 'dummy' => '999']);
    }
XXXXXX
     */

    /**
     * 単純なSQLのエラー例外: 存在しないテーブル
     *
     * @expectedException \SlimLittleTools\Exception\DbException
    public function testDbException2()
    {
// XXX
        $this->assertSame('', '');
    }
     */

    /**
     * 複合主キーの時にfindでstring(またはint)を指定したケース
     *
     * @expectedException \SlimLittleTools\Exception\ModelGuardException
    public function testDbException2()
    {
// XXX
        $this->assertSame('', '');
    }
     */
}
