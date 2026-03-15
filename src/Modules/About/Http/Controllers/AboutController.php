<?php

declare(strict_types=1);

namespace Src\Modules\About\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Modules\About\DTO\AboutData;
use Src\Modules\About\Http\Requests\UpsertAboutRequest;
use Src\Modules\About\Http\Resources\AboutResource;
use Src\Modules\About\Services\GetAboutService;
use Src\Modules\About\Services\UpsertAboutService;

final class AboutController extends Controller
{
    public function __construct(
        private GetAboutService $getAboutService,
        private UpsertAboutService $upsertAboutService
    ) {
    }

    public function show(): JsonResponse
    {
        $result = $this->getAboutService->handle();

        return (new AboutResource($result))->response()->setStatusCode((int) $result['status']);
    }

    public function upsert(UpsertAboutRequest $request): JsonResponse
    {
        $result = $this->upsertAboutService->handle(AboutData::fromArray($request->validated()));

        return (new AboutResource($result))->response()->setStatusCode((int) $result['status']);
    }
}
