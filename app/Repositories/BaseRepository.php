<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all data from model.
     *
     * @return mixed
     */
    public function getAll(?Request $request = null, $options = null)
    {
        return $this->model->get();
    }

    /**
     * Get all paginated data.
     *
     * @return mixed
     */
    public function getAllPaginated(?Request $request = null, $options = null)
    {
        return $this->model->paginate();
    }

    public function getById($id)
    {
        return $this->model->find($id);
    }

    public function getByIds(array $ids)
    {
        return $this->model->findMany($ids);
    }

    /**
     * Default create model.
     */
    public function create(array $data): mixed
    {
        return $this->model->create($data)->refresh();
    }

    /**
     * Default update model.
     *
     * @param  null  $data
     */
    public function update($model, $data = null): bool
    {
        if ($model instanceof Model) {
            if (is_null($data)) {
                return $model->save();
            }

            return $model->fill($data)->save();
        }

        if (is_array($model) && is_null($data)) {
            return $this->model->update($model);
        }

        if (is_array($model)) {
            if (array_is_list($model)) {
                return $this->model->whereIn($this->model->getKeyName(), $model)->update($data);
            } else {
                return $this->model->where(array_map(function ($item, $key) {
                    return [$key, '=', $item];
                }, $model))->update($data);
            }
        }

        return $this->model->where($this->model->getKeyName(), $model)->update($data);
    }

    public function save($model)
    {
        return $model->save();
    }

    /**
     * Default delete model.
     */
    public function delete($model): bool
    {
        if ($model instanceof Model) {
            return (bool) $model->delete();
        }

        return $this->model->destroy($model) > 0;
    }

    /**
     * Provides direct access to method in the repository (if available).
     *
     * @return mixed
     */
    public function __call(string $name, array $params)
    {
        $repository = $this->model;

        return $repository->{$name}(...$params);
    }
}
