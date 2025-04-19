<?php

declare(strict_types=1);

namespace App\Respository;

use Carbon\CarbonImmutable;
use Hyperf\DbConnection\Db;
use App\Domain\Payment;
use App\Domain\Services\PaymentRepositoryContract;

/**
 * @SuppressWarnings("PHPMD.ShortVariable")
 */
class DbPaymentRepository implements PaymentRepositoryContract
{
    public function __construct(
        private Db $db,
    ) {}

    public function save(Payment $payment): Payment
    {
        $now = CarbonImmutable::now()->format('Y-m-d H:i:s.v');

        $data = [
            'payer_id' => $payment->payer->getId(),
            'payee_id' => $payment->payee->getId(),
            'amount' => $payment->amount->value,
            'updated_at' => $now,
        ];

        if ($payment->getId()) {
            $this->db
                ->table('payments')
                ->where('id', $payment->getId())
                ->update($data);
            return $payment;
        }

        $data['created_at'] = $now;
        $id = $this->db
            ->table('payments')
            ->insertGetId($data);
        $this->setId($payment, $id);
        return $payment;
    }

    private function setId(Payment $payment, int $id): void
    {
        $reflectionUser = new \ReflectionClass(Payment::class);
        $reflectionId = $reflectionUser->getProperty('id');
        $reflectionId->setValue($payment, $id);
    }
}
