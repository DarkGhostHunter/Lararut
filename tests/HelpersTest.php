<?php

namespace Tests;

use DarkGhostHunter\RutUtils\Rut;
use Orchestra\Testbench\TestCase;

class HelpersTest extends TestCase
{

    /** @var Rut */
    protected $rut;

    protected function getPackageProviders($app)
    {
        return ['DarkGhostHunter\Lararut\LararutServiceProvider'];
    }

    protected function setUp() : void
    {
        parent::setUp();

        // Load the helper manually
        include(__DIR__.'/../src/helpers.php');
    }


    public function testRutHelper()
    {
        $ruts = Rut::make('66123136K', 247009094);

        $rutsHelper = rut('66123136K', 247009094);

        $this->assertEquals($ruts, $rutsHelper);
    }
}