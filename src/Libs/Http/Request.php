<?php

namespace SlimLittleTools\Libs\Http;

use SlimLittleTools\Libs\Security;

class Request extends \Slim\Http\Request
{
    /**
     * 非最短形式のチェックを通す
     */
    public function getParam($key, $default = null)
    {
        return Security::checkEncoding(parent::getParam($key, $default));
    }
    public function getParsedBodyParam($key, $default = null)
    {
        return Security::checkEncoding(parent::getParsedBodyParam($key, $default));
    }
    public function getQueryParam($key, $default = null)
    {
        return Security::checkEncoding(parent::getQueryParam($key, $default));
    }
    public function getCookieParam($key, $default = null)
    {
        return Security::checkEncoding(parent::getCookieParam($key, $default));
    }

    /**
     * 指定したパラメタをまとめて取得
     */
    public function getSpecifiedParams($params, $add_null_flg = false)
    {
        $ret = [];
        foreach ($params as $name) {
            $v = $this->getParam($name);
            if ( (true === $add_null_flg) || (null !== $v) ) {
                $ret[$name] = $v;
            }
        }
        return $ret;
    }
}
