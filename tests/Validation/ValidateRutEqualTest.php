<?php

namespace Tests\Validation;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase;
use Tests\RegistersPackage;

class ValidateRutEqualTest extends TestCase
{
    use RegistersPackage;

    public function testRutEqual()
    {
        $validator = Validator::make([
            'rut' => '19.743.721-9',
        ], [
            'rut' => 'rut_equal:197437219'
        ]);

        static::assertFalse($validator->fails());
    }

    public function testRutEqualList()
    {
        $validator = Validator::make([
            'rut' => '19.743.721-9',
        ], [
            'rut' => 'rut_equal:197437219,19.743.721-9'
        ]);

        static::assertFalse($validator->fails());
    }

    public function testRutEqualFailsWhenNoArguments()
    {
        $this->expectException(InvalidArgumentException::class);

        $validator = Validator::make([
            'rut' => '19.743.721-9',
        ], [
            'rut' => 'rut_equal'
        ]);

        static::assertFalse($validator->fails());
    }

    public function testRutEqualFailsOnNotEqual()
    {
        $validator = Validator::make([
            'rut' => '19.743.721-9',
        ], [
            'rut' => 'rut_equal:143281450'
        ]);

        static::assertTrue($validator->fails());
    }

    public function testRutEqualFailsOnNotEqualOnArray()
    {
        $validator = Validator::make([
            'rut' => '19.743.721-9',
        ], [
            'rut' => 'rut_equal:143281450,19.743.721-9'
        ]);

        static::assertTrue($validator->fails());
    }

    public function testRutEqualFailsOnInvalidRut()
    {
        $validator = Validator::make([
            'rut' => '18.765.432-1',
        ], [
            'rut' => 'rut_equal:187654321'
        ]);

        static::assertTrue($validator->fails());
    }

    public function testRutEqualFailsOnArray()
    {
        $validator = Validator::make([
            'rut' => ['19.743.721-9','19.743.721-9']
        ], [
            'rut' => 'rut_equal:19.743.721-9'
        ]);

        static::assertTrue($validator->fails());
    }
}