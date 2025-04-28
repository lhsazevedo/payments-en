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
use App\Domain\Services\EventPublisherContract;
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
    private EventPublisherContract&MockObject $eventPublisher;
    private PaymentAuthorizerContract&MockObject $paymentAuthorizer;
    private PaymentRepositoryContract&MockObject $paymentRepository;
    private TransactionManagerContract $transactionManager;
    private UserRepositoryContract&MockObject $userRepository;

    public function setUp(): void
    {
        $this->eventPublisher = $this->createMock(EventPublisherContract::class);
        $this->paymentAuthorizer = $this->createMock(PaymentAuthorizerContract::class);
        $this->paymentRepository = $this->createMock(PaymentRepositoryContract::class);
        $this->transactionManager = new class() implements TransactionManagerContract {
            public function run (callable $cb): void {
                $cb();
            }
        };
        $this->userRepository = $this->createMock(UserRepositoryContract::class);
    }

    public function testSuccessfulTransfer(): void
    {
        // Arrange
        $action = new TransferAction(
            $this->eventPublisher,
            $this->paymentAuthorizer,
            $this->paymentRepository,
            $this->transactionManager,
            $this->userRepository,
        );

        $payerId = 1;
        $payeeId = 2;
        $amount  = new Amount(50);

        $taxId = new TaxId('12345678900');
        $payer = new User(
            'Payer',
            '21987654300',
            'payer@exemplo.com.br',
            $taxId,
            UserType::Regular,
            new Amount(100),
        );
        $payee = new User(
            'Payee',
            '21987654301',
            'payee@exemplo.com.br',
            $taxId,
            UserType::Regular,
            new Amount(20),
        );

        $this->userRepository
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
        $this->userRepository
            ->expects($matcher)
            ->method('save')
            ->willReturnCallback(function (User $user) use ($matcher, $payer, $payee) {
                match ($matcher->numberOfInvocations()) {
                    1 => $this->assertSame($payer, $user),
                    2 => $this->assertSame($payee, $user),
                };
                return $user;
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

        $this->userRepository
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

        $this->userRepository
            ->expects($this->exactly(2))
            ->method('findByIdForUpdateOrFail')
            ->willReturn($payer);

        $this->expectException(InvalidPayerException::class);

        $action($payerId, $payerId, $amount);
    }
}
