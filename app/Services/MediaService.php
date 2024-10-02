<?php

namespace App\Services;

use App\Models\Media;
use App\Repositories\MediaRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

readonly class MediaService
{
    public function __construct(
        private MediaRepository $mediaRepository
    ) {}

    public function create($file, $options = []): Media
    {
        $mediaPath = 'media/' . date('Y/m');
        if ($file instanceof UploadedFile) {
            if ($file->getError() != UPLOAD_ERR_OK) {
                throw new UploadException($file->getErrorMessage());
            }
            if ($path = $file->store($mediaPath)) {
                return $this->mediaRepository->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }
        if (is_string($file) && preg_match('/^data:(\w+\/\w+);base64,/', $file, $type)) {
            // Remove the metadata part and decode the base64 string
            $fileData = base64_decode(preg_replace('/^data:\w+\/\w+;base64,/', '', $file));

            // Get the file extension based on the MIME type
            $fileExtension = explode('/', $type[1])[1];

            $fileName = ($options['file_name'] ?? uniqid()) . '.' . $fileExtension;

            $path = $mediaPath . '/' . $fileName;

            if (Storage::put($path, $fileData)) {
                return $this->mediaRepository->create([
                    'file_path' => $path,
                    'file_name' => $fileName,
                    'mime_type' => Storage::mimeType($path),
                ]);
            }
        }

        throw new UploadException('Cannot upload file');
    }
}
