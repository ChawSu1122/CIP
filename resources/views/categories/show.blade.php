@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h1 class="display-6 fw-bold">{{ $category->name }}</h1>
            <p class="text-muted mb-0">Posts and discussions tagged under this topic.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">View All Topics</a>
        </div>
    </div>

    <div class="row g-4">
        @foreach($posts as $post)
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="row g-0 align-items-center">
                        <div class="col-md-4">
                            <img src="{{ $post->feature_image }}" class="img-fluid rounded-start w-100" style="height: 220px; object-fit: contain; object-position: center; background: #f8f9fa;" alt="Featured image">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h2 class="h5"><a href="{{ route('posts.show', $post) }}" class="text-decoration-none text-dark">{{ $post->title }}</a></h2>
                                <div class="text-muted mb-3">
                                    <small>Posted by {{ $post->user->name }} · {{ $post->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-3 text-muted">{{ Str::limit($post->body, 200) }}</p>
                                <a href="{{ route('posts.show', $post) }}" class="btn btn-sm btn-primary">Read More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $posts->links() }}
    </div>
</div>
@endsection
