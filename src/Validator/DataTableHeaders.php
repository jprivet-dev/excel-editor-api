<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

class DataTableHeaders extends Constraint
{
    public string $message = 'This is not a valid headers.';
    public string $excessMessage = 'Headers are in excess [{{ headers }}].';
    public string $missingMessage = 'Headers are missing [{{ headers }}].';
    public string $notOrderedMessage = 'The headers are not in the right order [{{ headers }}].';
    public array $expectedHeaders;

    public function __construct(array $expectedHeaders = [], mixed $options = null, array $groups = null, mixed $payload = null)
    {
        $this->expectedHeaders = $expectedHeaders ?? $this->expectedHeaders;

        parent::__construct($options, $groups, $payload);
    }
}
