<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top" id="sideNav">
    <a class="navbar-brand js-scroll-trigger" href="{{ route('frontend') }}#page-top">
        <span class="d-block d-lg-none">{{ $about->name }}</span>
        <span class="d-none d-lg-block">
            <img class="lazy img-fluid img-profile rounded-circle mx-auto mb-2" data-src="{{ $about->avatar_url }}" src="{{ asset('assets/common/img/lazyloader.gif') }}" alt="Portrait of {{ $about->name }}" loading="lazy" decoding="async" />
        </span>
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav">
            @if ($portfolioConfig['visibility']['about'])
                <li class="nav-item"><a class="nav-link js-scroll-trigger" href="{{ route('frontend') }}#about">About</a></li>
            @endif
            @if ($portfolioConfig['visibility']['experiences'])
                <li class="nav-item"><a class="nav-link js-scroll-trigger" href="{{ route('frontend') }}#experience">Experience</a></li>
            @endif
            @if ($portfolioConfig['visibility']['education'])
                <li class="nav-item"><a class="nav-link js-scroll-trigger" href="{{ route('frontend') }}#education">Education</a></li>
            @endif
            @if ($portfolioConfig['visibility']['skills'])
                <li class="nav-item"><a class="nav-link js-scroll-trigger" href="{{ route('frontend') }}#skills">Skills</a></li>
            @endif
            @if ($portfolioConfig['visibility']['projects'])
                <li class="nav-item"><a class="nav-link js-scroll-trigger" href="{{ route('frontend') }}#projects">Projects</a></li>
            @endif
            @if ($portfolioConfig['visibility']['services'])
                <li class="nav-item"><a class="nav-link js-scroll-trigger" href="{{ route('frontend') }}#services">Services</a></li>
            @endif
            <li class="nav-item"><a class="nav-link" href="{{ route('blog.index') }}">Blog</a></li>
            @if ($portfolioConfig['visibility']['contact'])
                <li class="nav-item"><a class="nav-link js-scroll-trigger" href="{{ route('frontend') }}#contact">Contact</a></li>
            @endif
        </ul>
    </div>
</nav>
