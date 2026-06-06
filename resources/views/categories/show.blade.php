@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>{{ $category->name }}</h2>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">All Categories</a>
            </div>

            @foreach($posts as $post)
                <div class="card mb-4">
                    <img src="{{ $post->feature_image }}" class="card-img-top" alt="Featured image">
                    <div class="card-body">
                        <h3 class="card-title">{{ $post->title }}</h3>
                        <div class="card-text text-muted mb-2">
                            <small>
                                Posted by {{ $post->user->name }}
                                {{ $post->created_at->diffForHumans() }}
                            </small>
                        </div>
                        <p class="card-text">{{ Str::limit($post->body, 200) }}</p>
                        <a href="{{ route('posts.show', $post) }}" class="btn btn-primary">Read More</a>
                    </div>
                </div>
            @endforeach

            <div class="d-flex justify-content-center">
                {{ $posts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
