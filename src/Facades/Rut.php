<?php

namespace DarkGhostHunter\Lararut\Facades;

use DarkGhostHunter\RutUtils\Rut as RutAccessor;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool validate(...$ruts)
 * @method static bool validateStrict(...$ruts)
 * @method static bool areEqual(string $rutA, string $rutB)
 * @method static array filter(...$ruts)
 * @method static Rut rectify(int $num)
 * @method static bool isPerson(string $rut)
 * @method static bool isCompany(string $rut)
 *
 * @method static array|Rut generate(int $iterations = 1, bool $unwrapSingle = true)
 * @method static \DarkGhostHunter\RutUtils\RutBuilder unique()
 * @method static \DarkGhostHunter\RutUtils\RutBuilder notUnique()
 * @method static \DarkGhostHunter\RutUtils\RutBuilder asCompany()
 * @method static \DarkGhostHunter\RutUtils\RutBuilder asPerson()
 * @method static \DarkGhostHunter\RutUtils\RutBuilder asRaw()
 * @method static \DarkGhostHunter\RutUtils\RutBuilder asString()
 * @method static \DarkGhostHunter\RutUtils\RutBuilder asObject()
 *
 * @method static array|Rut make(...$ruts)
 * @method static array|Rut makeValid(...$ruts)
 * @method static Rut allUppercase()
 * @method static Rut allLowercase()
 * @method string toRawString()
 * @method string toFormattedString()
 * @method array toArray()
 * @method string jsonSerialize()
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