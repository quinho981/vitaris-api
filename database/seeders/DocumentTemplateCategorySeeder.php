<?php

namespace Database\Seeders;

use App\Models\DocumentTemplateCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentTemplateCategorySeeder extends Seeder
{
    const CATEGORIES = [
        ['id' => 1, 'name' => 'Clínica', 'color' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400', 'icon' => 'Stethoscope'],
        ['id' => 2, 'name' => 'Cirurgia', 'color' => 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400', 'icon' => 'Slice'],
        ['id' => 3, 'name' => 'Diagnóstico', 'color' => 'bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400', 'icon' => 'TestTubeDiagonal '],
        ['id' => 4, 'name' => 'Pediátrica', 'color' => 'bg-pink-50 text-pink-600 dark:bg-pink-500/10 dark:text-pink-400', 'icon' => 'Baby'],
        ['id' => 5, 'name' => 'Saúde Mental', 'color' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400', 'icon' => 'Brain'],
        ['id' => 6, 'name' => 'Reabilitação', 'color' => 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400', 'icon' => 'Activity']
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
