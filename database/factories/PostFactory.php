<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement([
                'Looking for a laptop that can play games and run engineering apps',
                'Selling my office desktop with GTX 1660 and 32GB RAM',
                'Best place to buy a second-hand laptop in Yangon?',
                'Need advice: gaming laptop vs desktop for CAD work',
                'Selling a MacBook Air with warranty, what price is fair?',
                'What should I check before buying a used PC from a local seller?',
                'Laptop buyers: are you checking serial numbers and invoices?',
                'Help: I need a student laptop that can also handle light engineering tools',
                'Where to find a good deal on refurbished desktops for programming',
                'Community recommendation: safe meetup spots in Yangon for hardware pickup',
            ]),
            'body' => $this->generateCommunityBody(),
            'user_id' => User::factory(),
            'category_id' => Category::inRandomOrder()->value('id') ?: Category::factory(),
            'feature_image' => 'https://picsum.photos/800/600?random=' . fake()->unique()->numberBetween(1, 1000)
        ];
    }

    protected function generateCommunityBody(): string
    {
        $openers = [
            'I want to buy a laptop that will play games and also handle a little engineering work like AutoCAD and MATLAB.',
            'I am looking for a desktop that can support programming, video calls, and some 3D modeling without overheating.',
            'My budget is around 1.2 million kyats, and I want a machine that can do both gaming and school engineering projects.',
            'I need advice on whether to buy a used laptop with a good GPU or a new business laptop for daily office work.',
            'This is for a community member in Yangon who needs a reliable machine for remote classes and occasional gaming.',
        ];

        $details = [
            'The seller says the laptop has 16GB RAM, a 512GB SSD, and an RTX 3060, but I want to know if this is a fair price in our market.',
            'I would like to know what to check in person during a meet-up, especially for the battery health and keyboard condition.',
            'Can someone suggest a safe place in Yangon to inspect the computer before paying? I prefer a spot near Thamada or Yankin.',
            'If I choose a desktop, should I look for an Intel or AMD processor for both gaming and engineering applications?',
            'I am also concerned about warranties, so I want to ask the seller for a purchase receipt and serial number before agreeing.',
        ];

        $askers = [
            'Any tips on avoiding fake listings and hidden fees would be really helpful.',
            'Please share if you have bought similar hardware nearby and how the seller treated you.',
            'I am open to buying refurbished if the seller provides a good return policy or verified proof.',
            'I don’t want to miss a good deal, but I also don’t want to pay too much for an old laptop.',
            'Thanks in advance for your recommendations and local experiences in the Yangon market.',
        ];

        return implode("\n\n", [
            fake()->randomElement($openers),
            fake()->randomElement($details),
            fake()->randomElement($askers),
        ]);
    }
}
