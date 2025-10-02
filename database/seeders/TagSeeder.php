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
        Tag::insert(
            [
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
                [
                    'name' => 'Politics',
                    'slug' => 'politics',
                    'description' => 'Political news and current affairs',
                ],
                [
                    'name' => 'Education',
                    'slug' => 'education',
                    'description' => 'News about schools, universities, and learning',
                ],
                [
                    'name' => 'Agriculture',
                    'slug' => 'agriculture',
                    'description' => 'Farming and agricultural news',
                ],
                [
                    'name' => 'Environment',
                    'slug' => 'environment',
                    'description' => 'Environmental news and updates',
                ],
                [
                    'name' => 'Sports',
                    'slug' => 'sports',
                    'description' => 'Sports news and events',
                ],
                [
                    'name' => 'Culture & Lifestyle',
                    'slug' => 'culture-lifestyle',
                    'description' => 'Culture, entertainment, and lifestyle news',
                ],
            ]

        );
    }
}
