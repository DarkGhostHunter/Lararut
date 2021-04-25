<?php

namespace DarkGhostHunter\Lararut\Casts;

use DarkGhostHunter\RutUtils\Rut;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use RuntimeException;

class CastRut implements CastsAttributes
{
    /**
     * Transform the attribute from the underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     *
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        $this->ensureModelHasRut($model);

        return Rut::make($attributes[$model->getRutNumColumn()], $attributes[$model->getRutVdColumn()]);
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     *
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        $this->ensureModelHasRut($model);

        if (!$value instanceof Rut) {
            $value = Rut::make($value);
        }

        $attributes[$model->getRutNumColumn()] = $value->num;
        $attributes[$model->getRutVdColumn()] = $value->vd;

        return $attributes;
    }

    /**
     * Stops if the model has no methods to get the RUT columns.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    protected function ensureModelHasRut($model)
    {
        if (! method_exists($model, 'getRutNumColumn') || ! method_exists($model, 'getRutVdColumn') ) {
            throw new RuntimeException("The " . get_class($model) . 'must include the `ScopesRut` trait.');
        }
    }
}