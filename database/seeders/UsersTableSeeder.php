<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure roles exist
        User::ensureRole(User::ROLE_ADMINISTRATEUR);
        User::ensureRole(User::ROLE_COUTURIER);

        // Administrator
        $admin = User::factory()->create([
            'nom' => 'Adjovi',
            'prenom' => 'Kossi',
            'telephone' => '+22990000001',
            'email' => 'admin@koda.bj',
        ]);
        $admin->syncRoles([User::ROLE_ADMINISTRATEUR]);

        // Prestataires (couturiers) — example Beninese names
        $prestataires = [
            ['nom' => 'Gnonlonfoun', 'prenom' => 'Emilie', 'telephone' => '+22990000002', 'email' => 'nolanpatinde06@gmail.com'],
            ['nom' => 'Houngbédji', 'prenom' => 'Arnaud', 'telephone' => '+22990000003', 'email' => 'arnaud.h@koda.bj'],
            ['nom' => 'Mensah', 'prenom' => 'Fati', 'telephone' => '+22990000004', 'email' => 'fati.m@koda.bj'],
            ['nom' => 'Ahouansou', 'prenom' => 'Yves', 'telephone' => '+22990000005', 'email' => 'yves.a@koda.bj'],
        ];

        foreach ($prestataires as $p) {
            $user = User::factory()->create([
                'nom' => $p['nom'],
                'prenom' => $p['prenom'],
                'telephone' => $p['telephone'],
                'email' => $p['email'],
                'password' => bcrypt('password'), // Default password for all prestataires
            ]);

            $user->syncRoles([User::ROLE_COUTURIER]);
        }
    }
}
