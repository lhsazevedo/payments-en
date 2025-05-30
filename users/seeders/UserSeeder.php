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
                'id' => 10,
                'name' => 'Alice',
                'mobile_number' => '21987654321',
                'email' => 'alice@exemplo.com.br',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 20,
                'name' => 'Bob',
                'mobile_number' => '21912345678',
                'email' => 'bob@exemplo.com.br',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 30,
                'name' => 'ACME Store',
                'mobile_number' => '21912341234',
                'email' => 'acme.store@exemplo.com.br',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
