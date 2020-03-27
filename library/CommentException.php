<?php

namespace addons\cms\library;


use think\Exception;
use Throwable;

class CommentException extends Exception
{
    public function __construct($message = "", $code = 0, $data = [])
    {
        $this->message = $message;
        $this->code = $code;
        $this->data = $data;
    }

}