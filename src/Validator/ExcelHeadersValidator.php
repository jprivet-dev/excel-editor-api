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

        $excess = $this->excessColumns($value);

        if (count($excess)) {
            $this->context->buildViolation($constraint->excessMessage)
                ->setParameter('{{ columns }}', $this->formatValues($excess))
                ->addViolation();

            return;
        }

        $missing = $this->missingColumns($value);

        if (count($missing)) {
            $this->context->buildViolation($constraint->missingMessage)
                ->setParameter('{{ columns }}', $this->formatValues($missing))
                ->addViolation();

            return;
        }

        $notOrdered = $this->notOrderedColumns($value);

        if (count($notOrdered)) {
            $this->context->buildViolation($constraint->notOrderedMessage)
                ->setParameter('{{ columns }}', $this->formatValues($notOrdered))
                ->addViolation();
        }
    }

    protected function excessColumns(ExcelHeadersModel $headers): array
    {
        return array_diff(
            $headers->getColumns(),
            $headers->getExpectedColumns()
        );
    }

    protected function missingColumns(ExcelHeadersModel $headers): array
    {
        return array_diff(
            $headers->getExpectedColumns(),
            $headers->getColumns()
        );
    }

    protected function notOrderedColumns(ExcelHeadersModel $headers): array
    {
        return array_diff_assoc(
            $headers->getColumns(),
            $headers->getExpectedColumns()
        );
    }

}
