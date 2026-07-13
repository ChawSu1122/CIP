<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\RegistrationStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TokenRegisterController extends Controller
{
    public function __construct(private RegistrationStorageService $storageService)
    {
        $this->middleware('guest');
    }

    public function showForm(): View
    {
        return view('auth.token-register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'auth_method' => 'token',
        ]);

        $user->createApiToken();
        $metric = $this->storageService->recordTokenRegistration($user);

        return redirect()->route('dashboard.storage')->with('registration_result', [
            'name' => $user->name,
            'auth_method' => 'token',
            'total_kb' => $metric->total_kb,
            'user_row_kb' => round($metric->user_row_bytes / 1024, 4),
            'session_row_kb' => 0,
            'token_kb' => round($metric->token_bytes / 1024, 4),
        ]);
    }
}
