<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DocumentTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = database_path('data/categories.xlsx');

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $header = array_map('strtolower', array_shift($rows));

        foreach ($rows as $row) {
            $data = array_combine($header, $row);

            if (empty($data['id']) || empty($data['name'])) {
                continue;
            }

            DocumentTemplate::updateOrCreate(
                ['id' => $data['id']],
                [
                    'category_id' => $data['category_id'] ?? null,
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'content' => $data['content'] ?? null,
                ]
            );
        }
    }
}
