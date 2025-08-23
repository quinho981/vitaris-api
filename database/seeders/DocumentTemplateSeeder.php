<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentTemplateSeeder extends Seeder
{
    const TEMPLATES = [
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
        foreach (self::TEMPLATES as $type) {
            DocumentTemplate::updateOrCreate(
                ['id' => $type['id']],
                ['name' => $type['name']]
            );
        }
    }
}
