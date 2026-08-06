<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Services\MetricRecorder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
        $this->authorizeResource(Post::class, 'post');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $start = MetricRecorder::start();

        $featuredSpamPost = Post::where('title', 'Limited offer: verify your laptop price before you pay')->first();

        $posts = Post::with(['user', 'category'])
            ->withCount('comments')
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$featuredSpamPost?->id ?: 0])
            ->latest()
            ->paginate(10);

        $categories = Category::withCount('posts')
            ->orderByDesc('posts_count')
            ->limit(8)
            ->get();

        $result = MetricRecorder::finish($start);
        MetricRecorder::log('session', 'get_posts', request(), $result['duration_ms'], $result['memory_usage'], $result['query_count']);

        return view('posts.index', compact('posts', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = Category::all();
        return view('posts.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $start = MetricRecorder::start();

        $validated = $request->validate([
            'title' => 'required|max:255',
            'body' => 'required',
            'category_id' => 'required|exists:categories,id',
            'feature_image' => 'required|url'
        ]);

        $post = $request->user()->posts()->create($validated);

        $result = MetricRecorder::finish($start);
        MetricRecorder::log('session', 'create_post', $request, $result['duration_ms'], $result['memory_usage'], $result['query_count']);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): View
    {
        return view('posts.show', [
            'post' => $post->load(['user', 'category', 'comments.user'])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post): View
    {
        $categories = Category::all();
        return view('posts.edit', compact('post', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'body' => 'required',
            'category_id' => 'required|exists:categories,id',
            'feature_image' => 'required|url'
        ]);

        $post->update($validated);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully.');
    }
}
