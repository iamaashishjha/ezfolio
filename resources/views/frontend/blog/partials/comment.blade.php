<div class="comment-card" id="comment-{{ $comment->id }}">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>{{ $comment->name }}</strong>
        <span class="text-muted small">{{ $comment->created_at->format('M d, Y') }}</span>
    </div>
    <p class="mb-2">{{ $comment->body }}</p>
    <button type="button" class="btn btn-link p-0 comment-reply-button" data-reply-id="{{ $comment->id }}" data-reply-name="{{ $comment->name }}">
        Reply
    </button>
</div>
@if ($comment->replies && $comment->replies->count())
    <div class="comment-replies">
        @foreach ($comment->replies as $reply)
            @include('frontend.blog.partials.comment', ['comment' => $reply])
        @endforeach
    </div>
@endif
