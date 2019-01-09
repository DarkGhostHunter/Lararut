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
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @param \Illuminate\Validation\Validator $validator
     * @return bool
     */
    public function isRut($attribute, $value, $parameters, $validator)
    {
        return RutHelper::validate($value);
    }

    /**
     * Returns if the RUTs are valid and properly formatted
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @param \Illuminate\Validation\Validator $validator
     * @return bool
     */
    public function isRutStrict($attribute, $value, $parameters, $validator)
    {
        return RutHelper::validateStrict($value);
    }

    /**
     * Returns if the RUT is equal to another
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @param \Illuminate\Validation\Validator $validator
     * @return bool
     */
    public function isRutEqual($attribute, $value, $parameters, $validator)
    {
        return RutHelper::isEqual($value, $parameters[0]);
    }

    /**
     * Returns if the RUT exist in the Database
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @param \Illuminate\Validation\Validator $validator
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

        // Here we will call the DB and ask for the number and verification
        // digit. For the latter we will need to uppercase the column and
        // then ask, because there is no guarantee the VD is uppercase.
        return app('db')
            ->connection($connection)
            ->table($table)
            ->where(trim($parameters[1], ' '), $rut->num)
            ->whereRaw('upper("' .trim($parameters[2]) . '") = ?', [strtoupper($rut->vd)])
            ->exists();
    }
}