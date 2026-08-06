<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
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

        Comment::factory()->create([
            'body' => 'Win an iPhone 17 Today! Click here to claim your prize. <a href="/phish" class="btn btn-sm btn-primary">Click Here</a>',
            'user_id' => 2,
            'post_id' => 1,
        ]);

        // Create comments
        Comment::factory(30)->create();

        // ensure at least one post exists (created by PostFactory or create it now)
        // $post = \App\Models\Post::first() ?? \App\Models\Post::factory()->create();

        // add a sample comment to the first post (uses CommentFactory)
        
    }
}
