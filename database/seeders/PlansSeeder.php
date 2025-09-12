<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    const PLANS = [
        [ 'id' => 1, 'name' => 'free' ],
        [ 'id' => 2, 'name' => 'pro' ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::PLANS as $type) {
            Plan::updateOrCreate(
                ['id' => $type['id']],
                ['name' => $type['name']]
            );
        }
    }
}
