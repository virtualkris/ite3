<?php
namespace App\Helpers;

class Validator {
    protected static $errors = [];

    //Check if a value is empty
    public static function required($fieldName, $value) {
        if (empty(trim($value))) {
            self::$errors[$fieldName] = ucfirst($fieldName) . " is required!";
            return false;
        }
        return true;
    }

    //Check for a minimum length
    public static function minLength($fieldName, $value, $min) {
        if (strlen(trim($value)) < $min) {
            self::$errors[$fieldName] = ucfirst($fieldName) . " must be at least {$min} characters!";
            return false;
        }
        return true;
    }

    //Get validation errors
    public static function getErrors() {
        return self::$errors;
    }

    //Clear validation errors
    public static function clearErrors() {
        self::$errors = [];
    }
}