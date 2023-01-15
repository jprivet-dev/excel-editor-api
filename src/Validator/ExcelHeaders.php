<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

class ExcelHeaders extends Constraint
{
    public string $message = 'This is not a valid headers.';
    public string $excessMessage = 'Columns are in excess [{{ columns }}].';
    public string $missingMessage = 'Columns are missing [{{ columns }}].';
    public string $notOrderedMessage = 'The columns are not in the right order [{{ columns }}].';
}
