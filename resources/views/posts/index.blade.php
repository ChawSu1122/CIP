@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Blog Posts</h2>
                @auth
                    <a href="{{ route('posts.create') }}" class="btn btn-primary">Create New Post</a>
                @endauth
            </div>

            <div class="row row-cols-1 row-cols-md-2 g-4">
                @foreach($posts as $post)
                    <div class="col">
                        <div class="card h-100">
                            <div class="position-relative">
                                <img src="{{ $post->feature_image }}" class="card-img-top" alt="Featured image" style="height: 200px; object-fit: cover;">
                                <div class="position-absolute bottom-0 start-0 w-100 p-2" style="background: rgba(0,0,0,0.6);">
                                    <a href="{{ route('categories.show', $post->category) }}" class="badge bg-primary text-decoration-none">
                                        {{ $post->category->name }}
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <h3 class="card-title h5">{{ $post->title }}</h3>
                                <div class="card-text text-muted mb-2">
                                    <small>
                                        Posted by {{ $post->user->name }}
                                        {{ $post->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <p class="card-text">{{ Str::limit($post->body, 150) }}</p>
                            </div>
                            <div class="card-footer bg-transparent border-top-0">
                                <a href="{{ route('posts.show', $post) }}" class="btn btn-primary btn-sm">Read More</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $posts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
