<?php

namespace App\Tests\Validator;

use App\Model\ExcelHeadersModel;
use App\Validator\ExcelHeaders;
use App\Validator\ExcelHeadersValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ExcelHeadersValidatorTest extends ConstraintValidatorTestCase
{
    private ExcelHeadersModel $headers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->headers = new ExcelHeadersModel([
            'HEADER_1',
            'HEADER_2',
            'HEADER_3',
        ]);
    }

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

    public function testThrowsExceptionIfNotArrayCompatibleType()
    {
        $this->expectException(UnexpectedValueException::class);

        $this->validator->validate(new \stdClass(), new ExcelHeaders());
    }

    public function testHeadersAreValid()
    {
        $this->headers->setHeaders([
            'HEADER_1',
            'HEADER_2',
            'HEADER_3',
        ]);

        $this->validator->validate($this->headers, new ExcelHeaders());

        $this->assertNoViolation();
    }

    public function testExcessHeaders()
    {
        $this->headers->setHeaders([
            'HEADER_1',
            'HEADER_2',
            'HEADER_3',
            '__EXCESS_HEADER__',
        ]);

        $constrain = new ExcelHeaders();
        $this->validator->validate($this->headers, $constrain);

        $this->buildViolation($constrain->excessMessage)
            ->setParameter('{{ headers }}', '"__EXCESS_HEADER__"')
            ->assertRaised();
    }

    public function testMissingHeaders()
    {
        $this->headers->setHeaders([
            'HEADER_1',
            'HEADER_2',
        ]);

        $constrain = new ExcelHeaders();
        $this->validator->validate($this->headers, $constrain);

        $this->buildViolation($constrain->missingMessage)
            ->setParameter('{{ headers }}', '"HEADER_3"')
            ->assertRaised();
    }

    public function testNotOrderingHeaders()
    {
        $this->headers->setHeaders([
            'HEADER_1',
            'HEADER_3',
            'HEADER_2',
        ]);

        $constrain = new ExcelHeaders();
        $this->validator->validate($this->headers, $constrain);

        $this->buildViolation($constrain->notOrderedMessage)
            ->setParameter('{{ headers }}', '"HEADER_3", "HEADER_2"')
            ->assertRaised();
    }
}
