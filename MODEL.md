# Modelの基本

基本的に「1テーブルの1レコードを扱う」と思っていただいてよいです。
この「一塊のデータ」に対して

* DBからのselect
* DBへの、insert/update
* DBからのdelete

を行います。

## 指定する情報

以下を指定します。

```
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

    // PKがAUTO_INCREMENTのみのテーブルで、ここに明示的にtrueがあったら「insertの時にPKが指定されていたら例外を吐く」「insert後、PDO::lastInsertIdでとれる値をPKのカ
ラムに入れる」を行う
    protected $auto_increment = true;

    // DB suffix
    // 接続先が「db(デフォルト)」以外なら、ここにsuffixを記述しておく( SlimLittleTools\Libs\DB を使う前提)。通常は不要。
    protected $db_suffix = 'hoge';
```

## insertおよびupdate

この双方は、いくつか類似している部分があるので、重複部分はまとめて説明をします。    
insert/updateをする場合は、大まかに

* 事前にデータをフィルタリング(整形)
* validate(正当性チェック)
* insertないしupdate

という手順を踏みます。    
フィルタリングは「問答無用でデータを整形」します。    
validateは、結果として「invalidだった」可能性があるため、その場合は SlimLittleTools\Exception\ModelValidateException 例外を吐きます。    
insertないしupdateでエラーが発生した場合はnull(insert)またはfalse(update)をreturnします。    

## insert

insertに固有は指定は以下の通りです。    
ルールは、 |(バーティカルバー)でつなげる事で複数の指定が可能です。    

```
    // validate系設定
    // insert固有
    protected $validate_insert = [
        //'カラム名' => 'ルール',
        //'カラム名' => 'ルール',
    ];
    // insert / update共通
    protected $validate = [
        //'カラム名' => 'ルール',
        //'カラム名' => 'ルール',
    ];

    // filterルール設定
    // insert固有
    protected $filter_insert = [
        //'カラム名' => 'ルール',
        //'カラム名' => 'ルール',
    ];
    // insert / update共通
    protected $filter = [
        //'カラム名' => 'ルール',
        //'カラム名' => 'ルール',
    ];
```

基本的には「validate」と「filter」だけを定義しておけばよいと思います。    
insert自体は、以下の書式で行います。

`$modelObj = ModelClass::insert(データのhash配列)`

insertしたデータを含むインスタンスが、$modelObjに帰ってきます。    
validationを含むコードは、例えば以下のようになります。

```
try {
    $modelObj = ModelClass::insert(データのhash配列);
} catch (\SlimLittleTools\Exception\ModelValidateException $e) {
    //
    $error_array = $e->getErrorObj();
/*
// 例えば、こんな配列が帰ってきます
array(2) {
  ["カラム名"]=>
  array(1) {
    [0]=>
    string(6) "ルール"
  }
  ["カラム名"]=>
  array(2) {
    [0]=>
    string(6) "ルール"
    [1]=>
    string(6) "ルール"
  }
}
*/
    // エラー処理
}
if (null === $modelObj) {
    // (validateは成功したが)insertに失敗
}

// ここに来たらinsert成功

```

## update

updateに固有は指定は以下の通りです。

```
    // validate系設定
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
```

基本的には「validate」と「filter」だけを定義しておけばよいと思います。    
update自体は、以下の書式で行います。    

`$r = $modelObj->update(データのhash配列);`

結果として、インスタンスの中身が「引数で渡されたデータの中身」に置き換わります。    
validationを含むコードは、例えば以下のようになります。    

```
try {
    $r = $modelObj->update(データのhash配列);
} catch (\SlimLittleTools\Exception\ModelValidateException $e) {
    //
    $error_array = $e->getErrorObj();
/*
// 例えば、こんな配列が帰ってきます
array(2) {
  ["カラム名"]=>
  array(1) {
    [0]=>
    string(6) "ルール"
  }
  ["カラム名"]=>
  array(2) {
    [0]=>
    string(6) "ルール"
    [1]=>
    string(6) "ルール"
  }
}
*/
    // エラー処理
}
if (false === $r) {
    // (validateは成功したが)updateに失敗
}

// ここに来たらupdate成功

```

## インスタンスのデータのset/get

メソッドとしてget()があります。また __get() を定義しているので「$obj->カラム名」でも取得できます。    
set()メソッドはprotectedなので、基本的に外部には公開していません。インスタンスのデータを変更したい時は、updateメソッドをcallして「DBごと」変更を加えてください(この仕様はあとで変更するかもしれません)。    

また、データ全体を取得したい時は「toArray()」メソッドで「ハッシュ配列」を取り出す事ができます。    

## 検索

主キーが単一の場合は、以下で取得できます。    

```
$modelObj = ModelClass::find(1);
```

主キーが複合の場合は、以下で取得できます。    

```
$modelObj = ModelClass::find(['key1' => 1, 'key2' = 2]);
```

また、主キー以外で検索をしたい場合は、以下で可能です。    

```
$modelObj = ModelClass::findBy('no_key1'、1);
$modelObj = ModelClass::findBy(['no_key1' => 1, 'no_key2' = 2]);
```

なお、findByは「1レコード」を想定しています。複数レコードが帰ってきてしまうような検索条件の場合、どのレコードになるかは「不定」です。    
「全レコード」を取得したい場合は「findByAll()」メソッドを利用してください。使い方は一緒ですが、returnがSlimLittleTools\Model\ModelCollectionクラス(ほぼarrayObject。toArray()メソッドだけ生やしてある)が帰ってきます。    

```
$modelCollectionObj = ModelClass::findByAll('no_key1', 1);
$modelCollectionObj = ModelClass::findByAll(['no_key1' => 1, 'no_key2' = 2]);
```

また、findByAllは、ソート順を指定できます。    

```
$modelCollectionObj = ModelClass::findByAll('no_key1', 1, 'ソートカラム名');
$modelCollectionObj = ModelClass::findByAll(['no_key1' => 1, 'no_key2' = 2], 'ソートカラム名');
```

ソートカラム名は

* カンマで区切ると、複数指定できます(例: 'col_1, col_2')
* desc(DESC)を書くと、降順が指定できます(例: 'col_1 DESC')
* 上述は、組み合わせる事ができます(例: 'col_1, col_2 DESC')


## 削除

削除は「インスタンスの中にある主キーを削除」します。

`$modelObj->delete();`



