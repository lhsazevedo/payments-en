<?php

declare(strict_types=1);

namespace App\Respository;

use Carbon\CarbonImmutable;
use Hyperf\DbConnection\Db;
use App\Domain\Exception\EntityNotFoundException;
use App\Domain\Services\AccountRepositoryContract;
use App\Domain\Account;
use App\Domain\AccountType;
use App\Domain\ValueObject\Amount;
use App\Domain\ValueObject\TaxId;

/**
 * @SuppressWarnings("PHPMD.ShortVariable")
 */
class DbAccountRepository implements AccountRepositoryContract
{
    public function __construct(
        private Db $db,
    ) {}

    public function findByIdForUpdate(int $id): ?Account
    {
        /**
         * @var null|object{
         *   id: int,
         *   user_id: int,
         *   tax_id: string,
         *   type: int,
         *   balance: int,
         * }
         */
        $result = $this->db
            ->table('accounts')
            ->where('id', $id)
            ->lockForUpdate()
            ->first();

        if (! $result) {
            return null;
        }

        $account = new Account(
            $result->user_id,
            new TaxId($result->tax_id),
            AccountType::from($result->type),
            new Amount($result->balance),
        );
        $this->setId($account, $result->id);
        return $account;
    }

    public function findByIdForUpdateOrFail(int $id): Account
    {
        $account = $this->findByIdForUpdate($id);

        if (! $account) {
            throw new EntityNotFoundException();
        }

        return $account;
    }

    public function save(Account $account): Account
    {
        $now = CarbonImmutable::now()->format('Y-m-d H:i:s.v');

        $data = [
            'user_id' => $account->userId,
            'tax_id' => $account->taxId->value,
            'type' => $account->type,
            'balance' => $account->getBalance()->value,
            'updated_at' => $now,
        ];

        if ($account->getId()) {
            $this->db
                ->table('accounts')
                ->where('id', $account->getId())
                ->update($data);
            return $account;
        }

        $data['created_at'] = $now;
        $id = $this->db->table('accounts')->insertGetId($data);
        $this->setId($account, $id);
        return $account;
    }

    private function setId(Account $account, int $id): void
    {
        $reflectionAccount = new \ReflectionClass(Account::class);
        $reflectionId = $reflectionAccount->getProperty('id');
        $reflectionId->setValue($account, $id);
    }
}
