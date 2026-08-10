@extends('dashboard.layout')

@section('title', 'Revocation Latency')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Revocation Latency</h1>
        <p class="page-copy">This page compares attack flows side by side, highlighting how token and session hijacking behave when revocation latency matters.</p>
    </div>

    <div class="row row-cols-1 row-cols-lg-2 gx-4 gy-4">
        <div class="col">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white border-0">
                    <h2 class="h5 mb-1">Token Hijacking Attack</h2>
                    {{-- <p class="mb-0 small opacity-75">A victim's bearer token is captured and replayed to access protected resources.</p> --}}
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item py-3">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <span class="text-secondary">Browser A (victim)</span>
                            <span class="fw-semibold">Alice is logged in and clicks the phishing link.</span>
                        </div>
                    </li>
                    <li class="list-group-item py-3">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <span class="text-secondary">Captured token</span>
                            <span class="fw-semibold text-break">eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...</span>
                        </div>
                    </li>
                    <li class="list-group-item py-3">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <span class="text-secondary">Authentication Method</span>
                            <span class="fw-semibold">Token-Based</span>
                        </div>
                    </li>
                    <li class="list-group-item py-3">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <span class="text-secondary">Time</span>
                            <span class="fw-semibold">2026-08-10 15:12:58</span>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    <button type="button" class="btn btn-primary ">Unauthorized Access</button>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-danger text-white border-0">
                    <h2 class="h5 mb-1">Session Hijacking Attack</h2>
                    {{-- <p class="mb-0 small opacity-75">An attacker steals a valid session ID and reuses it to impersonate the victim.</p> --}}
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item py-3">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <span class="text-secondary">Browser A (victim)</span>
                            <span class="fw-semibold">Bob is logged in and clicks the phishing link.</span>
                        </div>
                    </li>
                    <li class="list-group-item py-3">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <span class="text-secondary">Captured session ID</span>
                            <span class="fw-semibold text-break">wKY8ZrNJhbxyGsZNbwLE0VhUMUkBJClr1kCg312</span>
                        </div>
                    </li>
                    <li class="list-group-item py-3">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <span class="text-secondary">Authentication Method</span>
                            <span class="fw-semibold">Session-Based</span>
                        </div>
                    </li>
                    <li class="list-group-item py-3">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <span class="text-secondary">Time</span>
                            <span class="fw-semibold">2026-08-10 15:12:58</span>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    <button type="button" class="btn btn-danger">Unauthorized Access</button>
                </div>
            </div>
        </div>
    </div>
@endsection
