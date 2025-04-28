<?php

declare(strict_types=1);

namespace Tests\Domain;

use App\Domain\Exception\InsufficientFundsException;
use App\Domain\Account;
use App\Domain\AccountType;
use App\Domain\ValueObject\TaxId;
use PHPUnit\Framework\TestCase;
use App\Domain\ValueObject\Amount;

class AccountTest extends TestCase
{
    public function testGetIdIsNullInitially(): void
    {
        $account = new Account(
            10,
            $this->createTaxId(),
            AccountType::Regular,
            new Amount(100),
        );

        $this->assertNull($account->getId());
    }

    public function testGetBalance(): void
    {
        $initial = new Amount(100);
        $account = new Account(
            10,
            $this->createTaxId(),
            AccountType::Regular,
            $initial,
        );

        $this->assertEquals($initial->value, $account->getBalance()->value);
    }

    public function testIsShopkeeperReturnsFalseForRegularAccount(): void
    {
        $account = new Account(
            10,
            $this->createTaxId(),
            AccountType::Regular,
            new Amount(0),
        );

        $this->assertFalse($account->isShopkeeper());
    }

    public function testIsShopkeeperReturnsTrueForShopkeeper(): void
    {
        $account = new Account(
            10,
            $this->createTaxId(),
            AccountType::Shopkeeper,
            new Amount(0),
        );

        $this->assertTrue($account->isShopkeeper());
    }

    public function testCreditIncreasesBalance(): void
    {
        $account = new Account(
            10,
            $this->createTaxId(),
            AccountType::Regular,
            new Amount(50),
        );

        $newBalance = $account->credit(new Amount(25));

        $this->assertEquals(75, $newBalance->value);
        $this->assertEquals(75, $account->getBalance()->value);
    }

    public function testDebitDecreasesBalance(): void
    {
        $account = new Account(
            10,
            $this->createTaxId(),
            AccountType::Regular,
            new Amount(100),
        );

        $newBalance = $account->debit(new Amount(30));

        $this->assertEquals(70, $newBalance->value);
        $this->assertEquals(70, $account->getBalance()->value);
    }

    public function testDebitThrowsWhenInsufficientFunds(): void
    {
        $this->expectException(InsufficientFundsException::class);

        $account = new Account(
            10,
            $this->createTaxId(),
            AccountType::Regular,
            new Amount(20),
        );

        $account->debit(new Amount(30));
    }
    private function createTaxId(): TaxId
    {
        return new TaxId('12345678900');
    }
}
