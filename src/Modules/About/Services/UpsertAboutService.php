<?php

declare(strict_types=1);

namespace Src\Modules\About\Services;

use App\Helpers\CoreConstants;
use Illuminate\Support\Facades\Log;
use Src\Modules\About\Adapters\AboutDataAdapter;
use Src\Modules\About\DTO\AboutData;
use Src\Modules\About\Repositories\AboutRepositoryInterface;

final class UpsertAboutService
{
    public function __construct(
        private AboutRepositoryInterface $aboutRepository,
        private AboutDataAdapter $aboutDataAdapter
    ) {
    }

    public function handle(AboutData $aboutData): array
    {
        try {
            $newData = $this->buildPersistenceData($aboutData->toArray());
            $existing = $this->aboutRepository->first();

            if ($existing !== null) {
                $saved = $this->aboutRepository->update($existing->getKey(), $newData);
            } else {
                $newData['avatar'] = 'assets/common/img/avatar/default.png';
                $newData['cover'] = 'assets/common/img/cover/default.png';
                $saved = $this->aboutRepository->create($newData);
            }

            return array(
                'message' => 'Data is successfully updated',
                'payload' => $this->aboutDataAdapter->adapt($saved),
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

    private function buildPersistenceData(array $data): array
    {
        $newData = array(
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'job_title' => $data['job_title'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'description' => $data['description'] ?? null,
            'hero_subtitle' => $data['hero_subtitle'] ?? null,
        );

        if (!empty($data['seederCV'])) {
            $newData['cv'] = $data['seederCV'];
        }

        $newData['taglines'] = $this->jsonOrNull($this->sanitizeStringList($data['taglines'] ?? array()));
        $newData['about_highlights'] = $this->jsonOrNull($this->sanitizeStringList($data['about_highlights'] ?? array()));
        $newData['social_links'] = $this->jsonOrNull($this->sanitizeSocialLinks($data['social_links'] ?? array()));

        return $newData;
    }

    private function sanitizeStringList(array $items): array
    {
        return array_values(array_filter(array_map(static fn ($value) => trim((string) $value), $items), static fn ($value) => $value !== ''));
    }

    private function sanitizeSocialLinks(array $items): array
    {
        $sanitized = array();

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $link = trim((string) ($item['link'] ?? ''));
            $iconClass = trim((string) ($item['iconClass'] ?? ''));

            if ($title !== '' && $link !== '' && $iconClass !== '') {
                $sanitized[] = array('title' => $title, 'link' => $link, 'iconClass' => $iconClass);
            }
        }

        return $sanitized;
    }

    private function jsonOrNull(array $data): ?string
    {
        return count($data) > 0 ? json_encode($data) : null;
    }
}
