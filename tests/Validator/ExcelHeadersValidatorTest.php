<?php

namespace App\Tests\Validator;

use App\Validator\ExcelHeaders;
use App\Validator\ExcelHeadersValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ExcelHeadersValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new ExcelHeadersValidator();
    }

    public function testNullIsValid()
    {
        $this->validator->validate(null, new ExcelHeaders());
        $this->assertNoViolation();
    }

    public function testEmptyIsValid()
    {
        $this->validator->validate('', new ExcelHeaders());
        $this->assertNoViolation();
    }

    public function testThrowsExceptionIfNotStringCompatibleType()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate(new \stdClass(), new ExcelHeaders());
    }

    /**
     * @dataProvider provideInvalidValues
     */
    public function testInvalidValues($values)
    {
        $constrain = new ExcelHeaders();
        $this->validator->validate($values, $constrain);

        $this->buildViolation($constrain->message)
            ->setParameter('{{ string }}', $values)
            ->assertRaised();
    }

    public function provideInvalidValues(): iterable
    {
        yield ['%'];
    }
}
