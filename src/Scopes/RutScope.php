<?php

namespace DarkGhostHunter\Lararut\Scopes;

use DarkGhostHunter\RutUtils\Rut;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Scope;

class RutScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected const EXTENSIONS = [
        'findRut',
        'findManyRut',
        'findRutOrFail',
        'findRutOrNew',
        'whereRut',
        'orWhereRut',
    ];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        // ..
    }

    /**
     * Extend the Eloquent Query Builder instance with macros.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     *
     * @return void
     */
    public function extend(Builder $builder): void
    {
        foreach (static::EXTENSIONS as $extension) {
            $builder->macro($extension, [__CLASS__, $extension]);
        }
    }

    /**
     * Find a model by its RUT number key.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  string|array|\Illuminate\Contracts\Support\Arrayable  $rut
     * @param  array  $columns
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|null
     * @throws \DarkGhostHunter\RutUtils\Exceptions\InvalidRutException
     */
    public static function findRut(Builder $builder, $rut, $columns = ['*'])
    {
        if (is_array($rut) || $rut instanceof Arrayable) {
            return static::findManyRut($builder, $rut, $columns);
        }

        return static::whereRut($builder, $rut)->first($columns);
    }


    /**
     * Find multiple models by their primary keys.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Contracts\Support\Arrayable|iterable|array  $ruts
     * @param  array  $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \DarkGhostHunter\RutUtils\Exceptions\InvalidRutException
     */
    public static function findManyRut(Builder $builder, $ruts, $columns = ['*']): Collection
    {
        $ruts = $ruts instanceof Arrayable ? $ruts->toArray() : $ruts;

        foreach ($ruts as $key => $id) {
            $ruts[$key] = Rut::makeOrThrow($id)->num;
        }

        return $builder->whereIn($builder->getModel()->getQualifiedRutNumColumn(), $ruts)->get($columns);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  mixed  $rut
     * @param  array  $columns
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \DarkGhostHunter\RutUtils\Exceptions\InvalidRutException
     */
    public static function findRutOrFail(Builder $builder, $rut, $columns = ['*'])
    {
        $result = static::findRut($builder, $rut, $columns);

        $rut = $rut instanceof Arrayable ? $rut->toArray() : $rut;

        if (is_array($rut)) {
            if (count($result) === count(array_unique($rut))) {
                return $result;
            }
        } elseif ($result !== null) {
            return $result;
        }

        throw (new ModelNotFoundException())->setModel(get_class($builder->getModel()), $rut);
    }

    /**
     * Find a model by its primary key or return fresh model instance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  mixed  $rut
     * @param  array  $columns
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \DarkGhostHunter\RutUtils\Exceptions\InvalidRutException
     */
    public static function findRutOrNew(Builder $builder, $rut, $columns = ['*']): Model
    {
        return static::findRut($builder, $rut, $columns) ?? $builder->newModelInstance();
    }

    /**
     * Adds a `WHERE` clause to the query with the RUT number
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  int|string|\DarkGhostHunter\RutUtils\Rut  $rut
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \DarkGhostHunter\RutUtils\Exceptions\InvalidRutException
     */
    public static function whereRut(Builder $builder, $rut): Builder
    {
        return $builder->where($builder->getModel()->getQualifiedRutNumColumn(), Rut::makeOrThrow($rut)->num);
    }

    /**
     * Adds a `WHERE` clause to the query with the RUT number
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  int|string|\DarkGhostHunter\RutUtils\Rut  $rut
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \DarkGhostHunter\RutUtils\Exceptions\InvalidRutException
     */
    public static function orWhereRut(Builder $builder, $rut): Builder
    {
        return $builder->orWhere($builder->getModel()->getQualifiedRutNumColumn(), Rut::makeOrThrow($rut)->num);
    }
}