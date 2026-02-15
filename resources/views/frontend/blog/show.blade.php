@extends('frontend.blog.layout')

@section('content')
    <section class="blog-hero">
        <div class="container">
            <a href="{{ route('blog.index') }}" class="text-muted small">
                <i class="fas fa-arrow-left"></i> Back to Blog
            </a>
            <h1 class="mt-2 mb-2">{{ $post->title }}</h1>
            <div class="blog-meta">
                <span>{{ $post->published_at ? $post->published_at->format('M d, Y') : $post->created_at->format('M d, Y') }}</span>
                @if ($post->category)
                    <span> · {{ $post->category->name }}</span>
                @endif
                <span> · {{ $commentCount }} comments</span>
            </div>
            @if ($post->tags && $post->tags->count())
                <div class="mt-2">
                    @foreach ($post->tags as $tag)
                        <span class="blog-tag">{{ $tag->name }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    @if ($post->cover_image_url)
                        <div class="blog-post-cover">
                            <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="img-fluid">
                        </div>
                    @endif
                    <div class="blog-post-body">
                        {!! $post->body !!}
                    </div>

                    <div class="mt-5" id="comments">
                        <h4 class="mb-3">Comments</h4>

                        @if (session('comment_status'))
                            <div class="alert alert-info">
                                {{ session('comment_status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($post->allow_comments)
                            <div class="blog-card mb-4">
                                <div class="card-body">
                                    <h5 class="mb-3">Leave a Comment</h5>
                                    <div id="replying-to" class="text-muted small mb-3" style="display: none;">
                                        Replying to <strong id="replying-to-name"></strong>
                                        <button type="button" class="btn btn-link p-0 ml-2" id="cancel-reply">Cancel</button>
                                    </div>
                                    <form method="post" action="{{ route('blog.comment', $post->slug) }}">
                                        @csrf
                                        <input type="hidden" name="parent_id" id="comment-parent-id" value="">
                                        <div class="form-group">
                                            <label for="comment-name">Name</label>
                                            <input type="text" name="name" id="comment-name" class="form-control" value="{{ old('name') }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="comment-email">Email (optional)</label>
                                            <input type="email" name="email" id="comment-email" class="form-control" value="{{ old('email') }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="comment-body">Comment</label>
                                            <textarea name="body" id="comment-body" class="form-control" rows="4" required>{{ old('body') }}</textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Submit Comment</button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-light border">
                                Comments are disabled for this post.
                            </div>
                        @endif

                        @if ($comments && $comments->count())
                            @foreach ($comments as $comment)
                                @include('frontend.blog.partials.comment', ['comment' => $comment])
                            @endforeach
                        @else
                            <div class="alert alert-light border">
                                No comments yet.
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="blog-sidebar mb-4">
                        <h5 class="mb-3">Post Details</h5>
                        <div class="mb-2 text-muted">Category</div>
                        <div class="mb-3">{{ $post->category ? $post->category->name : 'Uncategorized' }}</div>
                        <div class="mb-2 text-muted">Published</div>
                        <div class="mb-3">{{ $post->published_at ? $post->published_at->format('M d, Y') : $post->created_at->format('M d, Y') }}</div>
                        @if ($post->tags && $post->tags->count())
                            <div class="mb-2 text-muted">Tags</div>
                            <div>
                                @foreach ($post->tags as $tag)
                                    <span class="blog-tag">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @if ($relatedPosts && $relatedPosts->count())
                        <div class="blog-sidebar">
                            <h5 class="mb-3">Related Posts</h5>
                            <ul class="list-unstyled mb-0">
                                @foreach ($relatedPosts as $related)
                                    <li class="mb-3">
                                        <a href="{{ route('blog.show', $related->slug) }}">
                                            <strong>{{ $related->title }}</strong>
                                        </a>
                                        <div class="text-muted small">
                                            {{ $related->published_at ? $related->published_at->format('M d, Y') : $related->created_at->format('M d, Y') }}
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    $(function() {
        const replyButtons = $('.comment-reply-button');
        const parentIdInput = $('#comment-parent-id');
        const replyingTo = $('#replying-to');
        const replyingToName = $('#replying-to-name');
        const cancelReply = $('#cancel-reply');

        replyButtons.on('click', function() {
            const commentId = $(this).data('reply-id');
            const commentName = $(this).data('reply-name');
            parentIdInput.val(commentId);
            replyingToName.text(commentName);
            replyingTo.show();
            $('html, body').animate({ scrollTop: $('#comments').offset().top - 80 }, 400);
        });

        cancelReply.on('click', function() {
            parentIdInput.val('');
            replyingTo.hide();
        });
    });
</script>
@endpush
