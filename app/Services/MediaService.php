<?php

namespace App\Services;

use App\Repositories\MediaRepository;

readonly class MediaService
{
    public function __construct(
        private MediaRepository $mediaRepository
    ) {}
}
