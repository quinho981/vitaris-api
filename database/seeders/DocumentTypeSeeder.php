<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    const TYPES = [
        [ 'id' => 1, 'name' => 'Cardiologia' ],
        [ 'id' => 2, 'name' => 'Ortopedia' ],
        [ 'id' => 3, 'name' => 'Neurologia' ],
        [ 'id' => 4, 'name' => 'Oftalmologia' ],
        [ 'id' => 5, 'name' => 'Clínica médica' ],
        [ 'id' => 6, 'name' => 'Pediatria' ]
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::TYPES as $type) {
            DocumentType::updateOrCreate(
                ['id' => $type['id']],
                ['type' => $type['name']]
            );
        }
    }
}
