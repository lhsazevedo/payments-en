<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use App\Domain\ValueObject\TaxId;
use App\Domain\Exception\InvalidTaxIdException;

class TaxIdTest extends TestCase
{
    public function testConstructorRejectsNonDigits(): void
    {
        $this->expectException(InvalidTaxIdException::class);

        new TaxId('ABC123XYZ');
    }

    public function testConstructorRejectsInvalidLength(): void
    {
        $this->expectException(InvalidTaxIdException::class);

        // Length not 11 or 14
        new TaxId('1234567890'); // 10 digits
    }

    public function testCpfValidationNotImplemented(): void
    {
        $this->markTestSkipped('CPF validation algorithm not implemented yet');

        // 11 digits (CPF format)
        $cpf = '12345678909';

        $taxId = new TaxId($cpf);
        $this->assertSame($cpf, $taxId->value);
    }

    public function testCnpjValidationNotImplemented(): void
    {
        $this->markTestSkipped('CNPJ validation algorithm not implemented yet');

        // 14 digits (CNPJ format)
        $cnpj = '12345678000195';

        $taxId = new TaxId($cnpj);
        $this->assertSame($cnpj, $taxId->value);
    }
}
