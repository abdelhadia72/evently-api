<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Music',
                'description' => 'Concerts, festivals, and other music-related events',
            ],
            [
                'name' => 'Technology',
                'description' => 'Conferences, workshops, and hackathons',
            ],
            [
                'name' => 'Business',
                'description' => 'Networking, conferences, and workshops',
            ],
            [
                'name' => 'Health & Wellness',
                'description' => 'Yoga classes, fitness workshops, and wellness retreats',
            ],
            [
                'name' => 'Food & Drink',
                'description' => 'Food festivals, cooking classes, and wine tastings',
            ],
            [
                'name' => 'Arts & Culture',
                'description' => 'Art exhibitions, theater performances, and cultural festivals',
            ],
            [
                'name' => 'Sports',
                'description' => 'Sports events, tournaments, and races',
            ],
            [
                'name' => 'Education',
                'description' => 'Courses, workshops, and seminars',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
