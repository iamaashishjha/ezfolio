<!--

* Author        : "colorlib"
* Template Name : Ronaldo
* Version       : 1.0

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

-->

@php
    $accentColor = $portfolioConfig['accentColor'];
    $accentColorRGB = Utils::getRgbValue($accentColor);
    $resumePdf = $about->cv ?: 'assets/common/cv/default.pdf';
    $resumeDocx = preg_replace('/\\.pdf$/i', '.docx', $resumePdf);
    $hasResumeDocx = $resumeDocx && file_exists(public_path($resumeDocx));
    $jobTitle = $about->job_title ?: 'Software Engineer';
    $heroSubtitle = $about->hero_subtitle ?: 'Building scalable backend systems and microservices using Laravel, Lumen, Node.js, and modern databases.';
    $recaptchaSiteKey = config('services.recaptcha.site_key');
    $socialLinks = [];
    $schemaSameAs = [];
    if (!empty($about->social_links)) {
        $socialLinks = json_decode($about->social_links) ?: [];
        foreach ($socialLinks as $social) {
            if (!empty($social->link)) {
                $schemaSameAs[] = $social->link;
            }
        }
    }
    $schemaSameAs = array_values(array_unique(array_filter($schemaSameAs)));
    $baseUrl = url('/');
    $personSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => $about->name,
        'jobTitle' => $jobTitle,
        'url' => $baseUrl,
        'image' => asset($about->avatar),
    ];
    if (!empty($about->email)) {
        $personSchema['email'] = 'mailto:' . $about->email;
    }
    if (!empty($schemaSameAs)) {
        $personSchema['sameAs'] = $schemaSameAs;
    }
    $websiteSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $portfolioConfig['seo']['title'] ?: $about->name,
        'url' => $baseUrl,
        'description' => $portfolioConfig['seo']['description'],
    ];
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    @include('common.googleAnalytics')
    @if (!empty($portfolioConfig['script']['header']) && $portfolioConfig['script']['header'] != '')
    <script>
        {!!$portfolioConfig['script']['header']!!}
    </script>
    @endif

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta property="og:title" content="{{$portfolioConfig['seo']['title']}}" />
    <meta property="title" content="{{$portfolioConfig['seo']['title']}}" />
    <meta name="description" content="{{$portfolioConfig['seo']['description']}}" />
    <meta property="og:description" content="{{$portfolioConfig['seo']['description']}}" />
    <meta name="author" content="{{$portfolioConfig['seo']['author']}}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:image" content="{{asset($portfolioConfig['seo']['image'])}}" />
    <meta property="og:image:secure_url" content="{{asset($portfolioConfig['seo']['image'])}}" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{$portfolioConfig['seo']['title']}}" />
    <meta name="twitter:description" content="{{$portfolioConfig['seo']['description']}}" />
    <meta name="twitter:image" content="{{asset($portfolioConfig['seo']['image'])}}" />
    <link rel="canonical" href="{{ url()->current() }}" />
    <title>{{$portfolioConfig['seo']['title'] ?: $about->name}}</title>
    <link rel="shortcut icon" type="image/x-icon"  href="{{ Utils::getFavicon() }}">
    <link rel="preload" as="image" href="{{ asset($about->cover) }}">

    <link href="{{ asset('assets/common/lib/mdi-icon/css/materialdesignicons.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/common/lib/fontawesome/css/all.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/common/lib/boxicons/css/boxicons.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/common/lib/iziToast/css/iziToast.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/common/lib/aos/aos.css') }}" rel="stylesheet">
    <!-- Template Main CSS File -->
    <link href="{{ asset('assets/themes/procyon/css/styles.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/themes/procyon/css/custom.css') }}?v={{ filemtime(public_path('assets/themes/procyon/css/custom.css')) }}" rel="stylesheet">
    @if (!empty($recaptchaSiteKey))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
    <style>
        :root {
          --z-accent-color: {{$accentColor}};
        }
        .bg-primary, {
            background-color: {{$accentColor.' !important'}};
        }

        a {
            color: {{$accentColor}};
        }

        a:hover {
            color: rgba({{$accentColorRGB}}, .7);
        }

        .form-control:focus {
            border-color: rgba({{$accentColorRGB}}, .5) !important;
            box-shadow: none;
        }
        
        .border-primary {
            border-color: var(--z-accent-color) !important;
        }

        .text-primary {
            color: {{$accentColor.' !important'}};
        }
    </style>
    <script type="application/ld+json">
        {!! json_encode($personSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
</head>

<body data-spy="scroll" data-target=".site-navbar-target" data-offset="300">
    <a class="skip-link" href="#main-content">Skip to content</a>
    @include('common.preloader2')
    {{-- <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar ftco-navbar-light site-navbar-target"
        id="ftco-navbar">
        <div class="container">
            <a class="navbar-brand" href="#"><span>{{substr($about->name, 0, 1)}}</span>{{substr($about->name, 1)}}</a>
            <button class="navbar-toggler js-fh5co-nav-toggle fh5co-nav-toggle" type="button" data-toggle="collapse"
                data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span> Menu
            </button>
            <div class="collapse navbar-collapse" id="ftco-nav">
                <ul class="navbar-nav nav ml-auto">
                    <li class="nav-item"><a href="#hero" class="nav-link"><span>Home</span></a></li>
                    @if ($portfolioConfig['visibility']['about'])
                    <li class="nav-item"><a href="#about-section" class="nav-link"><span>About</span></a></li>
                    @endif
                    @if ($portfolioConfig['visibility']['experiences'] || $portfolioConfig['visibility']['education'] ||
                    $portfolioConfig['visibility']['skills'])
                    <li class="nav-item"><a href="#resume-section" class="nav-link"><span>Resume</span></a></li>
                    @endif
                    @if ($portfolioConfig['visibility']['services'])
                    <li class="nav-item"><a href="#services-section" class="nav-link"><span>Services</span></a></li>
                    @endif
                    @if ($portfolioConfig['visibility']['projects'])
                    <li class="nav-item"><a href="#projects-section" class="nav-link"><span>Projects</span></a></li>
                    @endif
                    @if ($portfolioConfig['visibility']['contact'])
                    <li class="nav-item"><a href="#contact-section" class="nav-link"><span>Contact</span></a></li>
                    @endif
                </ul>
            </div>
        </div>
    </nav> --}}
    <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar ftco-navbar-light site-navbar-target fixed-top"
        id="ftco-navbar">
        <div class="container">
            <a class="navbar-brand" href="#"><span>{{substr($about->name, 0, 1)}}</span>{{substr($about->name, 1)}}</a>
            {{-- <button class="navbar-toggler js-fh5co-nav-toggle fh5co-nav-toggle" type="button"
                data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span> Menu
            </button> --}}
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav"
                aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="ftco-nav">
                <ul class="navbar-nav nav ml-auto">
                    <li class="nav-item"><a href="#hero" class="nav-link"><span>Home</span></a></li>
                    @if ($portfolioConfig['visibility']['about'])
                    <li class="nav-item"><a href="#about-section" class="nav-link"><span>About</span></a></li>
                    @endif
                    @if ($portfolioConfig['visibility']['experiences'] || $portfolioConfig['visibility']['education'] ||
                    $portfolioConfig['visibility']['skills'])
                    <li class="nav-item"><a href="#resume-section" class="nav-link"><span>Resume</span></a></li>
                    @endif
                    @if ($portfolioConfig['visibility']['services'])
                    <li class="nav-item"><a href="#services-section" class="nav-link"><span>Services</span></a></li>
                    @endif
                    @if ($portfolioConfig['visibility']['projects'])
                    <li class="nav-item"><a href="#projects-section" class="nav-link"><span>Projects</span></a></li>
                    @endif
                    @if ($portfolioConfig['visibility']['contact'])
                    <li class="nav-item"><a href="#contact-section" class="nav-link"><span>Contact</span></a></li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
    <main id="main-content">
    <section class="hero-wrap js-fullheight" id="hero" style="background-image: url('{{asset($about->cover)}}');">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text js-fullheight justify-content-center align-items-center">
                <div class="col-lg-8 col-md-6 d-flex align-items-center" data-aos="fade-up"
                    data-aos-anchor-placement="top-bottom">
                    <div class="text text-center">
                        <h1 class="mb-2">{{$about->name}} <span class="hero-role">— {{ $jobTitle }}</span></h1>
                        <h2 class="mb-3">
                            <span id="typed-strings"></span>
                            {{-- <span class="txt-rotate" data-period="2000"
                                data-rotate='{!! json_encode(json_decode($about->taglines)) !!}'>
                            </span> --}}
                        </h2>
                        <p class="hero-subtitle">{{ $heroSubtitle }}</p>
                        <div class="hero-actions">
                            @if ($portfolioConfig['visibility']['cv'])
                                <a href="{{ $resumePdf }}" class="btn btn-primary py-3 px-4" download>
                                    <span class="btn-icon"><span class="fas fa-file-download" aria-hidden="true"></span></span>
                                    Download Resume
                                </a>
                            @endif
                            @if ($portfolioConfig['visibility']['projects'])
                                <a href="#projects-section" class="btn btn-light py-3 px-4">
                                    <span class="btn-icon"><span class="fas fa-folder-open" aria-hidden="true"></span></span>
                                    View Projects
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if ($portfolioConfig['visibility']['about'])
        <div class="mouse d-lg-block d-none">
            <a href="#about-section" class="mouse-icon" aria-label="Scroll to About">
                <div class="mouse-wheel"><span class="bx bxs-chevrons-down"></span></div>
            </a>
        </div>
        @endif
    </section>

    @if ($portfolioConfig['visibility']['about'])
    <section class="ftco-about img ftco-section ftco-no-pt ftco-no-pb goto-here" id="about-section">
        <div class="container">
            <div class="row d-flex no-gutters">
                <div class="col-md-6 col-lg-6 d-flex mt-5 mt-lg-0">
                    <div class="img-about img d-flex align-items-stretch">
                        <div class="overlay"></div>
                        <img class="img d-flex align-self-stretch align-items-center mx-auto my-auto lazy rounded-circle" data-src="{{asset($about->avatar)}}" src="{{asset('assets/common/img/lazyloader.gif')}}" alt="Portrait of {{$about->name}}" loading="lazy" decoding="async"/>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 pl-md-5 py-5">
                    <div class="row justify-content-start pb-3">
                        <div class="col-md-12 heading-section">
                            <h2 class="mb-4">About Me</h2>
                            @if ($about->description)
                            <div class="pb-2 text-muted">
                                {!! $about->description !!}
                            </div>
                            @endif
                            <div class="about-highlights">
                                @foreach ($aboutHighlights as $highlight)
                                    <span class="about-highlight">
                                        <span class="highlight-icon fas fa-check-circle" aria-hidden="true"></span>
                                        <span>{{$highlight}}</span>
                                    </span>
                                @endforeach
                            </div>
                            <ul class="about-info mt-4 px-md-0 px-2">
                                <li class="d-flex"><span>Name:</span> <span>{{ $about->name }}</span></li>
                                @if ($about->email && $about->email !== '')
                                <li class="d-flex"><span>Email:</span> <span>{{$about->email}}</span></li>
                                @endif
                                @if ($about->phone && $about->phone !== '')
                                <li class="d-flex"><span>Phone:</span> <span>{{$about->phone}}</span></li>
                                @endif
                                @if ($about->address && $about->address !== '')
                                <li class="d-flex"><span>Address:</span> <span>{{$about->address}}</span></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    @if ($portfolioConfig['visibility']['cv'])
                    <div class="counter-wrap d-flex justify-content-center mt-md-3" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                        <div class="text">
                            <p>
                                <a href="{{ $resumePdf }}" class="btn btn-primary py-3 px-3" download>
                                    <span class="btn-icon"><span class="fas fa-file-pdf" aria-hidden="true"></span></span>
                                    Download Resume (PDF)
                                </a>
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
    <section class="contact-section ftco-no-pb social-icon-block">
        <div class="container">
            <div class="row d-flex">
                @if (!empty($socialLinks))
                    @foreach ($socialLinks as $social)
                        <div class="col-6 col-md-4 col-lg-2 mb-lg-4" data-aos="zoom-in">
                            <div class="align-self-stretch box text-center p-3 shadow">
                                <a href="{{$social->link}}" target="_blank" rel="noopener noreferrer" aria-label="{{$social->title}}">
                                    <div class="icon d-flex align-items-center justify-content-center">
                                        <span class="{{$social->iconClass}}"></span>
                                    </div>
                                    <div>
                                        <p class="mb-0">{{$social->title}}</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>
    @endif

    @if ($portfolioConfig['visibility']['experiences'] || $portfolioConfig['visibility']['education'] ||
    $portfolioConfig['visibility']['skills'])
    <section class="ftco-section ftco-no-pb" id="resume-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <nav id="navi">
                        <ul>
                            @if ($portfolioConfig['visibility']['education'])
                            <li><a href="#page-1">Education</a></li>
                            @endif
                            @if ($portfolioConfig['visibility']['experiences'])
                            <li><a href="#page-2">Experience</a></li>
                            @endif
                            @if ($portfolioConfig['visibility']['skills'])
                            <li><a href="#page-3">Skills</a></li>
                            @endif
                        </ul>
                    </nav>
                </div>
                <div class="col-md-9">
                    @if ($portfolioConfig['visibility']['education'])
                    <div id="page-1" class="page one">
                        <h2 class="heading">Education</h2>
                        @if ($education)
                        @foreach ($education as $value)
                        <div class="resume-wrap d-flex" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                            <div class="icon d-flex align-items-center justify-content-center">
                                <span class="fas fa-book-open"></span>
                            </div>
                            <div class="text pl-3">
                                <span class="date">{{$value->period}}</span>
                                <h2>{{$value->degree}}</h2>
                                <span class="position">{{$value->institution}}</span>
                                <p class="mb-0">{{$value->cgpa && $value->cgpa !== '' ? 'CGPA: '.$value->cgpa : '' }}
                                </p>
                                <p>{{$value->thesis && $value->thesis !== '' ? 'Thesis: '.$value->thesis : '' }}</p>
                            </div>
                        </div>
                        @endforeach
                        @endif
                    </div>
                    @endif
                    @if ($portfolioConfig['visibility']['experiences'])
                    <div id="page-2" class="page two">
                        <h2 class="heading">Experience</h2>
                        @if ($experiences)
                            @foreach ($experiences as $experience)
                                <div class="resume-wrap d-flex" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                                    <div class="icon d-flex align-items-center justify-content-center">
                                        <span class="fas fa-briefcase"></span>
                                    </div>
                                    <div class="text pl-3">
                                        <span class="date">{{$experience->period}}</span>
                                        <h2>{{$experience->position}}</h2>
                                        <span class="position">{{$experience->company}}</span>
                                        @if ($experience->details)
                                        <div class="experience-details">{!! $experience->details !!}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    @endif
                    @if ($portfolioConfig['visibility']['skills'])
                    <div id="page-3" class="page three">
                        <h2 class="heading">Skills</h2>
                        @if ((int)$portfolioConfig['visibility']['skillProficiency'])
                        @if (!empty($skills))
                        <div class="row progress-circle mb-5">
                            @foreach ($skills as $key => $skill)
                            @if ($key < 3)
                            <div class="col-lg-4 mb-4" data-aos="zoom-in">
                                <div class="bg-white rounded-lg shadow p-4 z-hover">
                                    <h2 class="h5 font-weight-bold text-center mb-4">{{$skill->name}}</h2>
                                    <div class="progress mx-auto mb-4" data-value='{{$skill->proficiency}}'>
                                        <span class="progress-left">
                                            <span class="progress-bar border-primary"></span>
                                        </span>
                                        <span class="progress-right">
                                            <span class="progress-bar border-primary"></span>
                                        </span>
                                        <div
                                            class="progress-value w-100 h-100 rounded-circle d-flex align-items-center justify-content-center">
                                            <div class="h2 font-weight-bold">{{$skill->proficiency}}<sup
                                                    class="small">%</sup></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                        @endif
                        @if (!empty($skills))
                        <div class="row">
                            @foreach ($skills as $key => $skill)
                            @if ($key >= 3)
                            <div class="col-md-6">
                                <div class="progress-wrap" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                                    <h3>{{$skill->name}}</h3>
                                    <div class="progress">
                                        <div class="progress-bar color-1" role="progressbar"
                                            aria-valuenow="{{$skill->proficiency}}" aria-valuemin="0" aria-valuemax="100"
                                            style="width:{{$skill->proficiency}}%">
                                            <span>{{$skill->proficiency}}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                        @endif
                        @else
                        <div class="row progress-circle mb-5">
                            @foreach ($skills as $key => $skill)
                            <div class="col-lg-4 col-md-6 mb-4" data-aos="zoom-in">
                                <div class="bg-white rounded-lg shadow p-4 z-hover">
                                    <h2 class="h5 font-weight-bold text-center">{{$skill->name}}</h2>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        </div>
    </section>
    @endif


    @if ($portfolioConfig['visibility']['services'])
    <section class="ftco-section" id="services-section">
        <div class="container-fluid px-md-5">
            <div class="row justify-content-center py-5 mt-5">
                <div class="col-md-12 heading-section text-center">
                    <h2 class="mb-4">Services</h2>
                </div>
            </div>
            <div class="row">
                @if (!empty($services))
                @foreach ($services as $service)
                <div class="col-md-4 text-center d-flex" data-aos="zoom-in">
                    <div class="services-1 shadow z-hover">
                        <span class="icon">
                            <i class="{{$service->icon}}"></i>
                        </span>
                        <div class="desc">
                            <h3 class="mb-5">{{$service->title}}</h3>
                                    <div class="service-details">{!! $service->details !!}</div>
                        </div>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </section>
    @endif

    @if ($portfolioConfig['visibility']['projects'])
    <section class="ftco-section ftco-project" id="projects-section">
        <div class="container-fluid px-md-0">
            <div class="row no-gutters justify-content-center pb-5">
                <div class="col-md-12 heading-section text-center">
                    <h2 class="mb-4">Projects</h2>
                </div>
            </div>
            <div id="react-project-root" data-accentcolor="{{$accentColor}}" data-demomode="{{$demoMode}}" />
            <div class="mb-5"></div>
        </div>
    </section>
    @endif

    @if ($portfolioConfig['visibility']['contact'])
    <section class="ftco-section contact-section ftco-no-pb" id="contact-section" data-aos="zoom-in">
        <div class="container">
            <div class="row justify-content-center mb-5 pb-3">
                <div class="col-md-7 heading-section text-center">
                    <h2 class="mb-4">Contact Me</h2>
                </div>
            </div>

            <div class="row no-gutters block-9">
                <div class="col-md-6 order-md-last d-flex">
                    <form action="#" method="POST" id="contact-me-form" class="bg-light p-4 p-md-5 contact-form">
                        @csrf
                        <div class="hp-field" aria-hidden="true">
                            <label for="website">Website</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" aria-label="Your Name">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" id="email" name="email" placeholder="Your Email" aria-label="Your Email">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject" aria-label="Subject">
                        </div>
                        <div class="form-group">
                            <textarea id="body" name="body" cols="30" rows="7" class="form-control" placeholder="Body" aria-label="Message"></textarea>
                        </div>
                        @if (!empty($recaptchaSiteKey))
                            <div class="form-group">
                                <div class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKey }}"></div>
                            </div>
                        @endif
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-submit">Submit</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-6 d-flex">
                    <div class="align-self-stretch box text-center p-4">
                        <div class="card-body">
                            @if ($about->address)
                            <p class="mb-0"><strong>Address</strong></p>
                            <p class="pb-2 text-muted">{{$about->address }}</p>
                            @endif
                            @if ($about->email)
                            <p class="mb-0"><strong>Email</strong></p>
                            <p class="pb-2 text-muted">{{$about->email }}</p>
                            @endif
                            @if ($about->phone)
                            <p class="mb-0"><strong>Phone</strong></p>
                            <p class="pb-2 text-muted">{{$about->phone }}</p>
                            @endif
                            @if ($portfolioConfig['visibility']['cv'])
                                <p class="mb-0"><strong>Resume</strong></p>
                                <div class="resume-links">
                                    <a href="{{ $resumePdf }}" class="text-muted resume-link" download aria-label="Download resume PDF">
                                        <span class="fas fa-file-pdf" aria-hidden="true"></span>
                                        <span>PDF</span>
                                    </a>
                                    @if ($hasResumeDocx)
                                        <span class="text-muted">·</span>
                                        <a href="{{ $resumeDocx }}" class="text-muted resume-link" download aria-label="Download resume DOCX">
                                            <span class="fas fa-file-word" aria-hidden="true"></span>
                                            <span>DOCX</span>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    </main>
    <a href="#" class="back-to-top" aria-label="Back to top">
        <span class="bx bx-up-arrow-alt" aria-hidden="true"></span>
    </a>
    @if ($portfolioConfig['visibility']['cv'])
    <div class="mobile-resume-cta d-lg-none">
        <a href="{{ $resumePdf }}" class="btn btn-primary btn-sm" download>
            <span class="btn-icon"><span class="fas fa-file-download" aria-hidden="true"></span></span>
            Download Resume
        </a>
    </div>
    @endif
    @if ($portfolioConfig['visibility']['footer'])
    <footer class="footer">
        <div class="h4 title text-center text-muted">{{$about->name}}</div>
        @if (!empty($socialLinks))
            <div class="footer-social-links">
                @foreach ($socialLinks as $social)
                    @if (!empty($social->link))
                        <a href="{{$social->link}}" target="_blank" rel="noopener noreferrer" aria-label="{{$social->title}}" title="{{$social->title}}">
                            @if (!empty($social->iconClass))
                                <span class="{{$social->iconClass}}" aria-hidden="true"></span>
                                <span class="sr-only">{{$social->title}}</span>
                            @else
                                <span>{{$social->title}}</span>
                            @endif
                        </a>
                    @endif
                @endforeach
            </div>
        @endif
        <div class="text-center text-muted">
            <small>&copy; {{ date('Y') }} All rights reserved.</small>
        </div>
    </footer>
    @else
    <footer class="footer">
        @if (!empty($socialLinks))
            <div class="footer-social-links">
                @foreach ($socialLinks as $social)
                    @if (!empty($social->link))
                        <a href="{{$social->link}}" target="_blank" rel="noopener noreferrer" aria-label="{{$social->title}}" title="{{$social->title}}">
                            @if (!empty($social->iconClass))
                                <span class="{{$social->iconClass}}" aria-hidden="true"></span>
                                <span class="sr-only">{{$social->title}}</span>
                            @else
                                <span>{{$social->title}}</span>
                            @endif
                        </a>
                    @endif
                @endforeach
            </div>
        @endif
        <div class="text-center text-muted">
            <small>&copy; {{ date('Y') }} All rights reserved.</small>
        </div>
    </footer>
    @endif


    <script src="{{ asset('assets/common/lib/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/jquery-migrate/jquery-migrate.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/jquery.easing/jquery.easing.min.js') }}"></script>
    @if($about->taglines)
        <script src="{{ asset('assets/common/lib/typed/typed.js') }}"></script>
    @endif
    <script src="{{ asset('assets/common/lib/iziToast/js/iziToast.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/waypoints/jquery.waypoints.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/jquery.stellar/jquery.stellar.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/aos/aos.js') }}"></script>
    <script src="{{ asset('assets/common/lib/scrollax/scrollax.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/jquery.lazy/jquery.lazy.min.js') }}"></script>
    <script src="{{ asset('assets/themes/procyon/js/main.js') }}?v={{ filemtime(public_path('assets/themes/procyon/js/main.js')) }}"></script>
    <script src="{{ asset('js/client/frontend/roots/projects.js') }}"></script>
    <script>
        $(function() {
            $('.lazy').lazy();

            if ($('#szn-preloader').length) {
                $('#szn-preloader').delay(100).fadeOut('slow', function() {
                    $(this).remove();
                });
            }

            const mobileCta = document.querySelector('.mobile-resume-cta');
            let isCtaHidden = mobileCta ? mobileCta.classList.contains('is-hidden') : false;
            const updateMobileCtaOffset = () => {
                if (!mobileCta) {
                    return;
                }
                const isMobile = window.matchMedia('(max-width: 991.98px)').matches;
                if (!isMobile) {
                    document.documentElement.style.removeProperty('--mobile-cta-offset');
                    return;
                }
                if (isCtaHidden) {
                    document.documentElement.style.setProperty('--mobile-cta-offset', '0px');
                    return;
                }
                const height = mobileCta.offsetHeight || 0;
                document.documentElement.style.setProperty('--mobile-cta-offset', `${height}px`);
            };
            updateMobileCtaOffset();
            window.addEventListener('resize', updateMobileCtaOffset);

            if ($('#typed-strings').length) {
                @if($about->taglines)
                    var typedStrings = new Typed('#typed-strings', {
                        strings: {!! json_encode(json_decode($about->taglines)) !!},
                        typeSpeed: 70,
                        backSpeed: 40,
                        smartBackspace: true,
                        loop: true
                    });
                @endif
            }

            $('#contact-me-form').validate({
                rules: {
                    name: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true,
                    },
                    subject: {
                        required: true
                    },
                    body: {
                        required: true
                    }
                },
                submitHandler: function(form, event)
                {
                    const button = $('#contact-me-form #submit');
                    button.attr('disabled', true);
                    button.html(`<span class="content">
                        Sending <i class="fas fa-spinner fa-spin"></i>
                    </span>`);

                    $.ajax({
                        url: '{!! route('contact-me') !!}',
                        dataType: 'json',
                        data: $('#contact-me-form').serialize(),
                        type:'post',
                        success: function(response) {
                            if (response.status === 400) {
                                var errorArray = response.payload;
                                $.each( errorArray, function( key, errors ) {
                                    $.each( errors, function( key2, errorMessage ) {
                                        showNotification( errorMessage, 'error', false);
                                    });
                                });
                            } else if (response.status !== 200) {
                                showNotification(response.message, 'error', false);
                            } else if (response.status === 200) {
                                showNotification(response.message, 'success', false);
                                $('#contact-me-form').trigger('reset');
                                if (typeof grecaptcha !== 'undefined') {
                                    grecaptcha.reset();
                                }
                            }
                        },
                        error: function (jqXHR, exception) {
                            let messages = jqXHR.responseText;
                            if (typeof messages ==='string') {
                                messages = JSON.parse(messages).message;
                            }
                            
                           // Loop through each key in the object
                            Object.keys(messages).forEach(key => {
                                // Get the array of messages for each key
                                messages[key].forEach(message => {
                                    showNotification(message, 'error', false);
                                });
                            });
                        },
                        complete: function(data) {
                            button.attr('disabled', false);
                            button.html(`<span class="content">
                                Send <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M476 3.2L12.5 270.6c-18.1 10.4-15.8 35.6 2.2 43.2L121 358.4l287.3-253.2c5.5-4.9 13.3 2.6 8.6 8.3L176 407v80.5c0 23.6 28.5 32.9 42.5 15.8L282 426l124.6 52.2c14.2 6 30.4-2.9 33-18.2l72-432C515 7.8 493.3-6.8 476 3.2z">
                                    </path>
                                </svg>
                            </span>`);
                        }
                    });
                }
            });

            const footer = document.querySelector('footer.footer');
            if (mobileCta && footer && 'IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        const shouldHide = entry.isIntersecting;
                        mobileCta.classList.toggle('is-hidden', shouldHide);
                        if (shouldHide !== isCtaHidden) {
                            isCtaHidden = shouldHide;
                            updateMobileCtaOffset();
                        }
                    });
                }, { rootMargin: '0px 0px -64px 0px' });
                observer.observe(footer);
            }

            function showNotification(message = 'Something went wrong', type = 'error', sticky = false) {
                iziToast.show({
                    title: '',
                    message: message,
                    messageSize: 12,
                    position: 'topRight',
                    theme: 'dark',
                    pauseOnHover: true,
                    timeout: sticky === false ? 5000 : false,
                    progressBarColor: type === 'success' ? '#00ffb8' : '#ffafb4',
                    color: type === 'success' ? '#565c70' : '#565c70',
                    messageColor: type === 'success' ? '#00ffb8' : '#ffafb4',
                    icon: type === 'success' ? 'fas fa-check' : 'fas fa-times-circle'
                });
            }
        });
    </script>
    @if (!empty($portfolioConfig['script']['footer']) && $portfolioConfig['script']['footer'] != '')
    <script>
        {!!$portfolioConfig['script']['footer']!!}
    </script>
    @endif
    @include('common.pixelTracking')
</body>
</html>
