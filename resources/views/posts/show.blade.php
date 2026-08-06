@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row gx-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <img src="{{ $post->feature_image }}" class="img-fluid rounded-top" alt="Featured image" style="max-height: 420px; object-fit: cover;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h1 class="h3 mb-2">{{ $post->title }}</h1>
                            <p class="text-muted mb-1">by <strong>{{ $post->user->name }}</strong> in <a href="{{ route('categories.show', $post->category) }}">{{ $post->category->name }}</a></p>
                            <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                        </div>
                        @can('update', $post)
                            <div class="d-flex gap-2">
                                <a href="{{ route('posts.edit', $post) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('posts.destroy', $post) }}" method="POST" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </div>
                        @endcan
                    </div>
                    <div class="mb-4 text-muted">
                        {{ $post->body }}
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h2 class="h5 mb-0">Comments</h2>
                </div>
                <div class="card-body">
                    @auth
                        <form action="{{ route('comments.store', $post) }}" method="POST" class="mb-4">
                            @csrf
                            <div class="mb-3">
                                <textarea name="body" class="form-control @error('body') is-invalid @enderror" rows="4" placeholder="Share your thoughts">{{ old('body') }}</textarea>
                                @error('body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">Post Comment</button>
                        </form>
                    @else
                        <div class="alert alert-secondary mb-4">
                            <a href="{{ route('login') }}">Login</a> to join the discussion.
                        </div>
                    @endauth

                    @foreach($post->comments->sortByDesc('created_at') as $comment)
                        <div class="card mb-3 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>{{ $comment->user->name }}</strong>
                                        <div class="text-muted small">{{ $comment->created_at->diffForHumans() }}</div>
                                    </div>
                                    @can('update', $comment)
                                        <div class="d-flex gap-2 align-items-center">
                                            <button class="btn btn-sm btn-link text-decoration-none" onclick="toggleEditForm({{ $comment->id }})">Edit</button>
                                            <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                            </form>
                                        </div>
                                    @endcan
                                </div>
                                <div id="comment-{{ $comment->id }}-body">
                                    {!! $comment->body !!}
                                </div>
                                @can('update', $comment)
                                    <div id="comment-{{ $comment->id }}-form" style="display: none;">
                                        <form action="{{ route('comments.update', $comment) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="mb-2">
                                                <textarea name="body" class="form-control" rows="3">{{ $comment->body }}</textarea>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEditForm({{ $comment->id }})">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h2 class="h6 mb-3">Post details</h2>
                    <p class="text-muted mb-2"><strong>Author:</strong> {{ $post->user->name }}</p>
                    <p class="text-muted mb-2"><strong>Category:</strong> <a href="{{ route('categories.show', $post->category) }}">{{ $post->category->name }}</a></p>
                    <p class="text-muted mb-2"><strong>Published:</strong> {{ $post->created_at->format('M d, Y') }}</p>
                    <p class="text-muted mb-0"><strong>Comments:</strong> {{ $post->comments->count() }}</p>
                </div>
            </div>
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h2 class="h6 mb-3">Research links</h2>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><a href="{{ route('comparison.dashboard') }}" class="text-decoration-none">Authentication Comparison</a></li>
                        <li class="mb-2"><a href="{{ route('thesis.experiment') }}" class="text-decoration-none">Experiment Dashboard</a></li>
                        <li><a href="{{ route('presentation.summary') }}" class="text-decoration-none">Presentation Summary</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleEditForm(commentId) {
    const bodyElement = document.getElementById(`comment-${commentId}-body`);
    const formElement = document.getElementById(`comment-${commentId}-form`);
    
    if (bodyElement.style.display !== 'none') {
        bodyElement.style.display = 'none';
        formElement.style.display = 'block';
    } else {
        bodyElement.style.display = 'block';
        formElement.style.display = 'none';
    }
}
</script>
@endpush
@endsection
