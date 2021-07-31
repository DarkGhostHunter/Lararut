<?php

namespace Tests\Validation;

use Illuminate\Support\Facades\Validator;
use Orchestra\Testbench\TestCase;
use Tests\RegistersPackage;

class ValidateRutStrictTest extends TestCase
{
    use RegistersPackage;

    public function testRutStrict(): void
    {
        $validator = Validator::make([
            'rut_1' => '14.328.145-0',
            'rut_2' => '19.743.721-9',
        ], [
            'rut_1' => 'rut_strict',
            'rut_2' => 'rut_strict',
        ]);

        static::assertFalse($validator->fails());
    }

    public function testRutStrictInArray(): void
    {
        $validator = Validator::make([
            'rut' => ['14.328.145-0', '19.743.721-9']
        ], [
            'rut' => 'rut_strict',
        ]);

        static::assertFalse($validator->fails());
    }

    public function testRutStrictFailsOnInvalidFormat(): void
    {
        $validator = Validator::make([
            'rut' => '14328145-0'
        ], [
            'rut' => 'rut_strict'
        ]);

        static::assertTrue($validator->fails());
    }

    public function testReturnsMessage(): void
    {
        $validator = Validator::make([
            'rut' => '14328145-0'
        ], [
            'rut' => 'rut_strict'
        ]);

        static::assertEquals('The rut must be a properly formatted RUT.', $validator->getMessageBag()->first('rut'));
    }

    public function testRutFailsOnInvalidRut(): void
    {
        $validator = Validator::make([
            'rut' => '14.328.145-K'
        ], [
            'rut' => 'rut_strict'
        ]);

        static::assertTrue($validator->fails());
    }

    public function testRutStrictFailsOnSingleInvalidFormatRutArray(): void
    {
        $validator = Validator::make([
            'rut' => ['14328145-K', '14.328.145-0', '19.743.721-9']
        ], [
            'rut' => 'rut_strict'
        ]);

        static::assertTrue($validator->fails());
    }

    public function testRutStrictFailsOnAllInvalidFormatRutArray(): void
    {
        $validator = Validator::make([
            'rut' => ['14.328.145-0', '19.743.721-9', '19743721-9']
        ], [
            'rut' => 'rut_strict'
        ]);

        static::assertTrue($validator->fails());
    }

    public function testRutFailsOnAllRutArrayWithEmptyChild(): void
    {
        $validator = Validator::make([
            'rut' => ['14.328.145-0', '19.743.721-9', '']
        ], [
            'rut' => 'rut_strict'
        ]);

        static::assertTrue($validator->fails());
    }
}