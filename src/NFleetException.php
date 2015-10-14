<?php

include "ErrorData.php";

class NFleetException extends Exception {
    public $Items;

    public function __construct($items) {
        $errors = array();
        foreach($items->Items as $error) {
            $item = new ErrorData($error->Code, $error->Message);
            array_push($errors, $item);
        }
        $this->Items = $errors;
    }

    public function __toString() {
        $returnString = "";
        foreach ($this->Items as $error) {
            $returnString .= " Code: " . $error->Code . " - " . $error->Message . ".\n";
        }

        return __CLASS__ . $returnString;
    }
}