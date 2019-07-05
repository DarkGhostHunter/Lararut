<?php

namespace Tests\Validation;

use DarkGhostHunter\RutUtils\RutHelper;
use Illuminate\Support\Facades\Validator;
use Orchestra\Testbench\TestCase;
use Tests\PreparesDatabase;
use Tests\RegistersPackage;

class ValidateRutStrictTest extends TestCase
{
    use RegistersPackage;

    public function testRutStrict()
    {
        $validator = Validator::make([
            'rut_1' => '14.328.145-0',
            'rut_2' => '19.743.721-9',
        ], [
            'rut_1' => 'rut_strict',
            'rut_2' => 'rut_strict',
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRutStrictInArray()
    {
        $validator = Validator::make([
            'rut' => ['14.328.145-0', '19.743.721-9']
        ], [
            'rut' => 'rut_strict',
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRutStrictFailsOnInvalidFormat()
    {
        $validator = Validator::make([
            'rut' => '14328145-0'
        ], [
            'rut' => 'rut_strict'
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testRutFailsOnInvalidRut()
    {
        $validator = Validator::make([
            'rut' => '14.328.145-K'
        ], [
            'rut' => 'rut_strict'
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testRutStrictFailsOnSingleInvalidFormatRutArray()
    {
        $validator = Validator::make([
            'rut' => ['14328145-K', '14.328.145-0', '19.743.721-9']
        ], [
            'rut' => 'rut_strict'
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testRutStrictFailsOnAllInvalidFormatRutArray()
    {
        $validator = Validator::make([
            'rut' => ['14.328.145-0', '19.743.721-9', '19743721-9']
        ], [
            'rut' => 'rut_strict'
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testRutFailsOnAllRutArrayWithEmptyChild()
    {
        $validator = Validator::make([
            'rut' => ['14.328.145-0', '19.743.721-9', '']
        ], [
            'rut' => 'rut_strict'
        ]);

        $this->assertTrue($validator->fails());
    }
}