<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            TypeMesuresTableSeeder::class,
            TypeVetementSeeder::class,
            ModeleVetementSeeder::class,
            TypeVetementMesureSeeder::class,
            ClientsTableSeeder::class,
            FichesMesuresTableSeeder::class,
            CommandeVetementsTableSeeder::class,
        ]);
    }
}
