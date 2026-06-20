<div class="comment-card" id="comment-{{ $comment->id }}">
    <div class="comment-content d-flex align-items-start">
        <div class="comment-avatar mr-3" aria-hidden="true">{{ Str::upper(Str::substr($comment->name, 0, 1)) }}</div>
        <div class="flex-grow-1 comment-bubble">
            <div class="d-flex flex-wrap align-items-baseline mb-1">
                <strong class="mr-2">{{ $comment->name }}</strong>
                <span class="text-muted small" title="{{ $comment->created_at->format('M d, Y g:i A') }}">
                    {{ $comment->created_at->diffForHumans() }}
                </span>
            </div>
            <p class="mb-2 comment-body">{{ $comment->body }}</p>
            @if ($post->allow_comments)
                <button
                    type="button"
                    class="btn btn-link p-0 comment-reply-button"
                    data-reply-id="{{ $comment->id }}"
                    aria-controls="reply-form-{{ $comment->id }}"
                    aria-expanded="{{ (string) old('parent_id') === (string) $comment->id ? 'true' : 'false' }}"
                >
                    <i class="fas fa-reply mr-1"></i> Reply
                </button>
            @endif
        </div>
    </div>

    @if ($post->allow_comments)
        <div id="reply-form-{{ $comment->id }}" class="inline-reply-form {{ (string) old('parent_id') === (string) $comment->id ? '' : 'd-none' }}">
            <div class="reply-context small mb-3">
                <i class="fas fa-reply mr-1"></i> Replying to <strong>{{ $comment->name }}</strong>
            </div>
            <form method="post" action="{{ route('blog.comment', $post->slug) }}">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="reply-name-{{ $comment->id }}">Name</label>
                        <input type="text" name="name" id="reply-name-{{ $comment->id }}" class="form-control" value="{{ (string) old('parent_id') === (string) $comment->id ? old('name') : '' }}" maxlength="100" autocomplete="name" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="reply-email-{{ $comment->id }}">Email <span class="text-muted font-weight-normal">(optional)</span></label>
                        <input type="email" name="email" id="reply-email-{{ $comment->id }}" class="form-control" value="{{ (string) old('parent_id') === (string) $comment->id ? old('email') : '' }}" autocomplete="email">
                    </div>
                </div>
                <div class="form-group">
                    <label for="reply-body-{{ $comment->id }}" class="sr-only">Reply</label>
                    <textarea name="body" id="reply-body-{{ $comment->id }}" class="form-control" rows="3" maxlength="5000" placeholder="Write a reply…" required>{{ (string) old('parent_id') === (string) $comment->id ? old('body') : '' }}</textarea>
                </div>
                <div class="d-flex justify-content-end align-items-center">
                    <button type="button" class="btn btn-light btn-sm mr-2 cancel-inline-reply">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="far fa-paper-plane mr-1"></i> Post reply
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
@if ($comment->replies && $comment->replies->count())
    <div class="comment-replies">
        @foreach ($comment->replies as $reply)
            @include('frontend.blog.partials.comment', ['comment' => $reply])
        @endforeach
    </div>
@endif
