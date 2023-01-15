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

    public function testThrowsExceptionIfNotArrayCompatibleType()
    {
        $this->expectException(UnexpectedValueException::class);

        $this->validator->validate(new \stdClass(), new ExcelHeaders());
    }


    public function testHeadersAreValid()
    {
        $value = [
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

        $this->validator->validate($value, new ExcelHeaders());

        $this->assertNoViolation();
    }

    public function testNotAllowedColumns()
    {
        $value = [
            'Nom du groupe',
            'Origine',
            'Ville',
            'Année début',
            'Année séparation',
            'Fondateurs',
            'Membres',
            'Courant musical',
            'Présentation',
            '__NOT_ALLOWED__',
        ];

        $constrain = new ExcelHeaders();
        $this->validator->validate($value, $constrain);

        $this->buildViolation($constrain->excessMessage)
            ->setParameter('{{ columns }}', '"__NOT_ALLOWED__"')
            ->assertRaised();
    }

    public function testMandatoryColumns()
    {
        $value = [
            'Nom du groupe',
            'Origine',
            'Ville',
            'Année début',
            'Année séparation',
            'Fondateurs',
            'Membres',
            'Courant musical',
        ];

        $constrain = new ExcelHeaders();
        $this->validator->validate($value, $constrain);

        $this->buildViolation($constrain->missingMessage)
            ->setParameter('{{ columns }}', '"Présentation"')
            ->assertRaised();
    }

    public function testNotOrderingColumns()
    {
        $value = [
            'Nom du groupe',
            'Origine',
            'Ville',
            'Année début',
            'Année séparation',
            'Fondateurs',
            'Membres',
            'Présentation',
            'Courant musical',
        ];

        $constrain = new ExcelHeaders();
        $this->validator->validate($value, $constrain);

        $parameter = implode(', ', ['"Présentation"', '"Courant musical"',]);
        $this->buildViolation($constrain->notOrderedMessage)
            ->setParameter('{{ columns }}', $parameter)
            ->assertRaised();
    }
}
