<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends ApiController
{
    public function index(): JsonResponse
    {
        return response()->json(Category::withCount('posts')->get());
    }

    public function show(Category $category): JsonResponse
    {
        $posts = $category->posts()->with(['user', 'category'])->latest()->paginate(10);

        return response()->json(["category" => $category, "posts" => $posts]);
    }
}
