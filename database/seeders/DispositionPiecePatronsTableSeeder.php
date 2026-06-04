<?php

namespace Database\Seeders;

use App\Models\DispositionPiecePatron;
use App\Models\FormeDecoupe;
use App\Models\Materiau;
use App\Models\PiecePatron;
use Illuminate\Database\Seeder;

class DispositionPiecePatronsTableSeeder extends Seeder
{
    public function run(): void
    {
        $shapes = FormeDecoupe::query()->pluck('id', 'nom');
        $materials = Materiau::query()->pluck('id', 'nom');
        $pieces = PiecePatron::query()->with('patron.modeleVetement')->get();

        foreach ($pieces as $index => $piece) {
            $shapeName = str_contains(strtolower($piece->nom), 'col')
                ? 'Encolure ronde'
                : (str_contains(strtolower($piece->nom), 'manche')
                    ? 'Manche montée'
                    : (str_contains(strtolower($piece->nom), 'ceinture')
                        ? 'Coupe cintrée'
                        : 'Coupe droite'));

            $materialName = match ($piece->patron?->modeleVetement?->nom) {
                'Boubou homme Porto-Novo' => 'Bazin riche bleu nuit',
                'Chemise africaine col mao' => 'Popeline blanche',
                'Tenue sortie de mairie' => 'Dentelle guipure ivoire',
                default => 'Wax hollandais',
            };

            DispositionPiecePatron::query()->updateOrCreate(
                [
                    'piece_patron_id' => $piece->id,
                    'ordre' => 1,
                ],
                [
                    'forme_decoupe_id' => $shapes[$shapeName] ?? $shapes['Coupe droite'] ?? null,
                    'materiau_id' => $materials[$materialName] ?? null,
                    'position_x' => 10 + ($index % 5) * 4,
                    'position_y' => 8 + ($index % 3) * 6,
                    'rotation' => 0,
                    'echelle' => 1,
                ],
            );
        }
    }
}
