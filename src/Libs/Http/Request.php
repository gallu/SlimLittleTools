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

    /**
     * リクエストされたIPアドレスを返す
     *
     * 一端「HTTP_X_FORWARDED_FOR または REMOTE_ADDR」を想定。一応外からも「個別の(優先順位の高い)名前」を指定出来るようにはしておく
     */
    public function getSrcIp(array $env_name = []) : string
    {
        // XXX 特別に指定があった場合で「その名前があった」場合はそれを返す
        foreach($env_name as $s) {
            if ('' !== ($ip = $this->getServerParam($s, ''))) {
                return $ip;
            }
        }
        // else
        $ip = $this->getServerParam('HTTP_X_FORWARDED_FOR') ?? $this->getServerParam('REMOTE_ADDR', '');
        return $ip;
    }

}
