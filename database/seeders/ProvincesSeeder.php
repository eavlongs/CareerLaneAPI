<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvincesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = [
            ['name' => 'Banteay Meanchey'],
            ['name' => 'Battambang'],
            ['name' => 'Kampong Cham'],
            ['name' => 'Kampong Chhnang'],
            ['name' => 'Kampong Speu'],
            ['name' => 'Kampong Thom'],
            ['name' => 'Kampot'],
            ['name' => 'Kandal'],
            ['name' => 'Kep'],
            ['name' => 'Koh Kong'],
            ['name' => 'Kratié'],
            ['name' => 'Mondulkiri'],
            ['name' => 'Oddar Meanchey'],
            ['name' => 'Pailin'],
            ['name' => 'Phnom Penh'],  // Capital City
            ['name' => 'Preah Sihanouk'],
            ['name' => 'Preah Vihear'],
            ['name' => 'Prey Veng'],
            ['name' => 'Pursat'],
            ['name' => 'Ratanakiri'],
            ['name' => 'Siem Reap'],
            ['name' => 'Stung Treng'],
            ['name' => 'Svay Rieng'],
            ['name' => 'Takéo'],
            ['name' => 'Tboung Khmum'],
        ];

        DB::table('provinces')->insert($provinces);
    }
}
