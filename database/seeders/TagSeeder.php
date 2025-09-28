<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tag::insert([
            [
                'name' => 'Technology',
                'slug' => 'technology',
                'description' => 'All tech-related news',
            ],
            [
                'name' => 'Health',
                'slug' => 'health',
                'description' => 'Health news and updates',
            ],
            [
                'name' => 'Finance',
                'slug' => 'finance',
                'description' => 'Business and finance news',
            ],
        ]);
    }
}
