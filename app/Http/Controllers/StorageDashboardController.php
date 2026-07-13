<?php

namespace App\Http\Controllers;

use App\Services\RegistrationStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class StorageDashboardController extends Controller
{
    public function __construct(private RegistrationStorageService $storageService)
    {
    }

    public function index(): View
    {
        return view('dashboard.storage', [
            'registrationResult' => session('registration_result'),
            'registrationComparison' => $this->storageService->getRegistrationComparison(),
        ]);
    }

    public function data(): JsonResponse
    {
        return response()->json($this->storageService->getRegistrationComparison());
    }
}
