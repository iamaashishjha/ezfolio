@php
    $accentColor = $portfolioConfig['accentColor'];
    $accentColorRGB = Utils::getRgbValue($accentColor);
    $template = $portfolioConfig['template'] ?? 'procyon';
    if (!in_array($template, CoreConstants::PORTFOLIO_THEMES, true)) {
        $template = 'procyon';
    }
    $pageTitle = $pageTitle ?? ($portfolioConfig['seo']['title'] ?: $about->name);
    $pageDescription = $pageDescription ?? $portfolioConfig['seo']['description'];
    $pageImage = $pageImage ?? ($portfolioConfig['seo']['image_url'] ?? $portfolioConfig['seo']['image']);
    $canonicalUrl = $canonicalUrl ?? url()->current();
    $themeScript = $template === 'vega' ? 'scripts.js' : 'main.js';
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
    <meta property="og:image" content="{{ $pageImage }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $pageTitle }}" />
    <meta name="twitter:description" content="{{ $pageDescription }}" />
    <meta name="twitter:image" content="{{ $pageImage }}" />
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
            border: 1px solid rgba(15, 23, 42, 0.04);
        }
        .discussion-heading > i {
            color: {{ $accentColor }};
            font-size: 1.5rem;
        }
        .comment-composer {
            overflow: visible;
        }
        .comment-composer .form-control,
        .inline-reply-form .form-control {
            border-radius: 10px;
            border-color: #dbe1ea;
            background: #f8fafc;
        }
        .comment-composer textarea.form-control,
        .inline-reply-form textarea.form-control {
            resize: vertical;
        }
        .comment-composer .form-control:focus,
        .inline-reply-form .form-control:focus {
            background: #fff;
            border-color: {{ $accentColor }};
            box-shadow: 0 0 0 .2rem rgba({{ $accentColorRGB }}, .12);
        }
        .comment-avatar {
            align-items: center;
            background: rgba({{ $accentColorRGB }}, .12);
            border-radius: 50%;
            color: {{ $accentColor }};
            display: inline-flex;
            flex: 0 0 42px;
            font-size: .95rem;
            font-weight: 700;
            height: 42px;
            justify-content: center;
            text-transform: uppercase;
            width: 42px;
        }
        .comment-avatar-accent {
            background: {{ $accentColor }};
            color: #fff;
        }
        .comment-body {
            line-height: 1.6;
            overflow-wrap: anywhere;
            white-space: pre-line;
        }
        .comment-replies {
            border-left: 2px solid rgba({{ $accentColorRGB }}, .16);
            margin-left: 1.25rem;
            margin-top: 1rem;
            padding-left: 1.25rem;
        }
        .comment-replies .comment-card {
            box-shadow: 0 5px 16px rgba(15, 23, 42, 0.045);
        }
        .comment-reply-button {
            color: #6b7280;
            font-size: 0.82rem;
            font-weight: 600;
        }
        .comment-reply-button:hover,
        .comment-reply-button:focus {
            color: {{ $accentColor }};
            text-decoration: none;
        }
        .inline-reply-form {
            background: #f8fafc;
            border-radius: 12px;
            margin: 1rem 0 0 3.5rem;
            padding: 1rem;
        }
        .reply-context {
            color: #6b7280;
        }
        @media (max-width: 575.98px) {
            .comment-card {
                padding: 1rem;
            }
            .comment-replies,
            .comment-replies .comment-replies {
                margin-left: .5rem;
                padding-left: .65rem;
            }
            .inline-reply-form {
                margin-left: 0;
            }
            .comment-composer .card-body {
                padding: 1.2rem;
            }
        }
    </style>
</head>
<body class="blog-page">
    <a class="skip-link" href="#main-content">Skip to content</a>
    @include('common.preloader2')

    @if (in_array($template, ['kernel', 'blueprint', 'datastream', 'endpoint', 'mainframe', 'cluster', 'schema', 'uptime', 'pipeline', 'cloudline'], true))
        @include('frontend.blog.partials.nav-backend')
    @elseif ($template === 'rigel')
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
    <script src="{{ asset('assets/themes/' . $template . '/js/' . $themeScript) }}?v={{ filemtime(public_path('assets/themes/' . $template . '/js/' . $themeScript)) }}"></script>
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
