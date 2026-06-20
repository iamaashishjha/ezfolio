@php
    $theme = $theme ?? 'kernel';
    $themeName = $themeName ?? ucfirst($theme);
    $accentColor = $portfolioConfig['accentColor'];
    $jobTitle = $about->job_title ?: 'Backend Engineer';
    $heroSubtitle = $about->hero_subtitle ?: 'Designing reliable APIs, data systems, and infrastructure that scale.';
    $resumePdf = $about->cv_url ?: asset('assets/common/cv/default.pdf');
    $socialLinks = !empty($about->social_links) ? (json_decode($about->social_links) ?: []) : [];
    $taglines = !empty($about->taglines) ? (json_decode($about->taglines, true) ?: []) : [];
    $recaptchaSiteKey = config('services.recaptcha.site_key');
    $visibility = $portfolioConfig['visibility'];
@endphp
<!doctype html>
<html lang="en">
<head>
    @include('common.googleAnalytics')
    @if (!empty($portfolioConfig['script']['header']))<script>{!! $portfolioConfig['script']['header'] !!}</script>@endif
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $portfolioConfig['seo']['description'] }}">
    <meta name="author" content="{{ $portfolioConfig['seo']['author'] }}">
    <meta property="og:title" content="{{ $portfolioConfig['seo']['title'] }}">
    <meta property="og:description" content="{{ $portfolioConfig['seo']['description'] }}">
    <meta property="og:image" content="{{ $portfolioConfig['seo']['image_url'] ?? asset($portfolioConfig['seo']['image']) }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ Utils::getFavicon() }}">
    <title>{{ $portfolioConfig['seo']['title'] ?: $about->name }}</title>
    <link rel="stylesheet" href="{{ asset('assets/themes/' . $theme . '/css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/themes/' . $theme . '/css/custom.css') }}?v={{ filemtime(public_path('assets/themes/' . $theme . '/css/custom.css')) }}">
    <style>:root{--accent:{{ $accentColor }};--z-accent-color:{{ $accentColor }}}</style>
    @if (!empty($recaptchaSiteKey))<script src="https://www.google.com/recaptcha/api.js" async defer></script>@endif
</head>
<body class="be-theme theme-{{ $theme }}">
<a class="skip-link" href="#main-content">Skip to content</a>
<header class="site-header">
    <a class="brand" href="{{ route('frontend') }}" aria-label="{{ $about->name }} home"><span class="brand-mark" aria-hidden="true">{{ strtoupper(substr($about->name, 0, 1)) }}</span><span>{{ $about->name }}</span></a>
    <button class="nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false"><span aria-hidden="true">☰</span><span class="sr-only">Toggle navigation</span></button>
    <nav id="site-nav" class="site-nav" aria-label="Primary navigation">
        <a href="#about">Profile</a>
        @if ($visibility['skills'])<a href="#skills">Stack</a>@endif
        @if ($visibility['experiences'])<a href="#experience">Experience</a>@endif
        @if ($visibility['projects'])<a href="#projects">Systems</a>@endif
        @if ($visibility['services'])<a href="#services">Services</a>@endif
        @if ($visibility['blog'])<a href="{{ route('blog.index') }}">Blog</a>@endif
        @if ($visibility['contact'])<a href="#contact">Contact</a>@endif
    </nav>
</header>

<main id="main-content">
    <section class="hero section-shell" id="top">
        <div class="hero-copy">
            <p class="eyebrow"><span class="status-dot"></span>{{ $themeName }} / backend engineering</p>
            <h1>{{ $about->name }}<span>{{ $jobTitle }}</span></h1>
            <p class="hero-subtitle">{{ $heroSubtitle }}</p>
            @if ($taglines)<div class="signal-list" aria-label="Specialities">@foreach(array_slice($taglines, 0, 4) as $tagline)<span>{{ $tagline }}</span>@endforeach</div>@endif
            <div class="hero-actions">
                @if ($visibility['projects'])<a class="button primary" href="#projects">Inspect systems</a>@endif
                @if ($visibility['cv'])<a class="button" href="{{ $resumePdf }}" download>Download résumé</a>@endif
            </div>
        </div>
        <div class="hero-visual" aria-hidden="true">
            <div class="visual-top"><i></i><i></i><i></i><span>{{ $theme }}.runtime</span></div>
            <div class="visual-body"><span>01</span><b>service</b> portfolio {<br><span>02</span>&nbsp;&nbsp;role: <em>backend_engineer</em>,<br><span>03</span>&nbsp;&nbsp;focus: [<em>api</em>, <em>data</em>, <em>reliability</em>],<br><span>04</span>&nbsp;&nbsp;status: <strong>available</strong><br><span>05</span>}</div>
            <div class="node-map"><i></i><i></i><i></i><i></i><i></i></div>
        </div>
    </section>

    @if ($visibility['about'])
    <section class="content-section section-shell about-section" id="about">
        <header class="section-heading"><p>01 / Identity</p><h2>Engineering dependable foundations</h2></header>
        <div class="about-grid">
            @if ($about->avatar_url)<img class="portrait" src="{{ $about->avatar_url }}" alt="Portrait of {{ $about->name }}" loading="lazy">@endif
            <div class="rich-text">{!! $about->description !!}</div>
            <dl class="fact-list">
                @if ($about->email)<div><dt>Email</dt><dd><a href="mailto:{{ $about->email }}">{{ $about->email }}</a></dd></div>@endif
                @if ($about->address)<div><dt>Region</dt><dd>{{ $about->address }}</dd></div>@endif
                <div><dt>Discipline</dt><dd>{{ $jobTitle }}</dd></div>
            </dl>
        </div>
        @if (!empty($aboutHighlights))<ul class="highlight-list" aria-label="Engineering highlights">@foreach($aboutHighlights as $highlight)<li>{{ $highlight }}</li>@endforeach</ul>@endif
    </section>
    @endif

    @if ($visibility['skills'])
    <section class="content-section section-shell" id="skills">
        <header class="section-heading"><p>02 / Capabilities</p><h2>Backend systems toolkit</h2></header>
        <div class="skill-grid">
            @foreach ($skills as $skill)<article class="skill-card"><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $skill->name }}</h3>@if ($visibility['skillProficiency'])<div class="meter" role="progressbar" aria-label="{{ $skill->name }} proficiency" aria-valuenow="{{ $skill->proficiency }}" aria-valuemin="0" aria-valuemax="100"><i style="width:{{ max(0, min(100, (int)$skill->proficiency)) }}%"></i></div><small>{{ $skill->proficiency }}% operational confidence</small>@endif</article>@endforeach
        </div>
    </section>
    @endif

    @if ($visibility['experiences'] || $visibility['education'])
    <section class="content-section section-shell journey-section" id="experience">
        <header class="section-heading"><p>03 / Runtime history</p><h2>Experience and education</h2></header>
        <div class="journey-grid">
            @if ($visibility['experiences'])<div><h3 class="column-title">Production experience</h3>@foreach ($experiences as $experience)<article class="timeline-card"><time>{{ $experience->period }}</time><h3>{{ $experience->position }}</h3><p class="muted">{{ $experience->company }}</p><div class="rich-text">{!! $experience->details !!}</div></article>@endforeach</div>@endif
            @if ($visibility['education'])<div><h3 class="column-title">Knowledge base</h3>@foreach ($education as $item)<article class="timeline-card"><time>{{ $item->period }}</time><h3>{{ $item->degree }}</h3><p class="muted">{{ $item->institution }}</p>@if ($item->department)<p>{{ $item->department }}</p>@endif @if ($item->thesis)<p>{{ $item->thesis }}</p>@endif</article>@endforeach</div>@endif
        </div>
    </section>
    @endif

    @if ($visibility['services'])
    <section class="content-section section-shell" id="services">
        <header class="section-heading"><p>04 / Interfaces</p><h2>Backend engineering services</h2></header>
        <div class="service-grid">@foreach ($services as $service)<article class="service-card"><span class="service-index">SVC-{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $service->title }}</h3><div class="rich-text">{!! $service->details !!}</div></article>@endforeach</div>
    </section>
    @endif

    @if ($visibility['projects'])
    <section class="content-section section-shell" id="projects">
        <header class="section-heading"><p>05 / Deployments</p><h2>Selected systems and projects</h2></header>
        <div class="project-grid">@foreach ($projects as $project)<article class="project-card">@if ($project->thumbnail_url)<img src="{{ $project->thumbnail_url }}" alt="" loading="lazy">@endif<div class="project-copy"><p class="project-type">{{ $project->categories ?: 'Backend system' }}</p><h3>{{ $project->title }}</h3><div class="rich-text">{!! $project->details !!}</div>@if ($project->link)<a href="{{ $project->link }}" target="_blank" rel="noopener noreferrer">Open deployment <span aria-hidden="true">↗</span></a>@endif</div></article>@endforeach</div>
    </section>
    @endif

    @if ($visibility['blog'])
    <section class="content-section section-shell dispatch-section"><div><p class="eyebrow">Engineering notes / field reports</p><h2>Writing about APIs, architecture, data, and reliability.</h2></div><a class="button primary" href="{{ route('blog.index') }}">Read the blog</a></section>
    @endif

    @if ($visibility['contact'])
    <section class="content-section section-shell contact-section" id="contact">
        <header class="section-heading"><p>06 / Handshake</p><h2>Start a technical conversation</h2></header>
        <div class="contact-grid"><div><p>Have an API, platform, or reliability problem worth untangling? Send the useful details.</p>@if($about->email)<a href="mailto:{{ $about->email }}">{{ $about->email }}</a>@endif @if($about->phone)<p>{{ $about->phone }}</p>@endif</div>
        <form id="contact-me-form" action="{{ route('contact-me') }}" method="post">@csrf
            <div class="hp-field" aria-hidden="true"><label for="website">Website</label><input id="website" name="website" tabindex="-1" autocomplete="off"></div>
            <div class="field-row"><label>Name<input name="name" required maxlength="120" autocomplete="name"></label><label>Email<input name="email" type="email" required autocomplete="email"></label></div>
            <label>Subject<input name="subject" required maxlength="150"></label><label>Message<textarea name="body" rows="6" required></textarea></label>
            @if (!empty($recaptchaSiteKey))<div class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKey }}"></div>@endif
            <button class="button primary" type="submit">Send request</button><p class="form-status" role="status" aria-live="polite"></p>
        </form></div>
    </section>
    @endif
</main>

@if ($visibility['footer'])<footer class="site-footer section-shell"><p>© {{ now()->year }} {{ $about->name }}</p><p>Backend engineering · systems thinking · reliable delivery</p><div>@foreach($socialLinks as $social)<a href="{{ $social->link }}" target="_blank" rel="noopener noreferrer">{{ $social->title }}</a>@endforeach</div></footer>@endif
@include('common.pixelTracking')
<script src="{{ asset('assets/themes/' . $theme . '/js/main.js') }}?v={{ filemtime(public_path('assets/themes/' . $theme . '/js/main.js')) }}"></script>
@if (!empty($portfolioConfig['script']['footer']))<script>{!! $portfolioConfig['script']['footer'] !!}</script>@endif
</body>
</html>
