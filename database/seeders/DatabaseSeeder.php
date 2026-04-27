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
        User::ensureRole(User::ROLE_ADMINISTRATEUR);
        User::ensureRole(User::ROLE_COUTURIER);
        User::ensureRole(User::ROLE_CLIENT);

        $admin = User::factory()->create([
            'nom' => 'User',
            'prenom' => 'Test',
            'telephone' => '+22990000000',
            'email' => 'test@example.com',
        ]);

        $admin->syncRoles([User::ROLE_ADMINISTRATEUR]);
    }
}
