<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = env('STORE_SEED_ROWS', 100000);
        $chunks = 1000;

        for ($i = 0; $i < $rows / $chunks; $i++) {
            Store::factory()->count($chunks)->create();
        }

        $this->command->info("Successfully seeded {$rows} rows.");
    }
}
