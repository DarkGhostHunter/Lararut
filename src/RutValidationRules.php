<?php

namespace DarkGhostHunter\Lararut;

use DarkGhostHunter\RutUtils\Exceptions\InvalidRutException;
use DarkGhostHunter\RutUtils\Rut;
use DarkGhostHunter\RutUtils\RutHelper;
use Illuminate\Support\Str;

class RutValidationRules
{
    /**
     * Returns if the RUTs are valid
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool
     */
    public function isRut($attribute, $value, $parameters, $validator)
    {
        return RutHelper::validate($value);
    }

    /**
     * Returns if the RUTs are valid and properly formatted
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool
     */
    public function isRutStrict($attribute, $value, $parameters, $validator)
    {
        return RutHelper::validateStrict($value);
    }

    /**
     * Returns if the RUT is equal to another
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool
     */
    public function isRutEqual($attribute, $value, $parameters, $validator)
    {
        try {
            return RutHelper::isEqual($value, $parameters[0]);
        } catch (InvalidRutException $exception) {
            return false;
        }
    }

    /**
     * Returns if the RUT exist in the Database
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool
     */
    public function rutExists($attribute, $value, $parameters, $validator)
    {
        try {
            $rut = Rut::makeValid($value);
        } catch (InvalidRutException $exception) {
            return false;
        }

        [$connection, $table] = Str::contains($parameters[0], '.')
            ? explode('.', $parameters[0], 2)
            : [null, $parameters[0]];

        $builder = app('db')
            ->connection($connection)
            ->table($table)
            ->where(trim($parameters[1], ' '), $rut->num)
            ->where(trim($parameters[2], ' '), $rut->vd);

        return $builder->exists();
    }
}