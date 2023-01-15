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
            'COLUMN_1',
            'COLUMN_2',
            'COLUMN_3',
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
        $this->headers->setColumns([
            'COLUMN_1',
            'COLUMN_2',
            'COLUMN_3',
        ]);

        $this->validator->validate($this->headers, new ExcelHeaders());

        $this->assertNoViolation();
    }

    public function testExcessColumns()
    {
        $this->headers->setColumns([
            'COLUMN_1',
            'COLUMN_2',
            'COLUMN_3',
            '__EXCESS_COLUMN__',
        ]);

        $constrain = new ExcelHeaders();
        $this->validator->validate($this->headers, $constrain);

        $this->buildViolation($constrain->excessMessage)
            ->setParameter('{{ columns }}', '"__EXCESS_COLUMN__"')
            ->assertRaised();
    }

    public function testMissingColumns()
    {
        $this->headers->setColumns([
            'COLUMN_1',
            'COLUMN_2',
        ]);

        $constrain = new ExcelHeaders();
        $this->validator->validate($this->headers, $constrain);

        $this->buildViolation($constrain->missingMessage)
            ->setParameter('{{ columns }}', '"COLUMN_3"')
            ->assertRaised();
    }

    public function testNotOrderingColumns()
    {
        $this->headers->setColumns([
            'COLUMN_1',
            'COLUMN_3',
            'COLUMN_2',
        ]);

        $constrain = new ExcelHeaders();
        $this->validator->validate($this->headers, $constrain);

        $this->buildViolation($constrain->notOrderedMessage)
            ->setParameter('{{ columns }}', '"COLUMN_3", "COLUMN_2"')
            ->assertRaised();
    }
}
