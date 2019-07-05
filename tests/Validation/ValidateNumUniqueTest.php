<?php

namespace Tests\Validation;

use DarkGhostHunter\RutUtils\Rut;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Validator;
use Orchestra\Testbench\TestCase;
use Tests\PreparesDatabase;
use Tests\RegistersPackage;

class ValidateNumUniqueTest extends TestCase
{
    use RegistersPackage,
        PreparesDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareDatabase();
    }

    public function testUnique()
    {
        do {
            $rut = Rut::generate();
        } while (User::where(['rut_num', $rut->num, 'rut_vd', $rut->vd])->exists());

        $validator = Validator::make([
            'rut' => $rut->toFormattedString(),
        ], [
            'rut' => 'num_unique:testing.users,rut_num'
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testUniqueWithColumnGuessing()
    {
        do {
            $rut = Rut::generate();
        } while (User::where(['rut_num', $rut->num, 'rut_vd', $rut->vd])->exists());

        $validator = Validator::make([
            'rut' => $rut->toFormattedString(),
        ], [
            'rut' => 'num_unique:testing.users'
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testUniqueFailsWhenNotUnique()
    {
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString(),
        ], [
            'rut' => 'num_unique:testing.users,rut_num'
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testUniqueFailsWhenInvalidRut()
    {
        $validator = Validator::make([
            'rut' => '18.765.432-1',
        ], [
            'rut' => 'num_unique:testing.users,rut_num'
        ]);

        $this->assertTrue($validator->fails());
    }

}