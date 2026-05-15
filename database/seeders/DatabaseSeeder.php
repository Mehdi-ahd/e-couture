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
        $this->call([
            UsersTableSeeder::class,
            TypeVetementsTableSeeder::class,
            ModeleVetementsTableSeeder::class,
            TypeMesuresTableSeeder::class,
            ClientsTableSeeder::class,
            FichesMesuresTableSeeder::class,
            CommandeVetementsTableSeeder::class,
        ]);

        $admin->syncRoles([User::ROLE_ADMINISTRATEUR]);
    }
}
