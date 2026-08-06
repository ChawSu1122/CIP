<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Category;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create two specific users
        User::factory()->create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
        ]);

        User::factory()->create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
        ]);

        // Seed categories
        $this->call(CategorySeeder::class);

        // Create posts
        Post::factory(20)->create();

        // Add a featured marketplace post with a disguised verification link on the first page
        $featuredPost = Post::factory()->create([
            'title' => 'Limited offer: verify your laptop price before you pay',
            'body' => 'I found a lightly used gaming laptop with RTX 4070, 32GB RAM, and a 1TB SSD, but the seller is asking for a fast payment. Before you decide, check the full listing and warranty details carefully. If you want, use /phish to verify the secure listing and compare the offer against current market prices. This is a common trick in buying and selling groups, so it pays to stay cautious and ask for invoice proof.',
            'user_id' => User::first()->id,
            'category_id' => Category::where('name', 'Laptop Deals')->first()->id,
            'feature_image' => 'https://picsum.photos/800/600?random=901',
        ]);

        Comment::factory()->create([
            'body' => 'You can check it in Yangon, especially around Yankin or Thamada township if you want to inspect it in person.',
            'user_id' => User::find(2)->id,
            'post_id' => $featuredPost->id,
        ]);

        Comment::factory()->create([
            'body' => 'If the seller is asking for fast payment, ask for the invoice and serial number first. It helps avoid scams.',
            'user_id' => User::first()->id,
            'post_id' => $featuredPost->id,
        ]);

        Comment::factory()->create([
            'body' => 'You should verify the comparison before buying. Click <a href="/phish">here to compare the listing</a> and make sure the price matches what other sellers are asking in Yangon.',
            'user_id' => User::factory()->create(['name' => 'Aung Kyaw', 'email' => 'aungkyaw@example.com'])->id,
            'post_id' => $featuredPost->id,
        ]);

        // Create comments
        Comment::factory(30)->create();

        // ensure at least one post exists (created by PostFactory or create it now)
        // $post = \App\Models\Post::first() ?? \App\Models\Post::factory()->create();

        // add a sample comment to the first post (uses CommentFactory)
        
    }
}
