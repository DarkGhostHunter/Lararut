<?php

namespace DarkGhostHunter\Lararut;

/**
 * @method \Illuminate\Database\Eloquent\Collection|static[]|static|null findRut(mixed $rut, array $columns = [])
 * @method \Illuminate\Database\Eloquent\Collection|static[] findManyRut(\Illuminate\Contracts\Support\Arrayable|iterable|array $ruts, array $columns = [])
 * @method \Illuminate\Database\Eloquent\Collection|static[]|static findRutOrFail(mixed $rut, array $columns = [])
 * @method static findOrNew(mixed $rut, array $columns = [])
 * @method \Illuminate\Database\Eloquent\Builder whereRut(int|string|\DarkGhostHunter\RutUtils\Rut $rut)
 * @method \Illuminate\Database\Eloquent\Builder orWhereRut(int|string|\DarkGhostHunter\RutUtils\Rut $rut)
 */
trait HasRut
{
    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     * @internal
     */
    public static function bootHasRut(): void
    {
        static::addGlobalScope(new Scopes\RutScope());
    }

    /**
     * Get the name of the "rut number" column.
     *
     * @return string
     */
    public function getRutNumColumn(): string
    {
        return defined('static::RUT_NUM') ? static::RUT_NUM : 'rut_num';
    }

    /**
     * Get the fully qualified "deleted at" column.
     *
     * @return string
     */
    public function getQualifiedRutNumColumn(): string
    {
        return $this->qualifyColumn($this->getRutNumColumn());
    }
}