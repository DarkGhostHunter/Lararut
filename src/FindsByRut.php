<?php

namespace DarkGhostHunter\Lararut;

use DarkGhostHunter\RutUtils\RutHelper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @deprecated  Use the `HasRut` trait.
 */
trait FindsByRut
{
    /**
     * Find a model by its RUT number key.
     *
     * @param  string|array|\Illuminate\Contracts\Support\Arrayable  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function find($id, $columns = ['*'])
    {
        if (is_array($id) || $id instanceof Arrayable) {
            return $this->findMany($id, $columns);
        }

        $id = RutHelper::separateRut($id)[0];

        return $this->forwardCallTo($this->newQuery(), __FUNCTION__, [$id, $columns]);
    }


    /**
     * Find multiple models by their primary keys.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $ids
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findMany($ids, $columns = ['*'])
    {
        $ids = $ids instanceof Arrayable ? $ids->toArray() : $ids;

        foreach ($ids as &$id) {
            $id = RutHelper::separateRut($id)[0];
        }

        return $this->forwardCallTo($this->newQuery(), __FUNCTION__, [$ids, $columns]);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static|static[]
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail($id, $columns = ['*'])
    {
        $result = $this->find($id, $columns);

        if (is_array($id)) {
            if (count($result) === count(array_unique($id))) {
                return $result;
            }
        } elseif ($result !== null) {
            return $result;
        }

        throw (new ModelNotFoundException)->setModel(
            get_class($this), $id
        );
    }

    /**
     * Find a model by its primary key or return fresh model instance.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function findOrNew($id, $columns = ['*'])
    {
        if (($model = $this->find($id, $columns)) !== null) {
            return $model;
        }

        return $this->newQuery()->newModelInstance();
    }
}