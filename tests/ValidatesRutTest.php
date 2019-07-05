<?php
namespace Tests;

use DarkGhostHunter\Lararut\ValidatesRut;
use Orchestra\Testbench\TestCase;

class ValidatesRutTest extends TestCase
{
    use RegistersPackage;

    public function testUsesUppercaseAsDefault()
    {
        $this->assertFalse(ValidatesRut::getLowercase());
    }

    public function testUseLowercase()
    {
        ValidatesRut::useLowercase();
        $this->assertTrue(ValidatesRut::getLowercase());
        ValidatesRut::useUppercase();
    }

    public function testUseUppercase()
    {
        ValidatesRut::useUppercase();
        $this->assertFalse(ValidatesRut::getLowercase());

        ValidatesRut::useLowercase();
        ValidatesRut::useUppercase();

        $this->assertFalse(ValidatesRut::getLowercase());
    }
}
