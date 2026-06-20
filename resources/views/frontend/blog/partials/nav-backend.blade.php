<nav class="navbar navbar-expand-lg fixed-top backend-blog-nav" aria-label="Primary navigation">
    <div class="container">
        <a class="navbar-brand" href="{{ route('frontend') }}">{{ $about->name }}</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#backend-blog-nav" aria-controls="backend-blog-nav" aria-expanded="false" aria-label="Toggle navigation"><span aria-hidden="true">☰</span></button>
        <div class="collapse navbar-collapse" id="backend-blog-nav"><ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="{{ route('frontend') }}">Home</a></li>
            @if ($portfolioConfig['visibility']['about'])<li class="nav-item"><a class="nav-link" href="{{ route('frontend') }}#about">Profile</a></li>@endif
            @if ($portfolioConfig['visibility']['skills'])<li class="nav-item"><a class="nav-link" href="{{ route('frontend') }}#skills">Stack</a></li>@endif
            @if ($portfolioConfig['visibility']['projects'])<li class="nav-item"><a class="nav-link" href="{{ route('frontend') }}#projects">Systems</a></li>@endif
            <li class="nav-item"><a class="nav-link" href="{{ route('blog.index') }}" aria-current="page">Blog</a></li>
            @if ($portfolioConfig['visibility']['contact'])<li class="nav-item"><a class="nav-link" href="{{ route('frontend') }}#contact">Contact</a></li>@endif
        </ul></div>
    </div>
</nav>
