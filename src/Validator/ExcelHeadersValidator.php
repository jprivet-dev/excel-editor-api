<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ExcelHeadersValidator extends ConstraintValidator
{
    private $expectedHeaders = [
        'Nom du groupe',
        'Origine',
        'Ville',
        'Année début',
        'Année séparation',
        'Fondateurs',
        'Membres',
        'Courant musical',
        'Présentation',
    ];

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ExcelHeaders) {
            throw new UnexpectedTypeException($constraint, ExcelHeaders::class);
        }

        /* @var ExcelHeaders $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        $excess = $this->excessColumns($value, $this->expectedHeaders);
        $excessCount = count($excess);

        if ($excessCount) {
            $this->context->buildViolation($constraint->excessMessage)
                ->setParameter('{{ columns }}', $this->formatValues($excess))
                ->addViolation();

            return;
        }

        $missing = $this->missingColumns($value, $this->expectedHeaders);
        $missingCount = count($missing);

        if ($missingCount) {
            $this->context->buildViolation($constraint->missingMessage)
                ->setParameter('{{ columns }}', $this->formatValues($missing))
                ->addViolation();

            return;
        }

        $notOrdered = $this->notOrderedColumns($value, $this->expectedHeaders);

        if (count($notOrdered)) {
            $this->context->buildViolation($constraint->notOrderedMessage)
                ->setParameter('{{ columns }}', $this->formatValues($notOrdered))
                ->addViolation();
        }
    }

    protected function excessColumns(array $columns, array $expectedColumns): array
    {
        return array_diff($columns, $expectedColumns);
    }

    protected function missingColumns(array $columns, array $expectedColumns): array
    {
        return array_diff($expectedColumns, $columns);
    }

    protected function notOrderedColumns(array $columns, array $expectedColumns): array
    {
        return array_diff_assoc($columns, $expectedColumns);
    }

}
