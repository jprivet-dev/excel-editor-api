<?php

namespace App\Tests\Validator;

use App\Validator\DataTableHeaders;
use App\Validator\DataTableHeadersValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class DataTableHeadersValidatorTest extends ConstraintValidatorTestCase
{
    protected array $expectedHeaders = ['HEADER_1', 'HEADER_2', 'HEADER_3',];

    protected function createValidator(): DataTableHeadersValidator
    {
        return new DataTableHeadersValidator();
    }

    public function testNullIsValid()
    {
        $value = null;

        $constraint = new DataTableHeaders($this->expectedHeaders);
        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }

    public function testEmptyIsValid()
    {
        $value = '';

        $constraint = new DataTableHeaders($this->expectedHeaders);
        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }

    public function testThrowsExceptionIfNotArrayCompatibleType()
    {
        $this->expectException(UnexpectedValueException::class);

        $value = new \stdClass();

        $constraint = new DataTableHeaders($this->expectedHeaders);
        $this->validator->validate($value, $constraint);
    }

    public function testHeadersAreValid()
    {
        $value = ['HEADER_1', 'HEADER_2', 'HEADER_3',];

        $constraint = new DataTableHeaders($this->expectedHeaders);
        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }

    public function testExcessHeaders()
    {
        $value = ['HEADER_1', 'HEADER_2', 'HEADER_3', '__EXCESS_HEADER__',];

        $constraint = new DataTableHeaders($this->expectedHeaders);
        $this->validator->validate($value, $constraint);

        $this->buildViolation($constraint->excessMessage)
            ->setParameter('{{ headers }}', '"__EXCESS_HEADER__"')
            ->assertRaised();
    }

    public function testMissingHeaders()
    {
        $value = ['HEADER_1', 'HEADER_2',];

        $constraint = new DataTableHeaders($this->expectedHeaders);
        $this->validator->validate($value, $constraint);

        $this->buildViolation($constraint->missingMessage)
            ->setParameter('{{ headers }}', '"HEADER_3"')
            ->assertRaised();
    }

    public function testNotOrderingHeaders()
    {
        $value = ['HEADER_1', 'HEADER_3', 'HEADER_2',];

        $constraint = new DataTableHeaders($this->expectedHeaders);
        $this->validator->validate($value, $constraint);

        $this->buildViolation($constraint->notOrderedMessage)
            ->setParameter('{{ headers }}', '"HEADER_3", "HEADER_2"')
            ->assertRaised();
    }

    public function testInvalidConstraint()
    {
        $this->expectException(UnexpectedTypeException::class);
        $constraint = $this->getMockForAbstractClass(Constraint::class);
        $this->validator->validate(null, $constraint);
    }

}
