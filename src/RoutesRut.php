<?php

namespace DarkGhostHunter\Lararut;

use DarkGhostHunter\RutUtils\Exceptions\InvalidRutException;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

trait RoutesRut
{
    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        if ($field === 'rut') {
            if (!in_array(HasRut::class, class_uses_recursive($this), true)) {
                throw new RuntimeException('The model ' . get_class($this) . ' must use the `HasRut` trait.');
            }

            try {
                return $this->findRut($value);
            } catch (InvalidRutException $e) {
                return null;
            }
        }

        return parent::resolveRouteBinding($value, $field);
    }
}