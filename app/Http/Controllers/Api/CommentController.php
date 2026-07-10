<?php

namespace App\Http\Controllers\Api;

use App\Models\Comment;
use App\Models\Post;
use App\Services\MetricRecorder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommentController extends ApiController
{
    public function store(Request $request, Post $post): JsonResponse
    {
        $start = MetricRecorder::start();

        $user = $this->authenticate($request);

        if (! $user) {
            return $this->unauthorizedResponse();
        }

        $validated = $request->validate([
            'body' => 'required',
        ]);

        $comment = $post->comments()->create([
            'body' => $validated['body'],
            'user_id' => $user->id,
        ]);

        $result = MetricRecorder::finish($start);
        MetricRecorder::log('token', 'create_comment', $request, $result['duration_ms'], $result['memory_usage'], $result['query_count']);

        return response()->json($comment->load('user'), 201);
    }

    public function update(Request $request, Comment $comment): JsonResponse
    {
        $user = $this->authenticate($request);

        if (! $user) {
            return $this->unauthorizedResponse();
        }

        if ($user->id !== $comment->user_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'body' => 'required',
        ]);

        $comment->update($validated);

        return response()->json($comment->load('user'));
    }

    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        $user = $this->authenticate($request);

        if (! $user) {
            return $this->unauthorizedResponse();
        }

        if ($user->id !== $comment->user_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully.']);
    }
}
