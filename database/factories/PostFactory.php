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
        $title = fake()->randomElement([
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
        ]);

        return [
            'title' => $title,
            'body' => $this->generateCommunityBody(),
            'user_id' => User::factory(),
            'category_id' => Category::inRandomOrder()->value('id') ?: Category::factory(),
            'feature_image' => $this->nextUniqueTechImage(),
        ];
    }

    protected function nextUniqueTechImage(): string
    {
        static $index = 0;

        $images = [
            'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1545239351-1141bd82e8a6?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1556740749-887f6717d7e4?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1551818255-e6e10975bc17?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1562813733-b31f71025d54?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1591207779412-4513e874d9f5?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1571171637578-41bc2dd41cd2?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1616588589676-62b3bd4ff6d2?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1581092921461-eab62e97a6d0?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1580894894517-7c0d9f3e68c3?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1573164713714-d95e436ab8d6?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=1200&q=80',
        ];

        $image = $images[$index % count($images)];
        $index++;

        return $image;
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
