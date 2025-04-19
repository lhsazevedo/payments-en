<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use App\Domain\ValueObject\Amount;
use App\Domain\Exception\InvalidAmountException;

class AmountTest extends TestCase
{
    public function testConstructorRejectsNegativeValue(): void
    {
        $this->expectException(InvalidAmountException::class);

        new Amount(-1);
    }

    public function testZeroValueFormatsAsZeroReais(): void
    {
        $amount = new Amount(0);

        $this->assertSame('R$ 0,00', $amount->format());
        $this->assertSame('R$ 0,00', (string) $amount);
    }

    public function testPositiveValueFormatsCorrectly(): void
    {
        $amount = new Amount(12345);

        // 12345 cents = R$ 123,45
        $this->assertSame('R$ 123,45', $amount->format());
        $this->assertSame('R$ 123,45', (string) $amount);
    }

    public function testLargeValueWithThousandsSeparator(): void
    {
        // 123456789 cents = R$ 1.234.567,89
        $amount = new Amount(123456789);

        $expected = 'R$ 1.234.567,89';
        $this->assertSame($expected, $amount->format());
        $this->assertSame($expected, (string) $amount);
    }
}
