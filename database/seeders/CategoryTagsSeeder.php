<?php

namespace Database\Seeders;

use App\Models\Categories;
use App\Models\Tags;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryTagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Design & Creative',
                'slug' => 'design-creative',
                'tags' => [
                    ['name' => 'UI Design', 'color' => '#8B5CF6'],
                    ['name' => 'UX Design', 'color' => '#7C3AED'],
                    ['name' => 'Typography', 'color' => '#6D28D9'],
                    ['name' => 'Color Palettes', 'color' => '#5B21B6'],
                    ['name' => 'Icons', 'color' => '#4C1D95'],
                    ['name' => 'Illustrations', 'color' => '#A78BFA'],
                    ['name' => 'Prototyping', 'color' => '#C4B5FD'],
                    ['name' => 'Graphic Design', 'color' => '#DDD6FE'],
                ],
            ],
            [
                'name' => 'Development & Tech',
                'slug' => 'development-tech',
                'tags' => [
                    ['name' => 'Frontend', 'color' => '#3B82F6'],
                    ['name' => 'Backend', 'color' => '#2563EB'],
                    ['name' => 'Full-Stack', 'color' => '#1D4ED8'],
                    ['name' => 'APIs', 'color' => '#1E40AF'],
                    ['name' => 'Documentation', 'color' => '#1E3A8A'],
                    ['name' => 'DevTools', 'color' => '#60A5FA'],
                    ['name' => 'Hosting', 'color' => '#93C5FD'],
                    ['name' => 'Version Control', 'color' => '#BFDBFE'],
                ],
            ],
            [
                'name' => 'Business & Productivity',
                'slug' => 'business-productivity',
                'tags' => [
                    ['name' => 'Project Management', 'color' => '#10B981'],
                    ['name' => 'CRM', 'color' => '#059669'],
                    ['name' => 'Collaboration', 'color' => '#047857'],
                    ['name' => 'Note-Taking', 'color' => '#065F46'],
                    ['name' => 'Time Tracking', 'color' => '#064E3B'],
                    ['name' => 'Automation', 'color' => '#34D399'],
                    ['name' => 'Spreadsheets', 'color' => '#6EE7B7'],
                ],
            ],
            [
                'name' => 'Marketing & SEO',
                'slug' => 'marketing-seo',
                'tags' => [
                    ['name' => 'SEO Tools', 'color' => '#F59E0B'],
                    ['name' => 'Analytics', 'color' => '#D97706'],
                    ['name' => 'Email Marketing', 'color' => '#B45309'],
                    ['name' => 'Social Media', 'color' => '#92400E'],
                    ['name' => 'Content Marketing', 'color' => '#78350F'],
                    ['name' => 'Advertising', 'color' => '#FBBF24'],
                    ['name' => 'Lead Generation', 'color' => '#FCD34D'],
                ],
            ],
            [
                'name' => 'E-commerce & Retail',
                'slug' => 'ecommerce-retail',
                'tags' => [
                    ['name' => 'Online Stores', 'color' => '#EF4444'],
                    ['name' => 'Payment Processing', 'color' => '#DC2626'],
                    ['name' => 'Inventory', 'color' => '#B91C1C'],
                    ['name' => 'Dropshipping', 'color' => '#991B1B'],
                    ['name' => 'Marketplace', 'color' => '#7F1D1D'],
                    ['name' => 'Shopping Cart', 'color' => '#F87171'],
                ],
            ],
            [
                'name' => 'Education & Learning',
                'slug' => 'education-learning',
                'tags' => [
                    ['name' => 'Online Courses', 'color' => '#8B5CF6'],
                    ['name' => 'Tutorials', 'color' => '#7C3AED'],
                    ['name' => 'Documentation', 'color' => '#6D28D9'],
                    ['name' => 'Coding Bootcamps', 'color' => '#5B21B6'],
                    ['name' => 'Skill Building', 'color' => '#4C1D95'],
                    ['name' => 'Research', 'color' => '#A78BFA'],
                ],
            ],
            [
                'name' => 'AI & Machine Learning',
                'slug' => 'ai-machine-learning',
                'tags' => [
                    ['name' => 'AI Tools', 'color' => '#EC4899'],
                    ['name' => 'Chatbots', 'color' => '#DB2777'],
                    ['name' => 'Image Generation', 'color' => '#BE185D'],
                    ['name' => 'Text Processing', 'color' => '#9D174D'],
                    ['name' => 'Data Science', 'color' => '#831843'],
                    ['name' => 'Automation', 'color' => '#F472B6'],
                ],
            ],
            [
                'name' => 'Finance & Banking',
                'slug' => 'finance-banking',
                'tags' => [
                    ['name' => 'Budgeting', 'color' => '#14B8A6'],
                    ['name' => 'Investing', 'color' => '#0D9488'],
                    ['name' => 'Cryptocurrency', 'color' => '#0F766E'],
                    ['name' => 'Banking', 'color' => '#115E59'],
                    ['name' => 'Accounting', 'color' => '#134E4A'],
                    ['name' => 'Payment Tools', 'color' => '#2DD4BF'],
                ],
            ],
            [
                'name' => 'Entertainment & Media',
                'slug' => 'entertainment-media',
                'tags' => [
                    ['name' => 'Streaming', 'color' => '#F97316'],
                    ['name' => 'Gaming', 'color' => '#EA580C'],
                    ['name' => 'News', 'color' => '#C2410C'],
                    ['name' => 'Music', 'color' => '#9A3412'],
                    ['name' => 'Video', 'color' => '#7C2D12'],
                    ['name' => 'Podcasts', 'color' => '#FB923C'],
                    ['name' => 'Social Networks', 'color' => '#FDBA74'],
                ],
            ],
            [
                'name' => 'Health & Wellness',
                'slug' => 'health-wellness',
                'tags' => [
                    ['name' => 'Fitness', 'color' => '#22C55E'],
                    ['name' => 'Meditation', 'color' => '#16A34A'],
                    ['name' => 'Nutrition', 'color' => '#15803D'],
                    ['name' => 'Medical', 'color' => '#166534'],
                    ['name' => 'Mental Health', 'color' => '#14532D'],
                    ['name' => 'Tracking', 'color' => '#4ADE80'],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = Categories::updateOrCreate(
                ['slug' => $categoryData['slug']],
                ['name' => $categoryData['name']]
            );

            foreach ($categoryData['tags'] as $tagData) {
                $tag = Tags::updateOrCreate(
                    ['slug' => Str::slug($tagData['name'])],
                    [
                        'name' => $tagData['name'],
                        'tag_color' => $tagData['color'],
                    ]
                );

                // Attach tag to category if not already attached
                if (!$category->tags()->where('tag_id', $tag->id)->exists()) {
                    $category->tags()->attach($tag->id);
                }
            }
        }
    }
}
