<?php

namespace Tests;

use DarkGhostHunter\RutUtils\Rut;
use DarkGhostHunter\RutUtils\RutBuilder;
use Orchestra\Testbench\TestCase;

class ServiceProviderTest extends TestCase
{

    /** @var Rut */
    protected $rut;

    protected function getPackageProviders($app)
    {
        return ['DarkGhostHunter\Lararut\LararutServiceProvider'];
    }

    protected function getPackageAliases($app)
    {
        return ['Rut' => 'DarkGhostHunter\Lararut\Facades\Rut'];
    }

    public function testRegistersRutService()
    {
        $this->assertInstanceOf(Rut::class, $this->app->make(Rut::class));
    }

    public function testRegistersFacade()
    {
        $this->assertInstanceOf(Rut::class, \Rut::make('247009094'));
        $this->assertInstanceOf(Rut::class, \Rut::getFacadeRoot());
    }
}
