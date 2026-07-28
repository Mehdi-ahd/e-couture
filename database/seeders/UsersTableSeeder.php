<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        User::ensureRole(User::ROLE_ADMINISTRATEUR);
        User::ensureRole(User::ROLE_COUTURIER);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@koda.bj'],
            [
                'nom' => 'Adjovi',
                'prenom' => 'Kossi',
                'sexe' => 'homme',
                'telephone' => '+22990000001',
                'password' => Hash::make('password'),
                'est_actif' => true,
            ],
        );
        $admin->syncRoles([User::ROLE_ADMINISTRATEUR]);

        $prestataires = [
            ['nom' => 'Gnonlonfoun', 'prenom' => 'Emilie',  'sexe' => 'femme', 'telephone' => '+22990000002', 'email' => 'nolanpatinde06@gmail.com'],
            ['nom' => 'Houngbédji', 'prenom' => 'Arnaud',   'sexe' => 'homme', 'telephone' => '+22990000003', 'email' => 'arnaud.h@koda.bj'],
            ['nom' => 'Mensah',     'prenom' => 'Fati',     'sexe' => 'femme', 'telephone' => '+22990000004', 'email' => 'fati.m@koda.bj'],
            ['nom' => 'Ahouansou',  'prenom' => 'Yves',     'sexe' => 'homme', 'telephone' => '+22990000005', 'email' => 'yves.a@koda.bj'],
        ];

        foreach ($prestataires as $p) {
            $user = User::query()->updateOrCreate(
                ['email' => $p['email']],
                [
                    'nom' => $p['nom'],
                    'prenom' => $p['prenom'],
                    'sexe' => $p['sexe'],
                    'telephone' => $p['telephone'],
                    'password' => Hash::make('password'),
                    'est_actif' => true,
                ],
            );

            $user->syncRoles([User::ROLE_COUTURIER]);
        }
    }
}
