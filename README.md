# SlimLittleTools

Slim Framework用の、比較的小さなツール群です。

## インストールについて

```
composer require gallu/slim-littie-tools
```

でインストールしてください。

## ./:

### StaticBase.php

「静的メソッドのみ」クラス用の、インスタンス生成ガードクラス

### WithContainerBase.php

「コンストラクタでContainerを受け取る」クラス用の基底クラス

### WithStaticContainerBase.php 

「static プロパティでContainerを受け取る」クラス用の基底クラス

## ./Controller:

### ControllerBase.php

Controller用の継承クラス。コンストラクタでContainerを所持するようにしている。

## ./Exception:

内部的に使う例外クラスが収められています。

## ./Libs:

### Config.php

Container内の「setting」の設定を簡単に取得する為のラッパー

### DB.php ConnectPDO.php ConnectPDODummy.php

「settingに設定をいれたらDBハンドルへの接続＆Containerへの設定」と「DBハンドルの取得」ができるクラス    
PDOとあるが、実際には拡張されたPDOクラス

### PDO.php

上述に記載のある、拡張されたPDOクラス。    
トランザクションの有無が把握できる + 1メソッドでプリペアドステートメントが発行できる。

### Filter.php

データを、ルールにそってフィルタリング(データの修正/変形)を行います。    
ルールを複数記述する場合は、 | でつなげてください。

- trim  trim関数で前後の空白などを除去
- empty_string_to_null　空文字ならNULLに変換する
- strtolower  strtolower関数で処理(英字をすべて小文字にする) 
- strtoupper  strtoupper関数で処理(英字をすべて大文字にする)
- floor  端数の切り捨て(型はfloat型になる)
- ceil  端数の切り上げ(型はfloat型になる)
- abs  絶対値
- int  int型へのキャスト
- string  string型へのキャスト
- zip_hyphen  ハイフン付きの郵便番号にフォーマット(郵便番号としてinvalidなら入力値をそのまま返す)
- zip_space  スペース付きの郵便番号にフォーマット(郵便番号としてinvalidなら入力値をそのまま返す)
- zip_shorten  数字のみの郵便番号にフォーマット(郵便番号としてinvalidなら入力値をそのまま返す)
- hirakana　カタカナとひらがなをすべて「全角ひらがな」にする
- katakana　カタカナとひらがなをすべて「全角カタカナ」にする

### Validator.php

値が「正しいか」のチェック    
※ required無し、かつ入力が空文字の場合は、validateはtrueを返します


- required  必須チェック 
- datetime  日次フォーマットチェック
- alpha  アルファベット 
- alpha_num  アルファベットまたは数
- min_length:数  (文字としての)最低文字数(バイト長)。引数の数未満ならエラー
- max_length:数  (文字としての)最大文字数(バイト長)。引数の数を超えるならエラー
- range_length:数-数  (文字としての)範囲(バイト長)。引数は「ｎ以上ｍ以下」。範囲外ならエラー
- min_m_length:数  (文字としての)最低文字数(文字数(mb_length))。引数の数未満ならエラー
- max_m_length:数  (文字としての)最大文字数(文字数(mb_length))。引数の数を超えるならエラー
- range_m_length:数-数  (文字としての)範囲(文字数(mb_length))。引数は「ｎ以上ｍ以下」。範囲外ならエラー
- min_number:数  (数値としての)最低値。引数の数未満ならエラー
- max_number:数  (数値としての)最大値。引数の数を超えるならエラー
- range_number:数-数  (数値としての)範囲。引数は「ｎ以上ｍ以下」。範囲外ならエラー
- compare_with  自身のカラム名＋'_check' を探して、値をチェック。等しくなければエラー。パスワードとかemailで使う想定
- int  int型、もしくは「intとして認識可能な数字だけの文字列」であること
- float  float型、もしくは「floatとして認識可能な数字だけの文字列」であること
- zip  郵便番号形式( nnn-nnnn , nnn nnnn , nnnnnnn )であること
- tel  電話番号形式( ハイフン、スペース、数字のみは一通り対応)であること
- hirakana　文字がすべて「全角ひらがな(かスペース(全角半角ともOK))」でること
- katakana　文字がすべて「全角カタカナ(かスペース(全角半角ともOK))」でること

## ./Libs/Http:

### Cookies.php

getについて「非最短形式の場合には空文字を返す」ようにチェック。     
setについて「第三引数にsettingを指定したら"そのCookieだけ"defaultを変更する」処理を追加。    
deleteメソッドを追加。    

### Request.php

getParam等について、「非最短形式の場合には空文字を返す」ようにチェック(Param's'系はそのフックを入れていないので注意)。   
getSpecifiedParams()メソッドの追加(引数で渡した配列のカラムをまとめて取得)。    

## ./Middleware:

### AddHeader.php

主にセキュリティ上などの理由で「入れておきたい」ヘッダを追加で仕込みます。    
上書き等したい場合は、$setting['setting']['add_response_header'] に、[ key => value ]の形式で記述すると、上書きされます。

### Cookie.php

「containerにCookieインスタンスを入れる」「終了時に、Set-Cookieを仕込む」処理を自動で行います。

### CsrfGuard.php

Slim-Csrf( https://github.com/slimphp/Slim-Csrf )の軽いラッパーです。    
全体にMiddlewareを割り当てつつ「ルート名がこの名前の時はチェックをしない」処理を追加しています。    
setNotCoveredList()メソッドで、「除外対象のルート名」を渡してください。

### SlimLittleToolsUse.php

ConfigやDBなど、いくつかのLibs内のクラスが有効に働くようにします。具体的には「静的プロパティにcontainerを入れます」。

## ./Model:

### ModelContainer.php

「Modelの配列」です。ほぼ配列そのものですが、toAarray()メソッドを叩くと「中に保持している各modelのtoArray()を叩いて結果を返す」実装が追加されています。


### ModelBase.php

いわゆる「Model」クラスの基底です。    
細かい使い方は、別リンクで確認してください。    


## ./Trait:


## ./src:

Slim-Skeleton 系で「src」の中によく入っているコードの「推奨サンプル」になります。    
ほかに必要な設定もあるかと思うので、コピペして使うとよいと思います。   

## 戻り値の早見表

### Model

<dl>
  <dt>insert
    <dd>null / 自身のインスタンス
  <dt>update
    <dd>false / true
  <dt>delete
    <dd>false / PDOStatementインスタンス
  <dt>find / findBy
    <dd>null / 自身のインスタンス
  <dt>findByAll
    <dd>null / ModelCollectionインスタンス
</dl>

### PDO

<dl>
  <dt>preparedQuery
    <dd>false / PDOStatementインスタンス
</dl>


