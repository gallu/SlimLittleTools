<?php

namespace SlimLittleTools\Exception;

/**
 *
 *
 */

class ModelValidateException extends \ErrorException
{
    //
    public function setErrorObj($obj)
    {
        $this->error_obj = $obj;
        return $this;
    }
    public function getErrorObj($obj)
    {
        return $this->error_obj;
    }

    //private
    private $error_obj;
}
