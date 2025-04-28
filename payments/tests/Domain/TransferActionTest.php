<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Domain\TransferAction;
use App\Domain\Payment;
use App\Domain\Account;
use App\Domain\Exception\InvalidPayerException;
use App\Domain\Services\EventPublisherContract;
use App\Domain\Services\PaymentAuthorizerContract;
use App\Domain\Services\PaymentRepositoryContract;
use App\Domain\Services\TransactionManagerContract;
use App\Domain\Services\AccountRepositoryContract;
use App\Domain\ValueObject\Amount;
use App\Domain\ValueObject\TaxId;
use App\Domain\AccountType;

class TransferActionTest extends TestCase
{
    private EventPublisherContract&MockObject $eventPublisher;
    private PaymentAuthorizerContract&MockObject $paymentAuthorizer;
    private PaymentRepositoryContract&MockObject $paymentRepository;
    private TransactionManagerContract $transactionManager;
    private AccountRepositoryContract&MockObject $accountRepository;

    public function setUp(): void
    {
        $this->eventPublisher = $this->createMock(EventPublisherContract::class);
        $this->paymentAuthorizer = $this->createMock(PaymentAuthorizerContract::class);
        $this->paymentRepository = $this->createMock(PaymentRepositoryContract::class);
        $this->transactionManager = new class implements TransactionManagerContract {
            public function run(callable $cb): void
            {
                $cb();
            }
        };
        $this->accountRepository = $this->createMock(AccountRepositoryContract::class);
    }

    public function testSuccessfulTransfer(): void
    {
        // Arrange
        $action = new TransferAction(
            $this->eventPublisher,
            $this->paymentAuthorizer,
            $this->paymentRepository,
            $this->transactionManager,
            $this->accountRepository,
        );

        $payerId = 1;
        $payeeId = 2;
        $amount  = new Amount(50);

        $taxId = new TaxId('12345678900');
        $payer = new Account(
            10,
            $taxId,
            AccountType::Regular,
            new Amount(100),
        );
        $payee = new Account(
            20,
            $taxId,
            AccountType::Regular,
            new Amount(20),
        );

        $this->accountRepository
            ->method('findByIdForUpdateOrFail')
            ->willReturnMap([
                [$payerId, $payer],
                [$payeeId, $payee],
            ]);

        $this->paymentAuthorizer
            ->expects($this->once())
            ->method('authorize')
            ->with($this->isInstanceOf(Payment::class));

        $matcher = $this->exactly(2);
        $this->accountRepository
            ->expects($matcher)
            ->method('save')
            ->willReturnCallback(function (Account $account) use ($matcher, $payer, $payee) {
                match ($matcher->numberOfInvocations()) {
                    1 => $this->assertSame($payer, $account),
                    2 => $this->assertSame($payee, $account),
                };
                return $account;
            });

        $this->paymentRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Payment::class));

        $this->eventPublisher
            ->expects($this->once())
            ->method('publishTransferCreated')
            ->with($this->isInstanceOf(Payment::class));

        // Act
        $action($payerId, $payeeId, $amount);

        // Assert
        $this->assertEquals(50, $payer->getBalance()->value);
        $this->assertEquals(70, $payee->getBalance()->value);
    }

    public function testShopkeeperPayerThrowsException(): void
    {
        $action = new TransferAction(
            $this->eventPublisher,
            $this->paymentAuthorizer,
            $this->paymentRepository,
            $this->transactionManager,
            $this->accountRepository,
        );

        $payerId = 1;
        $payeeId = 2;
        $amount  = new Amount(30);

        $taxId = new TaxId('12345678900');
        $payer = $this->createMock(Account::class);
        $payer->method('isShopkeeper')->willReturn(true);
        $payee = new Account(
            10,
            $taxId,
            AccountType::Regular,
            new Amount(100),
        );

        $this->accountRepository
            ->expects($this->exactly(2))
            ->method('findByIdForUpdateOrFail')
            ->willReturnMap([
                [$payerId, $payer],
                [$payeeId, $payee],
            ]);

        $this->expectException(InvalidPayerException::class);

        $action($payerId, $payeeId, $amount);
    }

    public function testTransferToSelfThrowsException(): void
    {
        $action = new TransferAction(
            $this->eventPublisher,
            $this->paymentAuthorizer,
            $this->paymentRepository,
            $this->transactionManager,
            $this->accountRepository,
        );

        $payerId = 1;
        $amount  = new Amount(30);

        $taxId = new TaxId('12345678900');
        $payer = new Account(
            10,
            $taxId,
            AccountType::Regular,
            new Amount(100),
        );

        $this->accountRepository
            ->expects($this->exactly(2))
            ->method('findByIdForUpdateOrFail')
            ->willReturn($payer);

        $this->expectException(InvalidPayerException::class);

        $action($payerId, $payerId, $amount);
    }
}
