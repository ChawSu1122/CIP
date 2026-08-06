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
                    <p class="lead mb-4">Congradulations!! I'll send iPhone 17 to you soon.</p>
                    <div class="d-flex justify-content-center">
                        <a href="{{ route('home') }}" class="btn btn-primary btn-lg px-5">Close</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection