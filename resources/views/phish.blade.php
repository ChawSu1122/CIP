@extends('layouts.app')

@section('hideNavbar', true)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-dark text-white border-0 rounded-top-4">
                    <h1 class="h4 mb-0">Now You're winner</h1>
                </div>
                <div class="card-body">
                    <p class="lead mb-3">Congratulations!! I'll send iPhone 17 to you soon.</p>
                    <p class="small text-muted mb-4">This is a phishing simulation page. Since you clicked the suspicious link while logged in, your victim profile will be shown automatically in the Authentication Security Testing Module.</p>

                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item"><strong>Victim:</strong> {{ Auth::user()->name ?? 'Authenticated user' }}</li>
                        <li class="list-group-item"><strong>Email:</strong> {{ Auth::user()->email ?? 'unknown' }}</li>
                        <li class="list-group-item"><strong>Next step:</strong> Review the Authentication Security Testing Module to see the detected compromised credentials.</li>
                    </ul>

                    <div class="d-flex flex-column gap-3">
                        <a href="{{ route('thesis.replay', ['victim_id' => Auth::id(), 'victim_name' => Auth::user()->name, 'victim_email' => Auth::user()->email, 'attacker_id' => 53]) }}" class="btn btn-danger btn-lg px-4">Open Security Testing Module</a>
                        <a href="{{ route('home') }}" class="btn btn-secondary btn-lg px-4">Close</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection