<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Notification\TransferReceivedNotification;
use App\Domain\Notification\TransferSentNotification;
use App\Domain\Services\EmailGatewayContract;
use App\Domain\Services\SmsGatewayContract;
use App\Domain\Services\UserServiceContract;

class NotifyAction
{
    public function __construct(
        private UserServiceContract $userService,
        private SmsGatewayContract $smsGateway,
        private EmailGatewayContract $emailGateway,
    )
    {
    }

    public function __invoke(PaymentEvent $paymentEvent)
    {
        $payer = $this->userService->fetchById($paymentEvent->payerUserId);
        $payee = $this->userService->fetchById($paymentEvent->payeeUserId);
 
        $payeeNotification = new TransferReceivedNotification(
            $payer, $paymentEvent->amount
        );
        $this->smsGateway->send($payee->mobileNumber, $payeeNotification->asSms());
        $this->emailGateway->send($payee->email, $payeeNotification->asEmail());

        $payerNotification = new TransferSentNotification(
            $payee, $paymentEvent->amount
        );
        $this->smsGateway->send($payer->mobileNumber, $payerNotification->asSms());
        $this->emailGateway->send($payer->email, $payerNotification->asEmail());
    }
}
