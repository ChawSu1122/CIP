<?php

namespace App\Http\Controllers;

use App\Services\ThesisComparisonService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ThesisController extends Controller
{
    public function __construct(private ThesisComparisonService $comparisonService)
    {
    }

    public function questions(): View
    {
        return view('thesis.questions');
    }

    public function methodology(): View
    {
        return view('thesis.methodology');
    }

    public function experiment(): View
    {
        return view('thesis.experiment');
    }

    public function security(): View
    {
        return view('thesis.security');
    }

    public function complexity(): View
    {
        return view('thesis.complexity');
    }

    public function comparison(): View
    {
        return view('comparison');
    }

    public function data(): JsonResponse
    {
        return response()->json($this->comparisonService->getComparisonData());
    }
}
