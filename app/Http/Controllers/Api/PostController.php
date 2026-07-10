<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Services\MetricRecorder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PostController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $start = MetricRecorder::start();

        $posts = Post::with(['user', 'category'])
            ->latest()
            ->paginate(10);

        $result = MetricRecorder::finish($start);
        MetricRecorder::log('token', 'get_posts', $request, $result['duration_ms'], $result['memory_usage'], $result['query_count']);

        return response()->json($posts);
    }

    public function show(Post $post): JsonResponse
    {
        return response()->json($post->load(['user', 'category', 'comments.user']));
    }

    public function store(Request $request): JsonResponse
    {
        $start = MetricRecorder::start();

        $user = $this->authenticate($request);

        if (! $user) {
            return $this->unauthorizedResponse();
        }

        $validated = $request->validate([
            'title' => 'required|max:255',
            'body' => 'required',
            'category_id' => 'required|exists:categories,id',
            'feature_image' => 'required|url',
        ]);

        $post = $user->posts()->create($validated);

        $result = MetricRecorder::finish($start);
        MetricRecorder::log('token', 'create_post', $request, $result['duration_ms'], $result['memory_usage'], $result['query_count']);

        return response()->json($post->load(['user', 'category']), 201);
    }

    public function update(Request $request, Post $post): JsonResponse
    {
        $user = $this->authenticate($request);

        if (! $user) {
            return $this->unauthorizedResponse();
        }

        if ($user->id !== $post->user_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|max:255',
            'body' => 'required',
            'category_id' => 'required|exists:categories,id',
            'feature_image' => 'required|url',
        ]);

        $post->update($validated);

        return response()->json($post->load(['user', 'category']));
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        $user = $this->authenticate($request);

        if (! $user) {
            return $this->unauthorizedResponse();
        }

        if ($user->id !== $post->user_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully.']);
    }
}
