<?php

namespace DarkGhostHunter\Lararut;

use DarkGhostHunter\RutUtils\Exceptions\InvalidRutException;
use DarkGhostHunter\RutUtils\Rut;
use DarkGhostHunter\RutUtils\RutHelper;
use Illuminate\Support\Arr;

class ValidatesRut
{
    /**
     * If the database rules should use lowercase on "K" verification digit
     *
     * @var bool
     */
    protected static $lowercase = false;

    /**
     * Use lowercase "k" for database rules
     *
     * @return void
     */
    public static function useLowercase()
    {
        self::$lowercase = true;
    }

    /**
     * Use uppercase "K" for database rules
     *
     * @return void
     */
    public static function useUppercase()
    {
        self::$lowercase = false;
    }

    /**
     * Get if the database rules are using lowercase or uppercase
     *
     * @return bool
     */
    public static function getLowercase()
    {
        return self::$lowercase;
    }
    
    /**
     * Returns if the RUTs are valid
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @param \Illuminate\Validation\Validator $validator
     * @return bool
     */
    public function validateRut($attribute, $value, $parameters, $validator)
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
    public function validateRutStrict($attribute, $value, $parameters, $validator)
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
    public function validateRutEqual($attribute, $value, $parameters, $validator)
    {
        $validator->requireParameterCount(1, $parameters, 'rut_equal');

        if (!is_string($value) || (!is_string($value) && !is_numeric($value))) {
            return false;
        }

        return RutHelper::validate([$value] + $parameters) && RutHelper::isEqual($value, $parameters[0]);
    }


    /**
     * Returns if the number of the RUT exist in the Database
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @param \Illuminate\Validation\Validator $validator
     * @return bool
     */
    public function validateNumExists($attribute, $value, $parameters, $validator)
    {
        $validator->requireParameterCount(1, $parameters, 'num_exists');

        try {
            $rut = Rut::makeValid($value);
        } catch (InvalidRutException $exception) {
            return false;
        }

        $parameters[1] = ! isset($parameters[1]) || $parameters[1] === 'NULL' ? $attribute . '_num' : $parameters[1];

        return $validator->validateExists($attribute, $rut->num, $parameters);
    }

    /**
     * Returns if the number of the RUT exist in the Database
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @param \Illuminate\Validation\Validator $validator
     * @return bool
     */
    public function validateNumUnique($attribute, $value, $parameters, $validator)
    {
        $validator->requireParameterCount(1, $parameters, 'num_unique');

        try {
            $rut = Rut::makeValid($value);
        } catch (InvalidRutException $exception) {
            return false;
        }

        $parameters[1] = ! isset($parameters[1]) || $parameters[1] === 'NULL' ? $attribute . '_num' : $parameters[1];

        return $validator->validateUnique($attribute, $rut->num, $parameters);
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
    public function validateRutExists($attribute, $value, $parameters, $validator)
    {
        $validator->requireParameterCount(1, $parameters, 'rut_exists');

        try {
            $rut = Rut::makeValid($value);
        } catch (InvalidRutException $exception) {
            return false;
        }

        // If the parameters doesn't include the columns for the number and verification
        // digit, we will assume it's the attribute name plus "_num" and "_vd" in the
        // target table. We will just put these into the parameters array and pass.
        $parameters[1] = ! isset($parameters[1]) || $parameters[1] === 'NULL' ? $attribute . '_num' : $parameters[1];
        $parameters[2] = ! isset($parameters[2]) || $parameters[2] === 'NULL' ? $attribute . '_vd' : $parameters[2];

        // We will add the second column as an extra "where" clause, and then take the
        // parameters array and rearrange it so the "validateExists" rule can digest
        // the rule parameters. We don't have to reinvent the wheel, we just use it.
        $parameters[] = Arr::pull($parameters, 2);
        $parameters[] = self::$lowercase ? strtolower($rut->vd) : strtoupper($rut->vd);

        return $validator->validateExists($attribute, $rut->num, $parameters);
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
    public function validateRutUnique($attribute, $value, $parameters, $validator)
    {
        $validator->requireParameterCount(1, $parameters, 'rut_unique');

        try {
            $rut = Rut::makeValid($value);
        } catch (InvalidRutException $exception) {
            return false;
        }

        // We will add the second column as an extra "where" clause, and then take the
        // parameters array and rearrange it so the "validateUnique" rule can digest
        // the rule parameters. We don't have to reinvent the wheel, we just use it.
        $extra = [Arr::pull($parameters, 2), self::$lowercase ? strtolower($rut->vd) : strtoupper($rut->vd)];
        $parameters = array_merge(array_pad($parameters, 4, null), $extra);

        // If the parameters doesn't include the columns for the number and verification
        // digit, we will assume it's the attribute name plus "_num" and "_vd" in the
        // target table. We will just put these into the parameters array and pass.
        $parameters[1] = ! isset($parameters[1]) || $parameters[1] === 'NULL' ? $attribute . '_num' : $parameters[1];
        $parameters[2] = ! isset($parameters[2]) || $parameters[2] === 'NULL' ? $attribute . '_vd' : $parameters[2];

        return $validator->validateUnique($attribute, $rut->num, $parameters);
    }
}