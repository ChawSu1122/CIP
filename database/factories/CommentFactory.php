<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'body' => fake()->randomElement([
                'You can check it in Yangon, especially around the Bogyoke market area if you want to inspect it in person.',
                'Ask the seller to show the battery cycles and run a quick stress test before you pay.',
                'This sounds like a decent buy, but please verify the serial number and invoice first.',
                'If the price is too low for an RTX laptop, be careful and compare it with other listings in the group.',
                'For engineering software, 16GB RAM is the minimum I would recommend; anything less may feel slow.',
                'I recently bought a similar machine in Yangon; I suggest meeting at a cafe with good lighting and checking the screen closely.',
                'Check whether the SSD is already installed and ask for the original charger to avoid compatibility problems.',
                'If you want to sell it fast, mention the warranty status and any accessories included.',
                'A safe local pickup spot would be a mall or a well-known office area rather than a random apartment.',
                'Agree with the others: if the seller cannot provide proof, move to the next listing.',
            ]),
            'user_id' => User::factory(),
            'post_id' => Post::inRandomOrder()->value('id') ?: Post::factory()->create()->id,
        ];
    }
}
