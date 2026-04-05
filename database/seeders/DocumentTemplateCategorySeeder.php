<?php

namespace Database\Seeders;

use App\Models\DocumentTemplateCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentTemplateCategorySeeder extends Seeder
{
    const CATEGORIES = [
        ['id' => 1, 'name' => 'Clínica', 'color' => 'bg-blue-50 text-blue-600', 'icon' => 'Stethoscope'],
        ['id' => 2, 'name' => 'Cirurgia', 'color' => 'bg-red-50 text-red-600', 'icon' => 'Slice'],
        ['id' => 3, 'name' => 'Diagnóstico', 'color' => 'bg-purple-50 text-purple-600', 'icon' => 'TestTubeDiagonal '],
        ['id' => 4, 'name' => 'Pediátrica', 'color' => 'bg-pink-50 text-pink-600', 'icon' => 'Baby'],
        ['id' => 5, 'name' => 'Saúde Mental', 'color' => 'bg-indigo-50 text-indigo-600', 'icon' => 'Brain'],
        ['id' => 6, 'name' => 'Reabilitação', 'color' => 'bg-green-50 text-green-600', 'icon' => 'Activity']
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            DocumentTemplateCategory::updateOrCreate(
                ['id' => $category['id']],
                [
                    'name' => $category['name'],
                    'color' => $category['color'],
                    'icon' => $category['icon']
                ]
            );
        }
    }
}
