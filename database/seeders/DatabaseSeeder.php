<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            TypeVetementsTableSeeder::class,
            TypeMesuresTableSeeder::class,
            FormesDecoupeTableSeeder::class,
            MateriauxTableSeeder::class,
            ModeleVetementsTableSeeder::class,
            PatronsTableSeeder::class,
            PiecePatronsTableSeeder::class,
            DispositionPiecePatronsTableSeeder::class,
            ClientsTableSeeder::class,
            FichesMesuresTableSeeder::class,
            CommandeVetementsTableSeeder::class,
        ]);
    }
}
