<?php

namespace DarkGhostHunter\Lararut\Casts;

use DarkGhostHunter\RutUtils\Rut;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

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
        if (!$value instanceof Rut) {
            $value = Rut::make($value);
        }

        $attributes[$model->getRutNumColumn()] = $value->num;
        $attributes[$model->getRutVdColumn()] = $value->vd;

        return $attributes;
    }
}