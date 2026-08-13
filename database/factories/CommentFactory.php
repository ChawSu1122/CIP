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
            'body' => $this->generateCommentBody(),
            'user_id' => User::factory(),
            'post_id' => Post::inRandomOrder()->value('id') ?: Post::factory()->create()->id,
        ];
    }

    protected function generateCommentBody(): string
    {
        $bodyTemplates = [
            'You can check it in Yangon, especially around the Bogyoke market area if you want to inspect it in person. It helps to meet in a public place and check the screen, battery, and ports before paying.',
            'Ask the seller to show the battery cycles and run a quick stress test before you pay. That gives you a much better idea of whether the machine is still healthy.',
            'This sounds like a decent buy, but please verify the serial number and invoice first. For used hardware, documentation is just as important as the specs.',
            'If the price is too low for an RTX laptop, be careful and compare it with other listings in the group. A good price should still match the hardware quality and age.',
            'For engineering software, 16GB RAM is the minimum I would recommend; anything less may feel slow. If you plan to run CAD or heavy apps, I would still lean toward 32GB.',
            'I recently bought a similar machine in Yangon; I suggest meeting at a cafe with good lighting and checking the screen closely. Good lighting makes it easier to spot dead pixels or panel issues.',
            'Check whether the SSD is already installed and ask for the original charger to avoid compatibility problems. Accessories are often a sign of how well the seller maintained the device.',
            'If you want to sell it fast, mention the warranty status and any accessories included. Clear details and photos usually generate more trust and faster replies.',
            'A safe local pickup spot would be a mall or a well-known office area rather than a random apartment. Public places make it easier to inspect the device and feel safer during the meetup.',
            'Agree with the others: if the seller cannot provide proof, move to the next listing. It is better to wait for a trustworthy listing than pay too quickly.',
        ];

        return fake()->randomElement($bodyTemplates);
    }
}
