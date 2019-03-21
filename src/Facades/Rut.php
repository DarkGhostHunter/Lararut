<?php

namespace DarkGhostHunter\Lararut\Facades;

use DarkGhostHunter\RutUtils\Rut as RutAccessor;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string cleanRut(string $rut, bool $forceUppercase = true)
 * @method static bool validate(...$ruts)
 * @method static bool validateStrict(...$ruts)
 * @method static bool isEqual(...$ruts)
 * @method static array filter(...$ruts)
 * @method static \DarkGhostHunter\RutUtils\Rut rectify(int $num)
 * @method static bool isPerson(string $rut)
 * @method static bool isCompany(string $rut)
 *
 * @method static \DarkGhostHunter\RutUtils\Rut|array generate(int $iterations = 1, bool $unwrapSingle = true)
 * @method static \DarkGhostHunter\RutUtils\RutBuilder unique()
 * @method static \DarkGhostHunter\RutUtils\RutBuilder notUnique()
 * @method static \DarkGhostHunter\RutUtils\RutBuilder asCompany()
 * @method static \DarkGhostHunter\RutUtils\RutBuilder asPerson()
 * @method static \DarkGhostHunter\RutUtils\RutBuilder asRaw()
 * @method static \DarkGhostHunter\RutUtils\RutBuilder asString()
 * @method static \DarkGhostHunter\RutUtils\RutBuilder asObject()
 *
 * @method static \DarkGhostHunter\RutUtils\Rut|array make(...$ruts)
 * @method static \DarkGhostHunter\RutUtils\Rut|array makeValid(...$ruts)
 * @method static \DarkGhostHunter\RutUtils\Rut allUppercase()
 * @method static \DarkGhostHunter\RutUtils\Rut allLowercase()
 * @method static void setStringFormat(string $format)
 * @method static string getStringFormat()
 * @method string toRawString()
 * @method string toFormattedString()
 * @method array toArray()
 * @method string jsonSerialize()
 * @method string toJson()
 *
 */
class Rut extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return RutAccessor::class;
    }
}