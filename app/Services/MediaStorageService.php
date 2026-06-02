<?php

namespace App\Services;

use App\Helpers\MediaUrl;
use App\Models\MediaFile;
use CoreConstants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Log;

class MediaStorageService
{
    /**
     * @var MediaFile
     */
    private $mediaFileModel;

    /**
     * @param MediaFile $mediaFile
     * @return void
     */
    public function __construct(MediaFile $mediaFile)
    {
        $this->mediaFileModel = $mediaFile;
    }

    /**
     * Upload a file and persist a media_files reference.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param array $options
     * @return array
     */
    public function upload(UploadedFile $file, string $directory, array $options = [])
    {
        try {
            $disk = isset($options['disk']) ? $options['disk'] : config('filesystems.media_disk', 'minio');
            $extension = $file->extension() ? $file->extension() : $file->getClientOriginalExtension();
            $extension = $extension ? strtolower($extension) : 'bin';
            $fileName = Str::uuid()->toString() . '.' . $extension;
            $path = trim($directory, '/') . '/' . $fileName;

            $putOptions = ['visibility' => isset($options['visibility']) ? $options['visibility'] : 'public'];
            $stored = Storage::disk($disk)->putFileAs(trim($directory, '/'), $file, $fileName, $putOptions);

            if (!$stored) {
                return [
                    'message' => 'File could not be saved',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR,
                ];
            }

            $url = $this->resolveUrl($disk, $path);
            $bucket = config('filesystems.disks.' . $disk . '.bucket');
            $owner = isset($options['owner']) && ($options['owner'] instanceof Model) ? $options['owner'] : null;
            $collection = isset($options['collection']) ? $options['collection'] : null;
            $metadata = isset($options['metadata']) && is_array($options['metadata']) ? $options['metadata'] : [];
            $size = $file->getSize();

            $reference = $this->mediaFileModel->updateOrCreate(
                [
                    'disk' => $disk,
                    'path' => $path,
                ],
                [
                    'owner_type' => $owner ? get_class($owner) : null,
                    'owner_id' => $owner ? $owner->getKey() : null,
                    'collection' => $collection,
                    'bucket' => $bucket,
                    'url' => $url,
                    'mime_type' => $file->getClientMimeType(),
                    'extension' => $extension,
                    'size' => is_numeric($size) ? (int) $size : null,
                    'metadata' => $metadata ?: null,
                ]
            );

            return [
                'message' => 'File is successfully saved',
                'payload' => [
                    'disk' => $disk,
                    'path' => $path,
                    'url' => $url,
                    'reference' => $reference,
                ],
                'status' => CoreConstants::STATUS_CODE_SUCCESS,
            ];
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR,
            ];
        }
    }

    /**
     * Delete an object + DB reference by storage path.
     *
     * @param string|null $path
     * @param string|null $disk
     * @return void
     */
    public function deleteByPath(?string $path, ?string $disk = null)
    {
        if (!$path) {
            return;
        }

        if (Str::startsWith($path, 'http://') || Str::startsWith($path, 'https://')) {
            return;
        }

        if (Str::startsWith($path, 'assets/')) {
            try {
                $publicPath = public_path($path);
                if (file_exists($publicPath)) {
                    unlink($publicPath);
                }
            } catch (\Throwable $th) {
                Log::warning($th->getMessage());
            }

            $this->mediaFileModel->where('path', $path)->delete();
            return;
        }

        $disk = $disk ?: config('filesystems.media_disk', 'minio');

        try {
            Storage::disk($disk)->delete($path);
        } catch (\Throwable $th) {
            Log::warning($th->getMessage());
        }

        $this->mediaFileModel->where('disk', $disk)->where('path', $path)->delete();
    }

    /**
     * Resolve a public URL for the given disk and path.
     *
     * @param string $disk
     * @param string $path
     * @return string|null
     */
    public function resolveUrl(string $disk, string $path)
    {
        if (!$path) {
            return null;
        }

        if (Str::startsWith($path, 'http://') || Str::startsWith($path, 'https://')) {
            return $path;
        }

        if (Str::startsWith($path, 'assets/')) {
            return asset($path);
        }

        if ($disk === 'public') {
            return Storage::disk('public')->url($path);
        }

        try {
            return MediaUrl::forPath($path);
        } catch (\Throwable $th) {
            Log::warning($th->getMessage());
            return asset($path);
        }
    }

    /**
     * Attach an existing media path to a model owner and collection.
     *
     * @param string|null $path
     * @param Model|null $owner
     * @param string|null $collection
     * @param string|null $disk
     * @return void
     */
    public function attachToOwner(?string $path, ?Model $owner, ?string $collection = null, ?string $disk = null)
    {
        if (!$path || !$owner) {
            return;
        }

        $disk = $disk ?: config('filesystems.media_disk', 'minio');

        $this->mediaFileModel
            ->where('disk', $disk)
            ->where('path', $path)
            ->update([
                'owner_type' => get_class($owner),
                'owner_id' => $owner->getKey(),
                'collection' => $collection,
            ]);
    }
}
