@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row align-items-end mb-4">
        <div class="col-md-8">
            <h1 class="display-6 fw-bold">Community Hub</h1>
            <p class="text-muted mb-0">An IT marketplace for desktops, laptops, buying/selling advice, and trusted community discussions.</p>
            <!-- <div class="alert alert-warning mt-3 py-2 px-3 small mb-0">
                <strong>Heads up:</strong> Always verify marketplace offers before you pay. Visit <a href="{{ route('phish') }}" class="alert-link">this verification page</a> for the sample listing check.
            </div> -->
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            @auth
                <a href="{{ route('posts.create') }}" class="btn btn-primary me-2 mb-2">New Discussion</a>
            @endauth
            <a href="{{ route('forum.features') }}" class="btn btn-outline-secondary mb-2">View Features</a>
        </div>
    </div>

    <div class="row gx-4">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h5 mb-1">Latest Marketplace Posts</h2>
                    <p class="text-muted mb-0">Browse the most recent IT listings, hardware advice, and buyer-seller conversations.</p>
                </div>
                <span class="text-muted">{{ $posts->total() }} posts</span>
            </div>

            @foreach($posts as $post)
                <div class="card shadow-sm mb-4 border-0">
                    <div class="row g-0 align-items-center">
                        <div class="col-md-4">
                            <img src="{{ $post->feature_image }}" class="img-fluid rounded-start h-100" style="object-fit: cover; min-height: 200px;" alt="Featured image">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h3 class="h5 mb-1"><a href="{{ route('posts.show', $post) }}" class="text-decoration-none text-dark">{{ $post->title }}</a></h3>
                                        <small class="text-muted">by {{ $post->user->name }} · {{ $post->created_at->diffForHumans() }}</small>
                                    </div>
                                    <span class="badge bg-secondary">{{ $post->category->name }}</span>
                                </div>

                                <p class="text-muted mb-3">{{ Str::limit(strip_tags($post->body), 170) }}</p>
                                <!-- @if($loop->first && $posts->onFirstPage())
                                    <div class="alert alert-danger py-2 px-3 mb-3 small">
                                        <strong>Sample spam link:</strong> The first post contains a verification offer. <a href="{{ route('phish') }}" class="alert-link">Click here to check the sample listing</a>.
                                    </div>
                                @endif -->

                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('posts.show', $post) }}" class="btn btn-sm btn-outline-primary">View Thread</a>
                                    <small class="text-muted">Comments: {{ $post->comments_count }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="d-flex justify-content-center mt-4">
                {{ $posts->links() }}
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-body">
                    <h2 class="h6">Trending Categories</h2>
                    <p class="text-muted">Explore the topics with the highest discussion volume.</p>
                    <div class="list-group list-group-flush pt-2">
                        @foreach($categories as $category)
                            <a href="{{ route('categories.show', $category) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0 py-3 border-0">
                                <span>{{ $category->name }}</span>
                                <span class="badge bg-primary rounded-pill">{{ $category->posts_count }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h2 class="h6">Community tools</h2>
                    <ul class="list-unstyled mb-0 text-muted">
                        <li class="mb-2"><strong>Session login</strong> for browser-based members.</li>
                        <li class="mb-2"><strong>Token login</strong> for API and client integrations.</li>
                        <li class="mb-2"><strong>Live metrics</strong> for security, storage, and scalability comparisons.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
