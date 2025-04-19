<?php

declare(strict_types=1);

namespace Tests\Domain;

use App\Domain\Exception\InsufficientFundsException;
use App\Domain\User;
use App\Domain\UserType;
use App\Domain\ValueObject\TaxId;
use PHPUnit\Framework\TestCase;
use App\Domain\ValueObject\Amount;

class UserTest extends TestCase
{
    public function testGetIdIsNullInitially(): void
    {
        $user = new User(
            'Alice',
            '21987654321',
            'alice@exemplo.com.br',
            $this->createTaxId(),
            UserType::Regular,
            new Amount(100),
        );

        $this->assertNull($user->getId());
    }

    public function testGetBalance(): void
    {
        $initial = new Amount(100);
        $user = new User(
            'Alice',
            '21987654321',
            'alice@exemplo.com.br',
            $this->createTaxId(),
            UserType::Regular,
            $initial,
        );

        $this->assertEquals($initial->value, $user->getBalance()->value);
    }

    public function testIsShopkeeperReturnsFalseForRegularUser(): void
    {
        $user = new User(
            'Alice',
            '21987654321',
            'alice@exemplo.com.br',
            $this->createTaxId(),
            UserType::Regular,
            new Amount(0),
        );

        $this->assertFalse($user->isShopkeeper());
    }

    public function testIsShopkeeperReturnsTrueForShopkeeper(): void
    {
        $user = new User(
            'ACME Co.',
            '21987654321',
            'alice@exemplo.com.br',
            $this->createTaxId(),
            UserType::Shopkeeper,
            new Amount(0),
        );

        $this->assertTrue($user->isShopkeeper());
    }

    public function testCreditIncreasesBalance(): void
    {
        $user = new User(
            'Alice',
            '21987654321',
            'alice@exemplo.com.br',
            $this->createTaxId(),
            UserType::Regular,
            new Amount(50),
        );

        $newBalance = $user->credit(new Amount(25));

        $this->assertEquals(75, $newBalance->value);
        $this->assertEquals(75, $user->getBalance()->value);
    }

    public function testDebitDecreasesBalance(): void
    {
        $user = new User(
            'Alice',
            '21987654321',
            'alice@exemplo.com.br',
            $this->createTaxId(),
            UserType::Regular,
            new Amount(100),
        );

        $newBalance = $user->debit(new Amount(30));

        $this->assertEquals(70, $newBalance->value);
        $this->assertEquals(70, $user->getBalance()->value);
    }

    public function testDebitThrowsWhenInsufficientFunds(): void
    {
        $this->expectException(InsufficientFundsException::class);

        $user = new User(
            'Alice',
            '21987654321',
            'alice@exemplo.com.br',
            $this->createTaxId(),
            UserType::Regular,
            new Amount(20),
        );

        $user->debit(new Amount(30));
    }
    private function createTaxId(): TaxId
    {
        return new TaxId('12345678900');
    }
}
