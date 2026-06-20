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
                <span> · <i class="far fa-eye"></i> {{ number_format($post->views_count) }} views</span>
                <span> · <i class="far fa-comment"></i> {{ number_format($commentCount) }} comments</span>
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
                        <div class="discussion-heading d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h4 class="mb-1">Discussion</h4>
                                <p class="text-muted small mb-0">{{ number_format($commentCount) }} {{ Str::plural('comment', $commentCount) }}</p>
                            </div>
                            <i class="far fa-comments" aria-hidden="true"></i>
                        </div>

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
                            <div class="blog-card comment-composer mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="comment-avatar comment-avatar-accent mr-3"><i class="far fa-user"></i></div>
                                        <div>
                                            <h5 class="mb-0">Join the discussion</h5>
                                            <small class="text-muted">Share a thought or ask a question.</small>
                                        </div>
                                    </div>
                                    <form method="post" action="{{ route('blog.comment', $post->slug) }}">
                                        @csrf
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label for="comment-name">Name</label>
                                                <input type="text" name="name" id="comment-name" class="form-control" value="{{ old('parent_id') ? '' : old('name') }}" maxlength="100" autocomplete="name" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="comment-email">Email <span class="text-muted font-weight-normal">(not published)</span></label>
                                                <input type="email" name="email" id="comment-email" class="form-control" value="{{ old('parent_id') ? '' : old('email') }}" autocomplete="email">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="comment-body" class="sr-only">Comment</label>
                                            <textarea name="body" id="comment-body" class="form-control" rows="4" maxlength="5000" placeholder="Write a comment…" required>{{ old('parent_id') ? '' : old('body') }}</textarea>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <small class="text-muted">Comments are reviewed before appearing.</small>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="far fa-paper-plane mr-1"></i> Post comment
                                            </button>
                                        </div>
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
                        <div class="mb-2 text-muted">Total views</div>
                        <div class="mb-3"><i class="far fa-eye mr-1"></i> {{ number_format($post->views_count) }}</div>
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
        $('.comment-reply-button').on('click', function() {
            const button = $(this);
            const composer = $('#reply-form-' + button.data('reply-id'));

            $('.inline-reply-form').not(composer).addClass('d-none');
            $('.comment-reply-button').not(button).attr('aria-expanded', 'false');
            composer.toggleClass('d-none');
            button.attr('aria-expanded', composer.hasClass('d-none') ? 'false' : 'true');

            if (!composer.hasClass('d-none')) {
                composer.find('textarea').trigger('focus');
            }
        });

        $('.cancel-inline-reply').on('click', function() {
            const composer = $(this).closest('.inline-reply-form');
            composer.addClass('d-none');
            composer.closest('.comment-card').children('.comment-content').find('.comment-reply-button').attr('aria-expanded', 'false').trigger('focus');
        });
    });
</script>
@endpush
