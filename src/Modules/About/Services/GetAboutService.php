<?php

declare(strict_types=1);

namespace Src\Modules\About\Services;

use App\Helpers\CoreConstants;
use Illuminate\Support\Facades\Log;
use Src\Modules\About\Adapters\AboutDataAdapter;
use Src\Modules\About\Repositories\AboutRepositoryInterface;

final class GetAboutService
{
    public function __construct(
        private AboutRepositoryInterface $aboutRepository,
        private AboutDataAdapter $aboutDataAdapter
    ) {
    }

    public function handle(): array
    {
        try {
            $about = $this->aboutRepository->first();

            if ($about === null) {
                return array(
                    'message' => 'No result found',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_NOT_FOUND,
                    'meta' => array(),
                );
            }

            return array(
                'message' => 'Data is fetched successfully',
                'payload' => $this->aboutDataAdapter->adapt($about),
                'status' => CoreConstants::STATUS_CODE_SUCCESS,
                'meta' => array(),
            );
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());

            return array(
                'message' => 'Something went wrong',
                'payload' => null,
                'status' => CoreConstants::STATUS_CODE_ERROR,
                'meta' => array(),
            );
        }
    }
}
