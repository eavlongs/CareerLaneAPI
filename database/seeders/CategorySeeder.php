<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ["name" => "Software Development"],
            ["name" => "Data Science"],
            ["name" => "Project Management"],
            ["name" => "Marketing"],
            ["name" => "Sales"],
            ["name" => "Customer Support"],
            ["name" => "Human Resources"],
            ["name" => "Finance"],
            ["name" => "Operations"],
            ["name" => "Design"]
        ];

        DB::table("categories")->insert($categories);
    }
}
