<?php

require __DIR__ . '/../app/Helpers/Validator.php';

use App\Helpers\Validator;

function assertEqual($expected, $actual, $message) {
    if ($expected !== $actual) {
        echo "FAIL: {$message}\n";
        echo "Expected: " . var_export($expected, true) . "\n";
        echo "Actual: " . var_export($actual, true) . "\n";
        exit(1);
    }

    echo "PASS: {$message}\n";
}

Validator::clearErrors();
assertEqual(true, Validator::numeric('price', '19.99'), 'decimal price is numeric');
assertEqual([], Validator::getErrors(), 'decimal price has no validation errors');

Validator::clearErrors();
assertEqual(true, Validator::numeric('stock', '25'), 'whole-number stock is numeric');
assertEqual([], Validator::getErrors(), 'whole-number stock has no validation errors');

Validator::clearErrors();
assertEqual(false, Validator::numeric('price', 'free'), 'word price is rejected');
assertEqual(
    ['price' => 'Price must be a number!'],
    Validator::getErrors(),
    'word price creates a numeric validation error'
);

Validator::clearErrors();
assertEqual(false, Validator::required('price', ''), 'empty price fails required');
assertEqual(true, Validator::numeric('price', ''), 'empty price does not overwrite required error');
assertEqual(
    ['price' => 'Price is required!'],
    Validator::getErrors(),
    'required message remains for empty price'
);
