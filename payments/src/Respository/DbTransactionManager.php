<?php

declare(strict_types=1);

namespace App\Respository;

use Hyperf\DbConnection\Db;
use App\Domain\Services\TransactionManagerContract;

class DbTransactionManager implements TransactionManagerContract
{
    public function __construct(
        private Db $db,
    ) {}

    public function run(callable $cb): void
    {
        $this->db->beginTransaction();

        try {
            $cb();
        } catch (\Throwable $th) {
            $this->db->rollBack();

            throw $th;
        }

        $this->db->commit();
    }
}
