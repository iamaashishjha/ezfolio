<?php

namespace Database\Seeders;

use CoreConstants;
use App\Services\Contracts\AboutInterface;
use App\Services\Contracts\EducationInterface;
use App\Services\Contracts\ExperienceInterface;
use App\Services\Contracts\MessageInterface;
use App\Services\Contracts\PortfolioConfigInterface;
use App\Services\Contracts\ProjectInterface;
use App\Services\Contracts\ServiceInterface;
use App\Services\Contracts\SkillInterface;
use App\Services\Contracts\VisitorInterface;
use Config;
use Illuminate\Database\Seeder;
use Log;
use Str;
use Faker\Factory as Faker;

class PortfolioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $faker = Faker::create();

            $portfolioConfig = resolve(PortfolioConfigInterface::class);
            $about = resolve(AboutInterface::class);
            $education = resolve(EducationInterface::class);
            $skill = resolve(SkillInterface::class);
            $experience = resolve(ExperienceInterface::class);
            $project = resolve(ProjectInterface::class);
            $service = resolve(ServiceInterface::class);
            $visitor = resolve(VisitorInterface::class);
            $message = resolve(MessageInterface::class);

            //portfolio config table seed

            //template
            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__TEMPLATE,
                'setting_value' => 'procyon',
                'default_value' => 'procyon',
            ];
            $portfolioConfig->insertOrUpdate($data);

            //accent color
            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__ACCENT_COLOR,
                'setting_value' => '#1890ff',
                'default_value' => '#1890ff',
            ];
            $portfolioConfig->insertOrUpdate($data);

            //google analytics ID
            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__GOOGLE_ANALYTICS_ID,
                'setting_value' => Config::get('custom.demo_mode') ? 'G-PS8JF33VLD' : '',
                'default_value' => Config::get('custom.demo_mode') ? 'G-PS8JF33VLD' : '',
            ];
            $portfolioConfig->insertOrUpdate($data);

            //maintenance mode
            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__MAINTENANCE_MODE,
                'setting_value' => CoreConstants::FALSE,
                'default_value' => CoreConstants::FALSE,
            ];
            $portfolioConfig->insertOrUpdate($data);

            //visibility
            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_ABOUT,
                'setting_value' => CoreConstants::TRUE,
                'default_value' => CoreConstants::TRUE,
            ];
            $portfolioConfig->insertOrUpdate($data);

            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_SKILL,
                'setting_value' => CoreConstants::TRUE,
                'default_value' => CoreConstants::TRUE,
            ];
            $portfolioConfig->insertOrUpdate($data);

            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_EDUCATION,
                'setting_value' => CoreConstants::TRUE,
                'default_value' => CoreConstants::TRUE,
            ];
            $portfolioConfig->insertOrUpdate($data);

            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_EXPERIENCE,
                'setting_value' => CoreConstants::TRUE,
                'default_value' => CoreConstants::TRUE,
            ];
            $portfolioConfig->insertOrUpdate($data);

            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_PROJECT,
                'setting_value' => CoreConstants::TRUE,
                'default_value' => CoreConstants::TRUE,
            ];
            $portfolioConfig->insertOrUpdate($data);

            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_SERVICE,
                'setting_value' => CoreConstants::TRUE,
                'default_value' => CoreConstants::TRUE,
            ];
            $portfolioConfig->insertOrUpdate($data);

            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_CONTACT,
                'setting_value' => CoreConstants::TRUE,
                'default_value' => CoreConstants::TRUE,
            ];
            $portfolioConfig->insertOrUpdate($data);

            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_FOOTER,
                'setting_value' => CoreConstants::TRUE,
                'default_value' => CoreConstants::TRUE,
            ];
            $portfolioConfig->insertOrUpdate($data);

            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_CV,
                'setting_value' => CoreConstants::TRUE,
                'default_value' => CoreConstants::TRUE,
            ];
            $portfolioConfig->insertOrUpdate($data);

            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_SKILL_PROFICIENCY,
                'setting_value' => CoreConstants::TRUE,
                'default_value' => CoreConstants::TRUE,
            ];
            $portfolioConfig->insertOrUpdate($data);

            //header script
            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__SCRIPT_HEADER,
                'setting_value' => '',
                'default_value' => '',
            ];
            $portfolioConfig->insertOrUpdate($data);

            //footer script
            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__SCRIPT_FOOTER,
                'setting_value' => '',
                'default_value' => '',
            ];
            $portfolioConfig->insertOrUpdate($data);

            //meta title
            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__META_TITLE,
                'setting_value' => 'Aashish Jha — Backend Software Engineer',
                'default_value' => 'Aashish Jha — Backend Software Engineer',
            ];
            $portfolioConfig->insertOrUpdate($data);

            //meta author
            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__META_AUTHOR,
                'setting_value' => 'Aashish Jha',
                'default_value' => 'Aashish Jha',
            ];
            $portfolioConfig->insertOrUpdate($data);

            //meta description
            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__META_DESCRIPTION,
                'setting_value' => 'Backend software engineer building scalable APIs, microservices, and data platforms with Laravel, Lumen, Node.js, and modern databases.',
                'default_value' => 'Backend software engineer building scalable APIs, microservices, and data platforms with Laravel, Lumen, Node.js, and modern databases.',
            ];
            $portfolioConfig->insertOrUpdate($data);

            //meta image
            try {
                if (is_dir('public/assets/common/img/meta-image')) {
                    $dir = 'public/assets/common/img/meta-image';
                } else {
                    $dir = 'assets/common/img/meta-image';
                }
                $leave_files = array('.gitkeep');
                
                foreach (glob("$dir/*") as $file) {
                    if (!in_array(basename($file), $leave_files)) {
                        unlink($file);
                    }
                }
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
            }
            $data = [
                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__META_IMAGE,
                'setting_value' => 'assets/common/img/cover/default.png',
                'default_value' => 'assets/common/img/cover/default.png',
            ];
            $portfolioConfig->insertOrUpdate($data);


            //about table seed
            try {
                try {
                    //avatar
                    if (is_dir('public/assets/common/img/avatar')) {
                        $dir = 'public/assets/common/img/avatar';
                    } else {
                        $dir = 'assets/common/img/avatar';
                    }
                    $leave_files = array('.gitkeep');
                    
                    foreach (glob("$dir/*") as $file) {
                        if (!in_array(basename($file), $leave_files)) {
                            unlink($file);
                        }
                    }

                    if (is_dir('public/assets/common/img/avatar')) {
                        copy('public/assets/common/default/avatar/default.png', $dir.'/default.png');
                    } else {
                        copy('assets/common/default/avatar/default.png', $dir.'/default.png');
                    }
                } catch (\Throwable $th) {
                    Log::error($th->getMessage());
                }

                try {
                    //cover
                    if (is_dir('public/assets/common/img/cover')) {
                        $dir = 'public/assets/common/img/cover';
                    } else {
                        $dir = 'assets/common/img/cover';
                    }
                    $leave_files = array('.gitkeep');
                    
                    foreach (glob("$dir/*") as $file) {
                        if (!in_array(basename($file), $leave_files)) {
                            unlink($file);
                        }
                    }

                    if (is_dir('public/assets/common/img/cover')) {
                        copy('public/assets/common/default/cover/default.png', $dir.'/default.png');
                    } else {
                        copy('assets/common/default/cover/default.png', $dir.'/default.png');
                    }
                } catch (\Throwable $th) {
                    Log::error($th->getMessage());
                }

                try {
                    //cv
                    if (is_dir('public/assets/common/cv')) {
                        $dir = 'public/assets/common/cv';
                    } else {
                        $dir = 'assets/common/cv';
                    }

                    $leave_files = array('.gitkeep');
                    
                    foreach (glob("$dir/*") as $file) {
                        if (!in_array(basename($file), $leave_files)) {
                            unlink($file);
                        }
                    }
                    if (is_dir('public/assets/common/default/cv/')) {
                        copy('public/assets/common/default/cv/default.pdf', $dir.'/default.pdf');
                        if (file_exists('public/assets/common/default/cv/default.docx')) {
                            copy('public/assets/common/default/cv/default.docx', $dir.'/default.docx');
                        }
                    } else {
                        copy('assets/common/default/cv/default.pdf', $dir.'/default.pdf');
                        if (file_exists('assets/common/default/cv/default.docx')) {
                            copy('assets/common/default/cv/default.docx', $dir.'/default.docx');
                        }
                    }
                } catch (\Throwable $th) {
                    Log::error($th->getMessage());
                }
                
                $data = [
                    'name' => 'Aashish Jha',
                    'job_title' => 'Backend Software Engineer',
                    'email' => 'aashish.jha@example.com',
                    'avatar' => 'assets/common/img/avatar/default.png',
                    'cover' => 'assets/common/img/cover/default.png',
                    'phone' => null,
                    'address' => 'Remote',
                    'description' => '<p>Backend software engineer focused on building API-first platforms, scalable microservices, and data-heavy systems. I design secure Laravel/Lumen and Node.js services, optimize MySQL/PostgreSQL queries, and use Redis caching and queues to improve latency, throughput, and reliability.</p>',
                    'taglines' => ['Backend Software Engineer', 'Microservices & API Design', 'Laravel/Lumen + Node.js', 'Database Performance & Caching'],
                    'hero_subtitle' => 'Building scalable backend systems and microservices using Laravel, Lumen, Node.js, and modern databases.',
                    'about_highlights' => [
                        'Microservices & REST APIs',
                        'DB Optimization (MySQL/Postgres)',
                        'Caching (Redis)',
                        'Security best practices',
                        'Laravel/Lumen + Node.js',
                    ],
                    'social_links' => [
                        [
                            'title' => 'LinkedIn',
                            'iconClass' => 'fab fa-linkedin-in',
                            'link' => 'https://www.linkedin.com'
                        ],
                        [
                            'title' => 'GitHub',
                            'iconClass' => 'fab fa-github',
                            'link' => 'https://github.com'
                        ],
                        [
                            'title' => 'Mail',
                            'iconClass' => 'far fa-envelope',
                            'link' => 'mailto:aashish.jha@example.com'
                        ],
                    ],
                    'seederCV' => 'assets/common/cv/default.pdf',
                ];
                $about->store($data);

                //education table seed
                try {
                    $data = [
                        'institution' => 'University of Colorado Boulder',
                        'period' => '2006-2010',
                        'degree' => 'Bachelor of Science',
                        'cgpa' => '4.00 out of 4.00',
                        'department' => 'Computer Science & Engineering',
                        'thesis' => 'Web Development Track'
                    ];
                    $education->store($data);

                    $data = [
                        'institution' => 'James Buchanan High School',
                        'period' => '2002-2006',
                        'degree' => 'Technology Magnet Program',
                        'cgpa' => '3.75 out of 4.00',
                        'department' => null,
                        'thesis' => null
                    ];
                    $education->store($data);
                } catch (\Throwable $th) {
                    Log::error($th->getMessage());
                }
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
            }

            //skill table seed
            try {
                $data = [
                    'name' => 'Laravel',
                    'proficiency' => '92'
                ];
                $skill->store($data);

                $data = [
                    'name' => 'Lumen',
                    'proficiency' => '88'
                ];
                $skill->store($data);

                $data = [
                    'name' => 'PHP',
                    'proficiency' => '90'
                ];
                $skill->store($data);

                $data = [
                    'name' => 'Node.js',
                    'proficiency' => '85'
                ];
                $skill->store($data);

                $data = [
                    'name' => 'Express.js',
                    'proficiency' => '80'
                ];
                $skill->store($data);

                $data = [
                    'name' => 'REST APIs',
                    'proficiency' => '90'
                ];
                $skill->store($data);

                $data = [
                    'name' => 'Microservices',
                    'proficiency' => '88'
                ];
                $skill->store($data);

                $data = [
                    'name' => 'MySQL',
                    'proficiency' => '90'
                ];
                $skill->store($data);

                $data = [
                    'name' => 'PostgreSQL',
                    'proficiency' => '88'
                ];
                $skill->store($data);

                $data = [
                    'name' => 'MongoDB',
                    'proficiency' => '80'
                ];
                $skill->store($data);

                $data = [
                    'name' => 'Redis',
                    'proficiency' => '85'
                ];
                $skill->store($data);

                $data = [
                    'name' => 'JavaScript',
                    'proficiency' => '78'
                ];
                $skill->store($data);

                $data = [
                    'name' => 'React.js',
                    'proficiency' => '72'
                ];
                $skill->store($data);

                $data = [
                    'name' => 'Vue.js',
                    'proficiency' => '70'
                ];
                $skill->store($data);

                $data = [
                    'name' => 'Docker',
                    'proficiency' => '85'
                ];
                $skill->store($data);

                $data = [
                    'name' => 'CI/CD',
                    'proficiency' => '80'
                ];
                $skill->store($data);

                $data = [
                    'name' => 'Git',
                    'proficiency' => '85'
                ];
                $skill->store($data);

                $data = [
                    'name' => 'Nginx',
                    'proficiency' => '78'
                ];
                $skill->store($data);
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
            }

            //experience table seed
            try {
                $data = [
                    'company' => 'NimbusPay',
                    'period' => '2021-Present',
                    'position' => 'Senior Backend Engineer',
                    'details' => '<ul><li>Led migration from a monolith to Laravel/Lumen microservices with versioned APIs and shared contracts.</li><li>Improved API response time by X% and reduced p95 latency by Y ms through query tuning and Redis caching.</li><li>Implemented JWT auth, rate limiting, and audit logging to meet security and compliance requirements.</li></ul>'
                ];
                $experience->store($data);

                $data = [
                    'company' => 'DataVista Analytics',
                    'period' => '2019-2021',
                    'position' => 'Backend Engineer',
                    'details' => '<ul><li>Built a reporting and analytics service with Node.js and PostgreSQL for multi-tenant dashboards.</li><li>Designed ETL jobs and materialized views to cut report generation time by X%.</li><li>Containerized services with Docker and standardized CI/CD pipelines.</li></ul>'
                ];
                $experience->store($data);

                $data = [
                    'company' => 'CloudStream Media',
                    'period' => '2017-2019',
                    'position' => 'Software Engineer (Backend)',
                    'details' => '<ul><li>Integrated media ingestion workflows and webhook-based processing pipelines.</li><li>Delivered scalable REST APIs for media metadata, access control, and search.</li><li>Instrumented monitoring to improve uptime to X% with faster incident response.</li></ul>'
                ];
                $experience->store($data);
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
            }

            //project table seed
            try {
                try {
                    //images
                    if (is_dir('public/assets/common/img/projects')) {
                        $dir = 'public/assets/common/img/projects';
                    } else {
                        $dir = 'assets/common/img/projects';
                    }
                    
                    $leave_files = array('.gitkeep');
                    
                    foreach (glob("$dir/*") as $file) {
                        if (!in_array(basename($file), $leave_files)) {
                            unlink($file);
                        }
                    }
                } catch (\Throwable $th) {
                    Log::error($th->getMessage());
                }

                $data = [
                    'title' => 'Auth & API Gateway Service',
                    'categories' => ['microservices', 'api', 'security'],
                    'link' => null,
                    'details' => 'Problem: Multiple services needed consistent auth, rate limiting, and request validation.'."\n".
                        'Solution: Built an API gateway and auth service with JWT, policy enforcement, and request throttling.'."\n".
                        'Tech Stack: Laravel, Lumen, Redis, MySQL, Nginx, Docker.'."\n".
                        'Role: Designed service contracts, implemented gateway middleware, and owned deployment pipeline.'."\n".
                        'Impact: Reduced auth errors by X% and improved p95 latency by Y ms.',
                    'seeder_thumbnail' => 'assets/common/img/projects/demo_project_1_1.png',
                    'seeder_images' => [
                        'assets/common/img/projects/demo_project_1_1.png',
                        'assets/common/img/projects/demo_project_1_2.png'
                    ]
                ];
                if (is_dir('public/assets/common/default/projects')) {
                    copy('public/assets/common/default/projects/demo_project_1_1.png', $dir.'/demo_project_1_1.png');
                    copy('public/assets/common/default/projects/demo_project_1_2.png', $dir.'/demo_project_1_2.png');
                } else {
                    copy('assets/common/default/projects/demo_project_1_1.png', $dir.'/demo_project_1_1.png');
                    copy('assets/common/default/projects/demo_project_1_2.png', $dir.'/demo_project_1_2.png');
                }
                
                $project->store($data);

                $data = [
                    'title' => 'Reporting & Analytics Service',
                    'categories' => ['microservices', 'analytics', 'backend'],
                    'link' => null,
                    'details' => 'Problem: Business teams needed faster, self-serve reporting across large datasets.'."\n".
                        'Solution: Built a dedicated analytics service with pre-aggregations and incremental ETL.'."\n".
                        'Tech Stack: Node.js, PostgreSQL, Redis, Docker, AWS.'."\n".
                        'Role: Implemented data pipelines, query optimization, and API endpoints.'."\n".
                        'Impact: Cut report generation time by X% and reduced query cost by Y%.',
                    'seeder_thumbnail' => 'assets/common/img/projects/demo_project_2_1.png',
                    'seeder_images' => [
                        'assets/common/img/projects/demo_project_2_1.png',
                        'assets/common/img/projects/demo_project_2_2.png'
                    ]
                ];

                if (is_dir('public/assets/common/default/projects')) {
                    copy('public/assets/common/default/projects/demo_project_2_1.png', $dir.'/demo_project_2_1.png');
                    copy('public/assets/common/default/projects/demo_project_2_2.png', $dir.'/demo_project_2_2.png');
                } else {
                    copy('assets/common/default/projects/demo_project_2_1.png', $dir.'/demo_project_2_1.png');
                    copy('assets/common/default/projects/demo_project_2_2.png', $dir.'/demo_project_2_2.png');
                }

                $project->store($data);

                $data = [
                    'title' => 'Media Ingestion & Streaming Integration',
                    'categories' => ['microservices', 'integration', 'media'],
                    'link' => null,
                    'details' => 'Problem: Media uploads and processing were slow and error-prone across services.'."\n".
                        'Solution: Introduced an ingestion microservice with async processing and webhook callbacks.'."\n".
                        'Tech Stack: Laravel, Node.js, Redis, S3-compatible storage, Docker.'."\n".
                        'Role: Built ingestion APIs, queue workers, and monitoring dashboards.'."\n".
                        'Impact: Improved processing throughput by X% and reduced failure rate by Y%.',
                    'seeder_thumbnail' => 'assets/common/img/projects/demo_project_3_1.png',
                    'seeder_images' => [
                        'assets/common/img/projects/demo_project_3_1.png',
                        'assets/common/img/projects/demo_project_3_2.png'
                    ]
                ];
                
                if (is_dir('public/assets/common/default/projects')) {
                    copy('public/assets/common/default/projects/demo_project_3_1.png', $dir.'/demo_project_3_1.png');
                    copy('public/assets/common/default/projects/demo_project_3_2.png', $dir.'/demo_project_3_2.png');
                } else {
                    copy('assets/common/default/projects/demo_project_3_1.png', $dir.'/demo_project_3_1.png');
                    copy('assets/common/default/projects/demo_project_3_2.png', $dir.'/demo_project_3_2.png');
                }
                
                $project->store($data);
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
            }

            //service table seed
            try {
                $data = [
                    'title' => 'API Development',
                    'icon' => 'fas fa-code',
                    'details' => 'Designing secure, versioned REST APIs with clear contracts and performance budgets.'
                ];
                $service->store($data);

                $data = [
                    'title' => 'Microservices Architecture',
                    'icon' => 'fas fa-project-diagram',
                    'details' => 'Breaking monoliths into resilient services with event-driven communication.'
                ];
                $service->store($data);

                $data = [
                    'title' => 'Performance Optimization',
                    'icon' => 'fas fa-tachometer-alt',
                    'details' => 'Profiling queries, caching hot paths, and tuning infrastructure for low latency.'
                ];
                $service->store($data);
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
            }

            try {
                //visitor table seed
                foreach (range(1, 72) as $index) {
                    $data = [
                        'tracking_id' => Str::random(30),
                        'is_new' => $faker->boolean(60),
                        'ip' => $faker->ipv4,
                        'is_desktop' => $faker->boolean(70),
                        'browser' => $faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Opera', 'Edge']),
                        'platform' => $faker->randomElement(['Windows', 'OS X', 'AndroidOS', 'iOS']),
                        'location' => $faker->country,
                        'country_code' => $faker->countryCode,
                        'region' => $faker->state,
                        'region_code' => $faker->stateAbbr,
                        'city' => $faker->city,
                        'zip' => $faker->postcode,
                        'latitude' => $faker->latitude,
                        'longitude' => $faker->longitude,
                        'timezone' => $faker->timezone,
                        'created_at' => $faker->dateTimeThisMonth()->format('Y-m-d H:i:s'),
                    ];
                    $visitor->forceStore($data);
                }
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
            }

            try {
                //message table seed
                foreach (range(1, 17) as $index) {
                    $data = [
                        'name' => $faker->name(),
                        'email' => $faker->email,
                        'subject' => $faker->sentence(),
                        'body' => $faker->text(),
                        'replied' => $faker->boolean(60),
                        'created_at' => $faker->dateTimeThisMonth()->format('Y-m-d H:i:s'),
                    ];
                    $message->store($data);
                }
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }
}
