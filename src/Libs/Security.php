<?php

namespace SlimLittleTools\Libs;

class Security
{
    /**
     * 非最短形式のチェック
     */
    public static function checkEncoding($val)
    {
        //
        if (is_string($val)) {
            if (false === mb_check_encoding($val, 'UTF8')) {
                return '';
            }
            // else
            return $val;
        }
        //
        if (is_array($val)) {
            $ret = [];
            foreach ($val as $v) {
                $ret[] = static::checkEncoding($v);
            }
            return $ret;
        }
        // else
        return $val;
    }
}
