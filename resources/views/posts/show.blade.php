@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-4">
                <img src="{{ $post->feature_image }}" class="card-img-top" alt="Featured image">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="card-title">{{ $post->title }}</h1>
                        @can('update', $post)
                            <div>
                                <a href="{{ route('posts.edit', $post) }}" class="btn btn-sm btn-primary">Edit</a>
                                <form action="{{ route('posts.destroy', $post) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </div>
                        @endcan
                    </div>
                    <div class="text-muted mb-3">
                        Posted by {{ $post->user->name }} in 
                        <a href="{{ route('categories.show', $post->category) }}">{{ $post->category->name }}</a>
                        {{ $post->created_at->diffForHumans() }}
                    </div>
                    <div class="card-text">
                        {{ $post->body }}
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="card">
                <div class="card-header">Comments</div>
                <div class="card-body">
                    @auth
                        <form action="{{ route('comments.store', $post) }}" method="POST" class="mb-4">
                            @csrf
                            <div class="form-group">
                                <textarea name="body" class="form-control @error('body') is-invalid @enderror" rows="3" placeholder="Leave a comment">{{ old('body') }}</textarea>
                                @error('body')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary mt-2">Post Comment</button>
                        </form>
                    @else
                        <p><a href="{{ route('login') }}">Login</a> to leave a comment.</p>
                    @endauth

                    @foreach($post->comments->sortByDesc('created_at') as $comment)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $comment->user->name }}</strong>
                                    <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                </div>
                                @can('update', $comment)
                                    <div>
                                        <button class="btn btn-sm btn-link" onclick="toggleEditForm({{ $comment->id }})">Edit</button>
                                        <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-link text-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </div>
                                @endcan
                            </div>
                            <div id="comment-{{ $comment->id }}-body">
                                {{ $comment->body }}
                            </div>
                            @can('update', $comment)
                                <div id="comment-{{ $comment->id }}-form" style="display: none;">
                                    <form action="{{ route('comments.update', $comment) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="form-group">
                                            <textarea name="body" class="form-control" rows="2">{{ $comment->body }}</textarea>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-primary mt-2">Update</button>
                                        <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="toggleEditForm({{ $comment->id }})">Cancel</button>
                                    </form>
                                </div>
                            @endcan
                        </div>
                    @endforeach
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
