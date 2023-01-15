<?php

namespace App\Validator;

use App\Model\ExcelHeadersModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ExcelHeadersValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ExcelHeaders) {
            throw new UnexpectedTypeException($constraint, ExcelHeaders::class);
        }

        /* @var ExcelHeaders $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof ExcelHeadersModel) {
            throw new UnexpectedValueException($value, ExcelHeadersModel::class);
        }

        $excess = $this->excessHeaders($value);

        if (count($excess)) {
            $this->context->buildViolation($constraint->excessMessage)
                ->setParameter('{{ headers }}', $this->formatValues($excess))
                ->addViolation();

            return;
        }

        $missing = $this->missingHeaders($value);

        if (count($missing)) {
            $this->context->buildViolation($constraint->missingMessage)
                ->setParameter('{{ headers }}', $this->formatValues($missing))
                ->addViolation();

            return;
        }

        $notOrdered = $this->notOrderedHeaders($value);

        if (count($notOrdered)) {
            $this->context->buildViolation($constraint->notOrderedMessage)
                ->setParameter('{{ headers }}', $this->formatValues($notOrdered))
                ->addViolation();
        }
    }

    protected function excessHeaders(ExcelHeadersModel $headers): array
    {
        return array_diff(
            $headers->getHeaders(),
            $headers->getExpectedHeaders()
        );
    }

    protected function missingHeaders(ExcelHeadersModel $headers): array
    {
        return array_diff(
            $headers->getExpectedHeaders(),
            $headers->getHeaders()
        );
    }

    protected function notOrderedHeaders(ExcelHeadersModel $headers): array
    {
        return array_diff_assoc(
            $headers->getHeaders(),
            $headers->getExpectedHeaders()
        );
    }

}
