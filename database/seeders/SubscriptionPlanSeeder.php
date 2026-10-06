<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Basic access for new teachers.',
                'price' => 0,
                'duration_days' => 30,
                'application_limit' => 3,
                'is_featured' => false,
                'status' => true,
                'sort_order' => 1,
            ],

            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Affordable monthly plan for active teachers.',
                'price' => 99,
                'duration_days' => 30,
                'application_limit' => 15,
                'is_featured' => false,
                'status' => true,
                'sort_order' => 2,
            ],

            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Unlimited tuition applications and priority access.',
                'price' => 199,
                'duration_days' => 30,
                'application_limit' => null,
                'is_featured' => true,
                'status' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                [
                    'slug' => $plan['slug'],
                ],
                $plan
            );
        }
    }
}