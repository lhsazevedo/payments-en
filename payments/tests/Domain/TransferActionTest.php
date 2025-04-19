<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Domain\TransferAction;
use App\Domain\Payment;
use App\Domain\User;
use App\Domain\Exception\InvalidPayerException;
use App\Domain\Notification\PaymentReceivedNotification;
use App\Domain\Services\Notification\NotificationServiceContract;
use App\Domain\Services\PaymentAuthorizerContract;
use App\Domain\Services\PaymentRepositoryContract;
use App\Domain\Services\TransactionManagerContract;
use App\Domain\Services\UserRepositoryContract;
use App\Domain\ValueObject\Amount;
use App\Domain\ValueObject\TaxId;
use App\Domain\UserType;

class TransferActionTest extends TestCase
{
    private NotificationServiceContract&MockObject $notificationService;
    private PaymentAuthorizerContract&MockObject $paymentAuthorizer;
    private PaymentRepositoryContract&MockObject $paymentRepository;
    private TransactionManagerContract&MockObject $transactionManager;
    private UserRepositoryContract&MockObject $userRepository;

    public function setUp(): void
    {
        $this->notificationService = $this->createMock(NotificationServiceContract::class);
        $this->paymentAuthorizer = $this->createMock(PaymentAuthorizerContract::class);
        $this->paymentRepository = $this->createMock(PaymentRepositoryContract::class);
        $this->transactionManager = $this->createMock(TransactionManagerContract::class);
        $this->userRepository = $this->createMock(UserRepositoryContract::class);
    }

    public function testSuccessfulTransfer(): void
    {
        $action = new TransferAction(
            $this->notificationService,
            $this->paymentAuthorizer,
            $this->paymentRepository,
            $this->transactionManager,
            $this->userRepository,
        );

        $payerId = 1;
        $payeeId = 2;
        $amount  = new Amount(50);

        $taxId = new TaxId('12345678900');
        $payer = new User('Payer', '21987654300', 'payer@exemplo.com.br', $taxId, UserType::Regular, new Amount(100));
        $payee = new User('Payee', '21987654301', 'payee@exemplo.com.br', $taxId, UserType::Regular, new Amount(20));

        // Mock repository to return payer/payee based on ID
        $this->userRepository
            ->expects($this->exactly(2))
            ->method('findByIdForUpdateOrFail')
            ->willReturnCallback(fn(int $id) => $id === $payerId ? $payer : $payee);

        // Execute transaction closure
        $this->transactionManager
            ->expects($this->once())
            ->method('run')
            ->willReturnCallback(fn(\Closure $callback) => $callback());

        // Authorize payment
        $this->paymentAuthorizer
            ->expects($this->once())
            ->method('authorize')
            ->with($this->isInstanceOf(Payment::class));

        // Save users: check order
        $saveMatcher = $this->exactly(2);
        $this->userRepository
            ->expects($saveMatcher)
            ->method('save')
            ->willReturnCallback(function (User $user) use ($saveMatcher, $payer, $payee) {
                match ($saveMatcher->numberOfInvocations()) {
                    1 => $this->assertSame($payer, $user),
                    2 => $this->assertSame($payee, $user),
                };
                return $user;
            });

        // Save payment
        $this->paymentRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Payment::class));

        // Notify payee
        $this->notificationService
            ->expects($this->once())
            ->method('notify')
            ->with($payee, $this->isInstanceOf(PaymentReceivedNotification::class));

        // Invoke action
        $action($payerId, $payeeId, $amount);

        // Assert balances
        $this->assertEquals(50, $payer->getBalance()->value);
        $this->assertEquals(70, $payee->getBalance()->value);
    }

    public function testShopkeeperPayerThrowsException(): void
    {
        $action = new TransferAction(
            $this->notificationService,
            $this->paymentAuthorizer,
            $this->paymentRepository,
            $this->transactionManager,
            $this->userRepository,
        );

        $payerId = 1;
        $payeeId = 2;
        $amount  = new Amount(30);

        $taxId = new TaxId('12345678900');
        $payer = $this->createMock(User::class);
        $payer->method('isShopkeeper')->willReturn(true);
        $payee = new User(
            'Payee',
            '21987654301',
            'payee@exemplo.com.br',
            $taxId,
            UserType::Regular,
            new Amount(100),
        );

        // Mock repository
        $this->userRepository
            ->expects($this->exactly(2))
            ->method('findByIdForUpdateOrFail')
            ->willReturnCallback(fn(int $id) => $id === $payerId ? $payer : $payee);

        // Transaction run
        $this->transactionManager
            ->expects($this->once())
            ->method('run')
            ->willReturnCallback(fn(\Closure $callback) => $callback());

        $this->expectException(InvalidPayerException::class);

        $action($payerId, $payeeId, $amount);
    }

    public function testTransferToSelfThrowsException(): void
    {
        $action = new TransferAction(
            $this->notificationService,
            $this->paymentAuthorizer,
            $this->paymentRepository,
            $this->transactionManager,
            $this->userRepository,
        );

        $payerId = 1;
        $amount  = new Amount(30);

        $taxId = new TaxId('12345678900');
        $payer = new User(
            'Payer',
            '21987654301',
            'payee@exemplo.com.br',
            $taxId,
            UserType::Regular,
            new Amount(100),
        );

        // Mock repository
        $this->userRepository
            ->expects($this->exactly(2))
            ->method('findByIdForUpdateOrFail')
            ->willReturnCallback(fn() => $payer);

        // Transaction run
        $this->transactionManager
            ->expects($this->once())
            ->method('run')
            ->willReturnCallback(fn(\Closure $callback) => $callback());

        $this->expectException(InvalidPayerException::class);

        $action($payerId, $payerId, $amount);
    }
}
