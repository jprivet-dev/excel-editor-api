<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

class ExcelHeaders extends Constraint
{
    public string $message = 'This is not a valid headers.';
    public string $excessMessage = 'Headers are in excess [{{ headers }}].';
    public string $missingMessage = 'Headers are missing [{{ headers }}].';
    public string $notOrderedMessage = 'The headers are not in the right order [{{ headers }}].';
}
