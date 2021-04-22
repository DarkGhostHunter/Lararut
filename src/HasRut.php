<?php

namespace DarkGhostHunter\Lararut;

use DarkGhostHunter\RutUtils\Rut;

/**
 * @method \Illuminate\Database\Eloquent\Collection|static[]|static|null findRut(mixed $rut, array $columns = [])
 * @method \Illuminate\Database\Eloquent\Collection|static[] findManyRut(\Illuminate\Contracts\Support\Arrayable|iterable|array $ruts, array $columns = [])
 * @method \Illuminate\Database\Eloquent\Collection|static[]|static findRutOrFail(mixed $rut, array $columns = [])
 * @method static findOrNew(mixed $rut, array $columns = [])
 * @method \Illuminate\Database\Eloquent\Builder whereRut(int|string|\DarkGhostHunter\RutUtils\Rut $rut)
 * @method \Illuminate\Database\Eloquent\Builder orWhereRut(int|string|\DarkGhostHunter\RutUtils\Rut $rut)
 *
 * @property-read \DarkGhostHunter\RutUtils\Rut $rut
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
     * Get the name of the "rut verificarion digit" column.
     *
     * @return string
     */
    public function getRutVdColumn(): string
    {
        return defined('static::RUT_NUM') ? static::RUT_VD : 'rut_vd';
    }

    /**
     * Get the fully qualified "rut number" column.
     *
     * @return string
     */
    public function getQualifiedRutNumColumn(): string
    {
        return $this->qualifyColumn($this->getRutNumColumn());
    }

    /**
     * Returns the RUT of the user as a Rut instance.
     *
     * @return \DarkGhostHunter\RutUtils\Rut
     */
    protected function getRutAttribute(): Rut
    {
        return Rut::make($this->getAttribute($this->getRutNumColumn()), $this->getAttribute($this->getRutVdColumn()));
    }
}