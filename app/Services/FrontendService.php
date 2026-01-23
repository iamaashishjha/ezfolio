<?php

namespace App\Services;

use App\Services\Contracts\AboutInterface;
use App\Services\Contracts\EducationInterface;
use App\Services\Contracts\ExperienceInterface;
use App\Services\Contracts\FrontendInterface;
use App\Services\Contracts\PortfolioConfigInterface;
use App\Services\Contracts\ProjectInterface;
use App\Services\Contracts\ServiceInterface;
use App\Services\Contracts\SkillInterface;
use CoreConstants;
use Log;
use Validator;

class FrontendService implements FrontendInterface
{
    /**
     * Create a new service instance
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get all data for frontend
     *
     * @return array
     */
    public function getAllData()
    {
        try {
            $data = [];

            //portfolio config
            $result = resolve(PortfolioConfigInterface::class)->getAllConfigData();
            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $data['portfolioConfig'] = $result['payload'];
            }

            //about
            $result = resolve(AboutInterface::class)->getAll();
            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $data['about'] = $result['payload'];
            }

            //skill
            $result = resolve(SkillInterface::class)->getAll();
            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $data['skills'] = $result['payload'];
            }

            //education
            $result = resolve(EducationInterface::class)->getAll();
            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $data['education'] = $result['payload'];
            }

            //experiences
            $result = resolve(ExperienceInterface::class)->getAll();
            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $data['experiences'] = $result['payload'];
            }

            //projects
            $result = resolve(ProjectInterface::class)->getAll();
            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $data['projects'] = $result['payload'];
            }

            //services
            $result = resolve(ServiceInterface::class)->getAll();
            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $data['services'] = $result['payload'];
            }

            $data['aboutHighlights'] = $this->buildAboutHighlights(
                $data['about'] ?? null,
                $data['skills'] ?? [],
                $data['services'] ?? []
            );
            
            return [
                'message' => 'Data is fetched successfully',
                'payload' => $data,
                'status' => CoreConstants::STATUS_CODE_SUCCESS
            ];
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status'  => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Get all projects
     *
     * @return array
     */
    public function getAllProjects()
    {
        try {
            $result = resolve(ProjectInterface::class)->getAll();

            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                return [
                    'message' => 'Projects are fetched successfully',
                    'payload' => $result['payload'],
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return $result;
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status'  => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Build about highlights from skills/services data.
     *
     * @param array|\Illuminate\Support\Collection $skills
     * @param array|\Illuminate\Support\Collection $services
     * @return array
     */
    private function buildAboutHighlights($about, $skills, $services)
    {
        if (!empty($about) && !empty($about->about_highlights)) {
            $decoded = json_decode($about->about_highlights, true);
            if (is_array($decoded) && count($decoded)) {
                return $decoded;
            }
        }

        $skillNames = collect($skills ?? [])->pluck('name')->map(function ($name) {
            return strtolower($name);
        });
        $serviceNames = collect($services ?? [])->pluck('title')->map(function ($title) {
            return strtolower($title);
        });

        $hasKeyword = function (array $keywords) use ($skillNames, $serviceNames) {
            foreach ($keywords as $keyword) {
                $keyword = strtolower($keyword);
                if ($skillNames->contains($keyword) || $serviceNames->contains($keyword)) {
                    return true;
                }
            }
            return false;
        };

        $aboutHighlights = [];

        if ($hasKeyword(['microservices', 'microservices architecture', 'rest apis', 'api development', 'api gateway'])) {
            $aboutHighlights[] = 'Microservices & REST APIs';
        }
        if ($hasKeyword(['mysql', 'postgresql', 'postgres', 'database', 'db optimization'])) {
            $aboutHighlights[] = 'DB Optimization (MySQL/Postgres)';
        }
        if ($hasKeyword(['redis', 'cache', 'caching'])) {
            $aboutHighlights[] = 'Caching (Redis)';
        }
        if ($hasKeyword(['security', 'jwt', 'oauth', 'auth'])) {
            $aboutHighlights[] = 'Security best practices';
        }
        if ($hasKeyword(['laravel', 'lumen', 'node.js', 'node', 'express.js'])) {
            $aboutHighlights[] = 'Laravel/Lumen + Node.js';
        }

        if (empty($aboutHighlights)) {
            $aboutHighlights = collect($skills ?? [])->take(5)->pluck('name')->all();
        }

        return $aboutHighlights;
    }
}
