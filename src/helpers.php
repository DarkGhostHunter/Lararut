<?php

use DarkGhostHunter\RutUtils\Rut;

if (!function_exists('rut')) {

    /**
     * Creates a new Rut instance
     *
     * @param mixed ...$rut
     * @return array|\DarkGhostHunter\RutUtils\Rut
     * @throws \DarkGhostHunter\RutUtils\Exceptions\InvalidRutException
     */
    function rut(...$rut) {
        return Rut::make(...$rut);
    }
}
