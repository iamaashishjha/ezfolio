<button type="button" class="mobile-nav-toggle d-xl-none" aria-label="Toggle navigation"><i class="fas fa-bars"></i></button>
<header id="header" class="d-flex flex-column justify-content-center">
    <nav class="nav-menu">
        <ul>
            <li><a href="{{ route('frontend') }}#hero"><i class="bx bx-home"></i> <span>Home</span></a></li>
            @if ($portfolioConfig['visibility']['about'])
                <li><a href="{{ route('frontend') }}#about"><i class="bx bx-user"></i> <span>About</span></a></li>
            @endif
            @if ($portfolioConfig['visibility']['skills'])
                <li><a href="{{ route('frontend') }}#skills"><i class='bx bx-code-block'></i> <span>Skills</span></a></li>
            @endif
            @if ($portfolioConfig['visibility']['experiences'])
                <li><a href="{{ route('frontend') }}#experiences"><i class='bx bx-briefcase'></i> <span>Experiences</span></a></li>
            @endif
            @if ($portfolioConfig['visibility']['education'])
                <li><a href="{{ route('frontend') }}#education"><i class='bx bx-book'></i> <span>Education</span></a></li>
            @endif
            @if ($portfolioConfig['visibility']['projects'])
                <li><a href="{{ route('frontend') }}#projects"><i class='bx bxs-package'></i> <span>Projects</span></a></li>
            @endif
            @if ($portfolioConfig['visibility']['services'])
                <li><a href="{{ route('frontend') }}#services"><i class="bx bx-server"></i> <span>Services</span></a></li>
            @endif
            <li><a href="{{ route('blog.index') }}"><i class='bx bx-notepad'></i> <span>Blog</span></a></li>
            @if ($portfolioConfig['visibility']['contact'])
                <li><a href="{{ route('frontend') }}#contact"><i class="bx bx-envelope"></i> <span>Contact</span></a></li>
            @endif
        </ul>
    </nav>
</header>
