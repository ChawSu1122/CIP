<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\RegistrationStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SessionRegisterController extends Controller
{
    public function __construct(private RegistrationStorageService $storageService)
    {
        $this->middleware('guest');
    }

    public function showForm(): View
    {
        return view('auth.session-register');
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
            'auth_method' => 'session',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $metric = $this->storageService->recordSessionRegistration($user, $request);

        return redirect()->route('dashboard.storage')->with('registration_result', [
            'name' => $user->name,
            'auth_method' => 'session',
            'total_kb' => $metric->total_kb,
            'user_row_kb' => round($metric->user_row_bytes / 1024, 4),
            'session_row_kb' => round($metric->session_row_bytes / 1024, 4),
            'token_kb' => 0,
        ]);
    }
}
