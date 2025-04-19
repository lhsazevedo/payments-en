<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Hyperf\Database\Seeders\Seeder;
use Hyperf\DbConnection\Db;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = CarbonImmutable::now()->format('Y-m-d H:i:s.v');

        Db::table('users')->insert([
            [
                'name' => 'Alice',
                'mobile_number' => '21987654321',
                'email' => 'alice@exemplo.com.br',
                'tax_id' => '12345678909',
                'type' => 0,
                'balance' => 10000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Bob',
                'mobile_number' => '21912345678',
                'email' => 'bob@exemplo.com.br',
                'tax_id' => '99999999999',
                'type' => 0,
                'balance' => 2000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'ACME Store',
                'mobile_number' => '21912341234',
                'email' => 'acme.store@exemplo.com.br',
                'tax_id' => '99999999999962',
                'type' => 1,
                'balance' => 100000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
