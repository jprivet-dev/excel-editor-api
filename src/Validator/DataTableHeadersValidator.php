<?php

namespace App\Validator;

use App\Model\DataTableModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class DataTableHeadersValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof DataTableHeaders) {
            throw new UnexpectedTypeException($constraint, DataTableHeaders::class);
        }

        /* @var DataTableHeaders $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        $excess = array_diff($value, $constraint->expectedHeaders);

        if (\count($excess)) {
            $this->context->buildViolation($constraint->excessMessage)
                ->setParameter('{{ headers }}', $this->formatValues($excess))
                ->addViolation();

            return;
        }

        $missing = array_diff($constraint->expectedHeaders, $value);

        if (\count($missing)) {
            $this->context->buildViolation($constraint->missingMessage)
                ->setParameter('{{ headers }}', $this->formatValues($missing))
                ->addViolation();

            return;
        }

        $notOrdered = array_diff_assoc($value, $constraint->expectedHeaders);

        if (\count($notOrdered)) {
            $this->context->buildViolation($constraint->notOrderedMessage)
                ->setParameter('{{ headers }}', $this->formatValues($notOrdered))
                ->addViolation();
        }
    }
}
