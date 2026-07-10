<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    protected function authenticate(Request $request): ?User
    {
        $authorization = $request->header('Authorization', '');

        if (!str_starts_with($authorization, 'Bearer ')) {
            return null;
        }

        $token = substr($authorization, 7);

        return User::where('api_token', $token)->first();
    }

    protected function unauthorizedResponse(): JsonResponse
    {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
