<?php
declare(strict_types=1);

namespace SlimLittleTools\Libs\Http;

use SlimLittleTools\Libs\Security;

class Cookies extends \Slim\Psr7\Cookies
{
    /**
     *
     */
    public function get($key, $default = null)
    {
        return Security::checkEncoding(parent::get($key, $default));
    }

    /**
     * 「１つだけパラメタを変えたい」ような時用
     *
     * XXX 「一時的に設定を変えたい」時は、valueを配列にして ['value' => 値, ...設定値] にすればよいみたい、なので、本継承は廃止
    public function set(string $name, $value) : \Slim\Psr7\Cookies
    {
    }
     */

    /**
     * 概ねラッパー
     */
    public function delete($name)
    {
        $this->set($name, ['value' => '', 'httponly' => true, 'expires' => date(DATE_COOKIE, 1) ]);
    }
}
