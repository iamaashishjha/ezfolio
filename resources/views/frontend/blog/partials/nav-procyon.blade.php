<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar ftco-navbar-light site-navbar-target fixed-top" id="ftco-navbar">
    <div class="container">
        <a class="navbar-brand" href="{{ route('frontend') }}"><span>{{ substr($about->name, 0, 1) }}</span>{{ substr($about->name, 1) }}</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav"
            aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav nav ml-auto">
                <li class="nav-item"><a href="{{ route('frontend') }}" class="nav-link"><span>Home</span></a></li>
                @if ($portfolioConfig['visibility']['about'])
                    <li class="nav-item"><a href="{{ route('frontend') }}#about-section" class="nav-link"><span>About</span></a></li>
                @endif
                @if ($portfolioConfig['visibility']['experiences'] || $portfolioConfig['visibility']['education'] || $portfolioConfig['visibility']['skills'])
                    <li class="nav-item"><a href="{{ route('frontend') }}#resume-section" class="nav-link"><span>Resume</span></a></li>
                @endif
                @if ($portfolioConfig['visibility']['services'])
                    <li class="nav-item"><a href="{{ route('frontend') }}#services-section" class="nav-link"><span>Services</span></a></li>
                @endif
                @if ($portfolioConfig['visibility']['projects'])
                    <li class="nav-item"><a href="{{ route('frontend') }}#projects-section" class="nav-link"><span>Projects</span></a></li>
                @endif
                <li class="nav-item"><a href="{{ route('blog.index') }}" class="nav-link"><span>Blog</span></a></li>
                @if ($portfolioConfig['visibility']['contact'])
                    <li class="nav-item"><a href="{{ route('frontend') }}#contact-section" class="nav-link"><span>Contact</span></a></li>
                @endif
            </ul>
        </div>
    </div>
</nav>
