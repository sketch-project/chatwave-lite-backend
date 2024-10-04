<?php

namespace App\Repositories;

use App\Models\Media;

readonly class MediaRepository
{
    public function __construct(private Media $model) {}

    public function create($data)
    {
        return $this->model->newInstance()->create($data)->refresh();
    }
}
