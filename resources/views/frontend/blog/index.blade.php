@extends('frontend.blog.layout')

@section('content')
    <section class="blog-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="mb-2">Blog</h1>
                    <p class="text-muted mb-0">Insights, notes, and updates from {{ $about->name }}.</p>
                </div>
                <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('blog.rss') }}">
                        <i class="fas fa-rss"></i> RSS
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    @if ($posts && $posts->count())
                        @foreach ($posts as $post)
                            <article class="blog-card">
                                @if ($post->cover_image_url)
                                    <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="img-fluid">
                                @endif
                                <div class="card-body">
                                    <div class="blog-meta mb-2">
                                        <span>{{ $post->published_at ? $post->published_at->format('M d, Y') : $post->created_at->format('M d, Y') }}</span>
                                        @if ($post->category)
                                            <span> · {{ $post->category->name }}</span>
                                        @endif
                                        @if (isset($post->comments_count))
                                            <span> · <i class="far fa-comment"></i> {{ number_format($post->comments_count) }}</span>
                                        @endif
                                        <span> · <i class="far fa-eye"></i> {{ number_format($post->views_count) }} views</span>
                                    </div>
                                    <h3 class="mb-2">
                                        <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                                    </h3>
                                    <p class="text-muted mb-3">
                                        {{ $post->excerpt ?: Str::limit(strip_tags($post->body), 180) }}
                                    </p>
                                    @if ($post->tags && $post->tags->count())
                                        <div class="mb-3">
                                            @foreach ($post->tags as $tag)
                                                <span class="blog-tag">{{ $tag->name }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    <a class="btn btn-primary btn-sm" href="{{ route('blog.show', $post->slug) }}">
                                        Read More
                                    </a>
                                </div>
                            </article>
                        @endforeach

                        <div class="mt-4">
                            {{ $posts->withQueryString()->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="alert alert-light border">
                            No posts yet. Check back soon.
                        </div>
                    @endif
                </div>
                <div class="col-lg-4">
                    <div class="blog-sidebar">
                        <h5 class="sidebar-title mb-3">
                            <i class="fas fa-sliders-h"></i> Filter Posts
                        </h5>
                        <form method="get" action="{{ route('blog.index') }}" id="blog-filter-form">
                            <div class="form-group">
                                <div class="search-wrapper">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" id="search" name="q" class="form-control search-input" value="{{ request()->get('q') }}" placeholder="Search title or keyword..." autocomplete="off">
                                    @if (request()->has('q') && request()->get('q') !== '')
                                        <button type="button" class="search-clear" onclick="this.closest('form').querySelector('[name=q]').value='';this.closest('form').submit()">&times;</button>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="filter-label"><i class="far fa-folder-open"></i> Category</label>
                                <div class="chip-group" data-input-name="category">
                                    <button type="button" class="chip {{ !request()->get('category') ? 'active' : '' }}" data-value="">All</button>
                                    @foreach ($categories as $category)
                                        <button type="button" class="chip {{ request()->get('category') === $category->slug ? 'active' : '' }}" data-value="{{ $category->slug }}">{{ $category->name }}</button>
                                    @endforeach
                                </div>
                                <input type="hidden" name="category" value="{{ request()->get('category') }}">
                            </div>

                            <div class="form-group">
                                <label class="filter-label"><i class="fas fa-tags"></i> Tag</label>
                                <div class="chip-group" data-input-name="tag">
                                    <button type="button" class="chip {{ !request()->get('tag') ? 'active' : '' }}" data-value="">All</button>
                                    @foreach ($tags as $tag)
                                        <button type="button" class="chip {{ request()->get('tag') === $tag->slug ? 'active' : '' }}" data-value="{{ $tag->slug }}">{{ $tag->name }}</button>
                                    @endforeach
                                </div>
                                <input type="hidden" name="tag" value="{{ request()->get('tag') }}">
                            </div>

                            <div class="d-flex gap-2 mt-4 filter-actions">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="fas fa-check"></i> Apply
                                </button>
                                <a href="{{ route('blog.index') }}" class="btn btn-outline-secondary flex-fill">
                                    <i class="fas fa-undo"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@push('scripts')
<script>
$(function() {
    $('.chip-group').each(function() {
        var group = $(this);
        var inputName = group.data('input-name');
        var hidden = group.closest('.form-group').find('input[type=hidden][name="' + inputName + '"]');

        group.on('click', '.chip', function() {
            var chip = $(this);
            var value = chip.data('value');

            group.find('.chip').removeClass('active');
            chip.addClass('active');
            hidden.val(value);

            $('#blog-filter-form').submit();
        });
    });
});
</script>
@endpush

@endsection
