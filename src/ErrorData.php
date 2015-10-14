<?php

class ErrorData {
    public $Code;
    public $Message;

    public function __construct($code, $message) {
        $this->Code = $code;
        $this->Message = $message;
    }

    public function toString() {
        return "Code: " . $this->Code . ", " . $this->Message;
    }

}