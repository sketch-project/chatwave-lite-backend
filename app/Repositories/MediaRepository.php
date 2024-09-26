<?php

namespace App\Repositories;

use App\Models\Media;

class MediaRepository
{
    public function __construct(private Media $model) {}
}
