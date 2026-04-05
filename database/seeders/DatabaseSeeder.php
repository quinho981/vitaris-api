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
            DocumentTemplateSeeder::class,
            TranscriptTypesSeeder::class,
            PlansSeeder::class,
            DocumentTemplateCategorySeeder::class
        ]);
    }
}
