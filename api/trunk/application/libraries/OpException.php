<?php
/**
 * Created by PhpStorm.
 * User: dateng
 * Date: 7/5/15
 * Time: 10:04 AM
 */

class OpException extends Exception {
    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}