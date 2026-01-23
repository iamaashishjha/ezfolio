<!--

=========================================================
* startbootstrap-resume - v6.0.1
=========================================================

* Product Page: https://startbootstrap.com/themes/resume/
* Copyright 2013-2020 Start Bootstrap LLC (https://startbootstrap.com)
* Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-resume/blob/master/LICENSE)

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

-->

@php
    $accentColor = $portfolioConfig['accentColor'];;
    $accentColorRGB = Utils::getRgbValue($accentColor);
    $resumePdf = $about->cv ?: 'assets/common/cv/default.pdf';
    $resumeDocx = preg_replace('/\\.pdf$/i', '.docx', $resumePdf);
    $hasResumeDocx = $resumeDocx && file_exists(public_path($resumeDocx));
    $linkedinUrl = null;
    if (!empty($about->social_links)) {
        foreach (json_decode($about->social_links) as $social) {
            if (
                (!empty($social->title) && stripos($social->title, 'linkedin') !== false) ||
                (!empty($social->iconClass) && stripos($social->iconClass, 'linkedin') !== false)
            ) {
                $linkedinUrl = $social->link;
                break;
            }
        }
    }
    $aboutHighlights = [
        'Microservices & REST APIs',
        'DB Optimization (MySQL/Postgres)',
        'Caching (Redis)',
        'Security best practices',
        'Laravel/Lumen + Node.js',
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
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta property="og:title" content="{{$portfolioConfig['seo']['title']}}"/>
    <meta property="title" content="{{$portfolioConfig['seo']['title']}}"/>
    <meta name="description" content="{{$portfolioConfig['seo']['description']}}" />
    <meta property="og:description" content="{{$portfolioConfig['seo']['description']}}"/>
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
    <link href="https://fonts.googleapis.com/css?family=Saira+Extra+Condensed:500,700" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/common/lib/mdi-icon/css/materialdesignicons.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/common/lib/fontawesome/css/all.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/common/lib/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/common/lib/iziToast/css/iziToast.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/themes/vega/css/styles.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/themes/vega/css/custom.css') }}" rel="stylesheet" />
    <style>
        :root {
          --z-accent-color: {{$accentColor}};
        }
        .bg-primary, .progress-bar {
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

        .text-primary {
            color: {{$accentColor.' !important'}};
        }

        .social-icons .social-icon:hover {
            text-decoration: none;
            background-color: {{$accentColor.' !important'}};
        }
    </style>
</head>
<body id="page-top">
    @include('common.preloader2')
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top" id="sideNav">
        <a class="navbar-brand js-scroll-trigger" href="#page-top">
            <span class="d-block d-lg-none">{{ $about->name }}</span>
            <span class="d-none d-lg-block"><img class="lazy img-fluid img-profile rounded-circle mx-auto mb-2" data-src="{{asset($about->avatar)}}" src="{{asset('assets/common/img/lazyloader.gif')}}" alt="Portrait of {{$about->name}}" /></span>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav">
                @if ($portfolioConfig['visibility']['about'])
                    <li class="nav-item"><a class="nav-link js-scroll-trigger" href="#about">About</a></li>
                @endif
                @if ($portfolioConfig['visibility']['experiences'])
                    <li class="nav-item"><a class="nav-link js-scroll-trigger" href="#experience">Experience</a></li>
                @endif
                @if ($portfolioConfig['visibility']['education'])
                    <li class="nav-item"><a class="nav-link js-scroll-trigger" href="#education">Education</a></li>
                @endif
                @if ($portfolioConfig['visibility']['skills'])
                    <li class="nav-item"><a class="nav-link js-scroll-trigger" href="#skills">Skills</a></li>
                @endif
                @if ($portfolioConfig['visibility']['projects'])
                    <li class="nav-item"><a class="nav-link js-scroll-trigger" href="#projects">Projects</a></li>
                @endif
                @if ($portfolioConfig['visibility']['services'])
                    <li class="nav-item"><a class="nav-link js-scroll-trigger" href="#services">Services</a></li>
                @endif
                @if ($portfolioConfig['visibility']['contact'])
                    <li class="nav-item"><a class="nav-link js-scroll-trigger" href="#contact">Contact</a></li>
                @endif
            </ul>
        </div>
    </nav>
    <!-- Page Content-->
    <div class="container-fluid p-0">
        @if ($portfolioConfig['visibility']['about'])
            <!-- About-->
            <section class="resume-section" id="about">
                <div class="resume-section-content">
                    <h1 class="mb-2" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
                        {{ $about->name }} <span class="hero-role">— Backend Software Engineer</span>
                    </h1>
                    <div class="subheading mb-3">
                        {{ $about->address ? $about->address . ' · ' : '' }} {{ $about->phone ? $about->phone . ' · ' : '' }}
                        <a href="mailto:{{ $about->email }}">{{ $about->email }}</a>
                    </div>
                    <p class="hero-subtitle mb-3">Building scalable backend systems and microservices using Laravel, Lumen, Node.js, and modern databases.</p>
                    <p>
                        <span id="typed-strings"></span>
                    </p>
                    @if ($portfolioConfig['visibility']['cv'])
                        <div class="hero-actions mb-3">
                            <a href="{{ $resumePdf }}" class="btn btn-primary btn-sm" download>Download Resume</a>
                            @if ($portfolioConfig['visibility']['projects'])
                                <a href="#projects" class="btn btn-outline-primary btn-sm">View Projects</a>
                            @endif
                        </div>
                    @endif
                    @if ($about->description)
                        <div class="pb-2 text-muted">
                            {!! $about->description !!}
                        </div>
                    @endif
                    <div class="about-highlights">
                        @foreach ($aboutHighlights as $highlight)
                            <span class="about-highlight">{{$highlight}}</span>
                        @endforeach
                    </div>
                        @if ($about->social_links)
                        <div class="social-icons" data-aos="zoom-in">
                            @foreach (json_decode($about->social_links) as $social)
                                <a class="social-icon" href="{{$social->link}}" target="_blank" rel="noreferrer" aria-label="{{$social->title}}">
                                    <i class="{{$social->iconClass}}"></i>
                                </a>
                            @endforeach
                        </div>
                        @endif
                </div>
            </section>
            <hr class="m-0" />
        @endif
        @if ($portfolioConfig['visibility']['cv'])
            <section class="resume-section" id="resume">
                <div class="resume-section-content">
                    <h2 class="mb-4">Resume</h2>
                    <p class="text-muted">Download the PDF or DOCX version for recruiters.</p>
                    <div class="resume-actions">
                        <a href="{{ $resumePdf }}" class="btn btn-primary btn-sm" download>Download Resume (PDF)</a>
                        @if ($hasResumeDocx)
                            <a href="{{ $resumeDocx }}" class="btn btn-outline-primary btn-sm" download>Download Resume (DOCX)</a>
                        @endif
                    </div>
                </div>
            </section>
            <hr class="m-0" />
        @endif
        @if ($portfolioConfig['visibility']['experiences'])
            <!-- Experience-->
            <section class="resume-section" id="experience">
                <div class="resume-section-content">
                    <h2 class="mb-5">Experience</h2>
                    @if ($experiences)
                        @foreach ($experiences as $experience)
                            <div class="d-flex flex-column flex-md-row justify-content-between mb-5" data-aos="fade-up">
                                <div class="flex-grow-1">
                                    {!! $experience->position ? '<h3 class="mb-0">'.$experience->position.'</h3>' : '' !!}
                                    {!! $experience->company ? '<div class="subheading mb-3">'.$experience->company.'</div>' : '' !!}
                                    @if ($experience->details)
                                        <div class="experience-details">{!! $experience->details !!}</div>
                                    @endif
                                </div>
                                {!! $experience->period ? '<div class="flex-shrink-0"><span class="text-primary">'.$experience->period.'</span></div>' : '' !!} 
                            </div>
                        @endforeach
                    @endif
                </div>
            </section>
            <hr class="m-0" />
        @endif
        @if ($portfolioConfig['visibility']['education'])
            <!-- Education-->
            <section class="resume-section" id="education">
                <div class="resume-section-content">
                    <h2 class="mb-5">Education</h2>
                    @if ($education)
                        @foreach ($education as $value)
                            <div class="d-flex flex-column flex-md-row justify-content-between mb-5" data-aos="fade-up">
                                <div class="flex-grow-1">
                                    {!! $value->institution ? '<h3 class="mb-0">'.$value->institution.'</h3>' : '' !!}
                                    {!! $value->degree ? '<div class="subheading mb-3">'.$value->degree.'</div>' : '' !!}
                                    {!! $value->department ? '<div>'.$value->department.'</div>' : '' !!}
                                    {!! $value->cgpa ? '<p>'.$value->cgpa.'</p>' : '' !!}
                                </div>
                                <div class="flex-shrink-0"><span class="text-primary">{!! $value->period ? $value->period : '' !!}</span></div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </section>
            <hr class="m-0" />
        @endif
        @if ($portfolioConfig['visibility']['skills'])
            <!-- Skills-->
            <section class="resume-section" id="skills">
                <div class="resume-section-content">
                    <h2 class="mb-5">Skills</h2>
                    <div class="row"> 
                        @php
                            $skillsCollection = collect($skills ?? []);
                            $skillGroups = [
                                'Backend' => ['laravel', 'lumen', 'php', 'node.js', 'express.js', 'rest apis', 'microservices'],
                                'Databases & Caching' => ['mysql', 'postgresql', 'mongodb', 'redis', 'elasticsearch'],
                                'Frontend' => ['javascript', 'react.js', 'vue.js', 'html', 'css'],
                                'Tools' => ['docker', 'ci/cd', 'git', 'nginx', 'aws'],
                            ];
                            $groupKeys = collect($skillGroups)->flatten()->all();
                            $otherSkills = $skillsCollection->filter(function ($skill) use ($groupKeys) {
                                return !in_array(strtolower($skill->name), $groupKeys);
                            });
                        @endphp
                        @foreach ($skillGroups as $groupName => $groupSkills)
                            @php
                                $groupItems = $skillsCollection->filter(function ($skill) use ($groupSkills) {
                                    return in_array(strtolower($skill->name), $groupSkills);
                                });
                            @endphp
                            @if ($groupItems->count())
                                <div class="col-12">
                                    <h3 class="skill-group-title">{{$groupName}}</h3>
                                </div>
                                @foreach ($groupItems as $skill)
                                    <div class="col-6 col-md-4 col-lg-2" data-aos="zoom-in">
                                        <div class="card z-hover skill-card text-center">
                                            <div class="card-body{{ !(int)$portfolioConfig['visibility']['skillProficiency'] ? ' center-this' : '' }}">
                                                @if ((int)$portfolioConfig['visibility']['skillProficiency'])
                                                    <div class="skill-progress-wrapper mb-2">
                                                        <div class="progress" data-percentage="{{$skill->proficiency}}">
                                                            <span class="progress-left">
                                                                <span class="progress-bar"></span>
                                                            </span>
                                                            <span class="progress-right">
                                                                <span class="progress-bar"></span>
                                                            </span>
                                                            <div class="progress-value">
                                                                <div>
                                                                    {{$skill->proficiency}}%
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div> 
                                                @endif
                                                {{$skill->name}}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                        @if ($otherSkills->count())
                            <div class="col-12">
                                <h3 class="skill-group-title">Other</h3>
                            </div>
                            @foreach ($otherSkills as $skill)
                                <div class="col-6 col-md-4 col-lg-2" data-aos="zoom-in">
                                    <div class="card z-hover skill-card text-center">
                                        <div class="card-body{{ !(int)$portfolioConfig['visibility']['skillProficiency'] ? ' center-this' : '' }}">
                                            @if ((int)$portfolioConfig['visibility']['skillProficiency'])
                                                <div class="skill-progress-wrapper mb-2">
                                                    <div class="progress" data-percentage="{{$skill->proficiency}}">
                                                        <span class="progress-left">
                                                            <span class="progress-bar"></span>
                                                        </span>
                                                        <span class="progress-right">
                                                            <span class="progress-bar"></span>
                                                        </span>
                                                        <div class="progress-value">
                                                            <div>
                                                                {{$skill->proficiency}}%
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> 
                                            @endif
                                            {{$skill->name}}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </section>
            <hr class="m-0" />
        @endif
        @if ($portfolioConfig['visibility']['projects'])
            <!-- projects-->
            <section class="resume-section" id="projects">
                <div class="resume-section-content">
                    <h2 class="mb-5">Projects</h2>
                    <div 
                        id="react-project-root" 
                        data-accentcolor="{{$accentColor}}" 
                        data-demomode="{{$demoMode}}"
                    />
                    <div class="mb-5"></div>
                    <hr class="m-0" />
                </div>
            </section>
        @endif
        @if ($portfolioConfig['visibility']['services'])
            <!-- Services-->
            <section class="resume-section" id="services">
                <div class="resume-section-content">
                    <h2 class="mb-5">Services</h2>
                    <div class="row"> 
                         @if (!empty($services))
                            @foreach ($services as $service)
                                <div class="col-lg-4 col-md-6 d-flex align-items-stretch icon-box-wrapper" data-aos="zoom-in">
                                    <div class="icon-box iconbox-blue z-hover">
                                        <div class="icon">
                                            <svg width="100" height="100" viewBox="0 0 600 600" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke="none" stroke-width="0" fill="#f5f5f5" d="M300,521.0016835830174C376.1290562159157,517.8887921683347,466.0731472004068,529.7835943286574,510.70327084640275,468.03025145048787C554.3714126377745,407.6079735673963,508.03601936045806,328.9844924480964,491.2728898941984,256.3432110539036C474.5976632858925,184.082847569629,479.9380746630129,96.60480741107993,416.23090153303,58.64404602377083C348.86323505073057,18.502131276798302,261.93793281208167,40.57373210992963,193.5410806939664,78.93577620505333C130.42746243093433,114.334589627462,98.30271207620316,179.96522072025542,76.75703585869454,249.04625023123273C51.97151888228291,328.5150500222984,13.704378332031375,421.85034740162234,66.52175969318436,486.19268352777647C119.04800174914682,550.1803526380478,217.28368757567262,524.383925680826,300,521.0016835830174"></path>
                                            </svg>
                                            <i class="{{$service->icon}}"></i>
                                        </div>
                                        <h4 class="my-3 text-muted">{{$service->title}}</h4>
                                        <p>{{$service->details}}</p>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </section>
            <hr class="m-0" />
        @endif
        @if ($portfolioConfig['visibility']['contact'])
            <!-- contact-->
            <section class="resume-section" id="contact" data-aos="zoom-in">
                <div class="resume-section-content">
                    <h2 class="mb-5">Contact</h2>
                    <div class="card border-0 mb-0">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card-body">
                                    <form action="#" id="contact-me-form" method="POST">
                                        @csrf
                                        <div class="hp-field" aria-hidden="true">
                                            <label for="website">Website</label>
                                            <input class="form-control" type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                                        </div>
                                        <div class="p pb-3">
                                            <strong>Send Me A Message</strong>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col">
                                                <div class="form-group">
                                                    <input class="form-control" type="text" id="name" name="name" placeholder="Name" aria-label="Name" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col">
                                                <div class="form-group">
                                                    <input class="form-control" type="text" id="subject" name="subject" placeholder="Subject" aria-label="Subject" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col">
                                                <div class="form-group">
                                                    <input class="form-control" type="email" id="email" name="email" placeholder="E-mail" aria-label="Email" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col">
                                                <div class="form-group">
                                                    <textarea class="form-control" id="body" name="body" placeholder="Body" aria-label="Message" required=""></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col">
                                                <button type="submit" class="submit-button">Send Message</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card-body">
                                    @if ($about->address)
                                        <p class="mb-0"><strong>Address</strong></p>
                                        <p class="pb-2 text-muted">{{$about->address }}</p>
                                    @endif
                                    @if ($about->email)
                                        <p class="mb-0"><strong>Email</strong></p>
                                        <p class="pb-2 text-muted">{{$about->email }}</p>
                                    @endif
                                    @if ($linkedinUrl)
                                        <p class="mb-0"><strong>LinkedIn</strong></p>
                                        <p class="pb-2 text-muted"><a href="{{$linkedinUrl}}" target="_blank" rel="noreferrer">{{$linkedinUrl}}</a></p>
                                    @endif
                                    @if ($about->phone)
                                        <p class="mb-0"><strong>Phone</strong></p>
                                        <p class="pb-2 text-muted">{{$about->phone }}</p>
                                    @endif
                                    @if ($portfolioConfig['visibility']['cv'])
                                        <p class="mb-0"><strong>Resume</strong></p>
                                        <div class="resume-links">
                                            <a href="{{ $resumePdf }}" class="text-muted" download>PDF</a>
                                            @if ($hasResumeDocx)
                                                <span class="text-muted">·</span>
                                                <a href="{{ $resumeDocx }}" class="text-muted" download>DOCX</a>
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
        @if ($portfolioConfig['visibility']['footer'])
            <footer class="footer">
                <div class="h4 title text-center text-muted">{{$about->name}}</div>
                <div class="text-center text-muted"><p>©{{ now()->year }} All rights reserved.</p></div>
            </footer>
        @endif
    </div>
    <!-- Bootstrap core JS-->
    <script src="{{ asset('assets/common/lib/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/bootstrap/js/bootstrap.min.js') }}"></script>
    <!-- Third party plugin JS-->
    <script src="{{ asset('assets/common/lib/jquery.easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/typed/typed.js') }}"></script>
    <script src="{{ asset('assets/common/lib/aos/aos.js') }}"></script>
    <script src="{{ asset('assets/common/lib/iziToast/js/iziToast.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/jquery.lazy/jquery.lazy.min.js') }}"></script>
    <!-- Core theme JS-->
    <script src="{{ asset('assets/themes/vega/js/scripts.js') }}"></script>
    <script src="{{ asset('js/client/frontend/roots/projects.js') }}"></script>
    <script>
        $(function() {
            if ($('#szn-preloader').length) {
                $('#szn-preloader').delay(100).fadeOut('slow', function() {
                    $(this).remove();
                });
            }

            AOS.init();

            $('.lazy').lazy();
            
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
                            }
                        },
                        error: function (jqXHR, exception) {
                            console.log(jqXHR);
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
