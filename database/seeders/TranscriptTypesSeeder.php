<?php

namespace Database\Seeders;

use App\Models\TranscriptType;
use Illuminate\Database\Seeder;

class TranscriptTypesSeeder extends Seeder
{
    const TYPES = [
        [ 'id' => 1, 'name' => 'Consulta geral' ],
        [ 'id' => 2, 'name' => 'Retorno' ],
        [ 'id' => 3, 'name' => 'Urgente' ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::TYPES as $type) {
            TranscriptType::updateOrCreate(
                ['id' => $type['id']],
                ['type' => $type['name']]
            );
        }
    }
}
