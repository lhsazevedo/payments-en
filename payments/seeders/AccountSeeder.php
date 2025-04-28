<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Hyperf\Database\Seeders\Seeder;
use Hyperf\DbConnection\Db;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = CarbonImmutable::now()->format('Y-m-d H:i:s.v');

        Db::table('accounts')->insert([
            [
                'user_id' => 10,
                'tax_id' => '12345678909',
                'type' => 0,
                'balance' => 10000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => 20,
                'tax_id' => '99999999999',
                'type' => 0,
                'balance' => 2000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => 30,
                'tax_id' => '99999999999962',
                'type' => 1,
                'balance' => 100000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
