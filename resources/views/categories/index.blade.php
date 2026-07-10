@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-lg-8">
            <h1 class="display-6 fw-bold">Topics</h1>
            <p class="text-muted">Discover the active categories powering the community forum.</p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">Back to Feed</a>
        </div>
    </div>

    <div class="row g-4">
        @foreach($categories as $category)
            <div class="col-sm-6 col-xl-4">
                <a href="{{ route('categories.show', $category) }}" class="card h-100 text-decoration-none text-dark shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h2 class="h5 mb-0">{{ $category->name }}</h2>
                            <span class="badge bg-primary rounded-pill">{{ $category->posts_count }}</span>
                        </div>
                        <p class="text-muted mb-0">Browse posts and discussions tagged in this category.</p>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
