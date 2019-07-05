<?php

namespace Tests;

trait RegistersPackage
{
    /** @var \DarkGhostHunter\RutUtils\Rut */
    protected $rut;

    protected function getPackageProviders($app)
    {
        return ['DarkGhostHunter\Lararut\LararutServiceProvider'];
    }
}