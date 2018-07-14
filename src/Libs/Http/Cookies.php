<?php

namespace SlimLittleTools\Libs\Http;

use SlimLittleTools\Libs\Sscurity;

class Cookies extends \Slim\Http\Cookies
{
    /**
     *
     */
    public function get($key, $default = null)
    {
        return Sscurity::checkEncoding(parent::get($key, $default));
    }

    /**
     * 「１つだけパラメタを変えたい」ような時用
     */
    public function set($name, $value, $settings = null)
    {
        // settingsの指定がなかったらそのまま親メソッドの処理
        if (null === $settings) {
            return parent::set($name, $value);
        }
        // else
        // 一旦、現在の設定を退避
        $bak_defaults = $this->defaults;
        // defaultを変更
        $this->setDefaults($settings);
        // 親メソッドをcall
        $r = parent::set($name, $value);
        // defaultをもとに戻す
        $this->defaults = $bak_defaults;
        // 復帰
        return $r;
    }

    /**
     * 概ねラッパー
     */
    public function delete($name)
    {
        $this->set($name, '', ['httponly' => true, 'expires' => date(DATE_COOKIE, 1) ]);
    }
}
