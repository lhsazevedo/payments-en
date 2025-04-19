<?php

declare(strict_types=1);

namespace App\Respository;

use Carbon\CarbonImmutable;
use Hyperf\DbConnection\Db;
use App\Domain\Exception\EntityNotFoundException;
use App\Domain\Services\UserRepositoryContract;
use App\Domain\User;
use App\Domain\UserType;
use App\Domain\ValueObject\Amount;
use App\Domain\ValueObject\TaxId;

/**
 * @SuppressWarnings("PHPMD.ShortVariable")
 */
class DbUserRepository implements UserRepositoryContract
{
    public function __construct(
        private Db $db,
    ) {}

    public function findByIdForUpdate(int $id): ?User
    {
        $result = $this->db
            ->table('users')
            ->where('id', $id)
            ->lockForUpdate()
            ->first();

        if (! $result) {
            return null;
        }

        $user = new User(
            $result->name,                 // @phpstan-ignore property.notFound
            $result->mobile_number,        // @phpstan-ignore property.notFound
            $result->email,                // @phpstan-ignore property.notFound
            new TaxId($result->tax_id),    // @phpstan-ignore property.notFound
            UserType::from($result->type), // @phpstan-ignore property.notFound
            new Amount($result->balance),  // @phpstan-ignore property.notFound
        );
        $this->setId($user, $result->id);  // @phpstan-ignore property.notFound
        return $user;
    }

    public function findByIdForUpdateOrFail(int $id): User
    {
        $user = $this->findByIdForUpdate($id);

        if (! $user) {
            throw new EntityNotFoundException();
        }

        return $user;
    }

    public function save(User $user): User
    {
        $now = CarbonImmutable::now()->format('Y-m-d H:i:s.v');

        $data = [
            'name' => $user->name,
            'mobile_number' => $user->mobileNumber,
            'email' => $user->email,
            'tax_id' => $user->taxId->value,
            'type' => $user->type,
            'balance' => $user->getBalance()->value,
            'updated_at' => $now,
        ];

        if ($user->getId()) {
            $this->db
                ->table('users')
                ->where('id', $user->getId())
                ->update($data);
            return $user;
        }

        $data['created_at'] = $now;
        $id = $this->db->table('users')->insertGetId($data);
        $this->setId($user, $id);
        return $user;
    }

    private function setId(User $user, int $id): void
    {
        $reflectionUser = new \ReflectionClass(User::class);
        $reflectionId = $reflectionUser->getProperty('id');
        $reflectionId->setValue($user, $id);
    }
}
