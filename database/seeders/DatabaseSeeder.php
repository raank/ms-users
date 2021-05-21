<?php

namespace Database\Seeders;

use App\Models\V1\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (env('APP_ENV') === 'local') {
            User::factory()
                ->count(20)
                ->create();
        }
    }
}
