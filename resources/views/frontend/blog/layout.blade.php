@php
    $accentColor = $portfolioConfig['accentColor'];
    $accentColorRGB = Utils::getRgbValue($accentColor);
    $template = $portfolioConfig['template'] ?? 'procyon';
    $pageTitle = $pageTitle ?? ($portfolioConfig['seo']['title'] ?: $about->name);
    $pageDescription = $pageDescription ?? $portfolioConfig['seo']['description'];
    $pageImage = $pageImage ?? $portfolioConfig['seo']['image'];
    $canonicalUrl = $canonicalUrl ?? url()->current();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    @include('common.googleAnalytics')
    @if (!empty($portfolioConfig['script']['header']) && $portfolioConfig['script']['header'] != '')
    <script>
        {!! $portfolioConfig['script']['header'] !!}
    </script>
    @endif

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="{{ $pageDescription }}">
    <meta property="og:title" content="{{ $pageTitle }}" />
    <meta property="og:description" content="{{ $pageDescription }}" />
    <meta property="og:type" content="article" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:image" content="{{ asset($pageImage) }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $pageTitle }}" />
    <meta name="twitter:description" content="{{ $pageDescription }}" />
    <meta name="twitter:image" content="{{ asset($pageImage) }}" />
    <link rel="canonical" href="{{ $canonicalUrl }}" />
    <title>{{ $pageTitle }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ Utils::getFavicon() }}">

    <link href="{{ asset('assets/common/lib/mdi-icon/css/materialdesignicons.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/common/lib/fontawesome/css/all.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/common/lib/boxicons/css/boxicons.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/common/lib/iziToast/css/iziToast.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/common/lib/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/themes/' . $template . '/css/styles.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/themes/' . $template . '/css/custom.css') }}?v={{ filemtime(public_path('assets/themes/' . $template . '/css/custom.css')) }}" rel="stylesheet">
    @stack('styles')
    <style>
        :root {
            --z-accent-color: {{ $accentColor }};
        }
        body.blog-page {
            background: #f5f7fb;
            color: #1f2937;
        }
        a {
            color: {{ $accentColor }};
        }
        a:hover {
            color: rgba({{ $accentColorRGB }}, .8);
        }
        .blog-hero {
            padding: 6rem 0 3rem;
            background: linear-gradient(120deg, rgba({{ $accentColorRGB }}, .12), rgba(31, 41, 55, .05));
        }
        .blog-hero h1 {
            font-weight: 700;
        }
        .blog-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
            overflow: hidden;
            margin-bottom: 24px;
        }
        .blog-card .card-body {
            padding: 1.5rem 1.6rem;
        }
        .blog-meta {
            color: #6b7280;
            font-size: 0.85rem;
        }
        .blog-tag {
            display: inline-flex;
            align-items: center;
            font-size: 0.75rem;
            padding: 0.15rem 0.6rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.08);
            margin-right: 0.4rem;
            margin-bottom: 0.3rem;
        }
        .blog-sidebar {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
        }
        .blog-sidebar .form-control,
        .blog-sidebar .custom-select {
            border-radius: 10px;
        }
        .blog-post-cover {
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .blog-post-body {
            line-height: 1.8;
        }
        .blog-post-body img {
            max-width: 100%;
            height: auto;
        }
        .comment-card {
            background: #fff;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            margin-bottom: 1rem;
        }
        .comment-replies {
            margin-left: 1.5rem;
            margin-top: 1rem;
        }
        .comment-reply-button {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body class="blog-page">
    <a class="skip-link" href="#main-content">Skip to content</a>
    @include('common.preloader2')

    @if ($template === 'rigel')
        @include('frontend.blog.partials.nav-rigel')
    @elseif ($template === 'vega')
        @include('frontend.blog.partials.nav-vega')
    @else
        @include('frontend.blog.partials.nav-procyon')
    @endif

    <main id="main-content">
        @yield('content')
    </main>

    @if (!empty($portfolioConfig['visibility']['footer']) && (int) $portfolioConfig['visibility']['footer'] === CoreConstants::TRUE)
        <footer class="text-center py-4 text-muted">
            <small>&copy; {{ date('Y') }} {{ $about->name }}. All rights reserved.</small>
        </footer>
    @endif

    <script src="{{ asset('assets/common/lib/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/jquery-migrate/jquery-migrate.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/iziToast/js/iziToast.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/aos/aos.js') }}"></script>
    <script src="{{ asset('assets/themes/' . $template . '/js/main.js') }}?v={{ filemtime(public_path('assets/themes/' . $template . '/js/main.js')) }}"></script>
    @include('common.pixelTracking')
    <script>
        $(function() {
            if ($('#szn-preloader').length) {
                $('#szn-preloader').delay(100).fadeOut('slow', function() {
                    $(this).remove();
                });
            }
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    once: true,
                    duration: 700,
                });
            }
        });

        function showNotification(message = 'Something went wrong', type = 'error', sticky = false) {
            if (typeof iziToast === 'undefined') {
                return;
            }
            iziToast[type]({
                title: type[0].toUpperCase() + type.slice(1),
                message: message,
                position: 'bottomRight',
                timeout: sticky ? false : 4500,
            });
        }
    </script>
    @if (!empty($portfolioConfig['script']['footer']) && $portfolioConfig['script']['footer'] != '')
    <script>
        {!! $portfolioConfig['script']['footer'] !!}
    </script>
    @endif
    @stack('scripts')
</body>
</html>
