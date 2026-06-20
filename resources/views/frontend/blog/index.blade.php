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
                        <h5 class="mb-3">Filter Posts</h5>
                        <form method="get" action="{{ route('blog.index') }}">
                            <div class="form-group">
                                <label for="search" class="text-muted">Search</label>
                                <input type="text" id="search" name="q" class="form-control" value="{{ request()->get('q') }}" placeholder="Search title or keyword">
                            </div>
                            <div class="form-group">
                                <label for="category" class="text-muted">Category</label>
                                <select id="category" name="category" class="custom-select">
                                    <option value="">All categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->slug }}" {{ request()->get('category') === $category->slug ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="tag" class="text-muted">Tag</label>
                                <select id="tag" name="tag" class="custom-select">
                                    <option value="">All tags</option>
                                    @foreach ($tags as $tag)
                                        <option value="{{ $tag->slug }}" {{ request()->get('tag') === $tag->slug ? 'selected' : '' }}>
                                            {{ $tag->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-flex flex-column">
                                <button type="submit" class="btn btn-primary mb-2">Apply Filters</button>
                                <a href="{{ route('blog.index') }}" class="btn btn-light">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
