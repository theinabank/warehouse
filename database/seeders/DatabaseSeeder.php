<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory(1)->create();

        $token = $user->first()->createToken(
            'api-seeder-token'
        )->plainTextToken;

        $this->command->info('API TOKEN (save this, it will not be shown again):');
        $this->command->line($token);

        $this->call([
            ProductSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
