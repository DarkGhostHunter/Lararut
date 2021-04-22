<?php

namespace Tests\Validation;

use DarkGhostHunter\RutUtils\Rut;
use DarkGhostHunter\RutUtils\RutGenerator;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Validator;
use Orchestra\Testbench\TestCase;
use Tests\PreparesDatabase;
use Tests\RegistersPackage;


class ValidateRutUniqueTest extends TestCase
{
    use RegistersPackage,
        PreparesDatabase;

    protected function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            $this->prepareDatabase();
        });

        parent::setUp();
    }

    public function testUnique()
    {
        do {
            $rut = RutGenerator::make()->generate();
        } while (User::where('rut_num', $rut->num)->exists());

        $validator = Validator::make([
            'rut' => $rut->toFormattedString(),
        ], [
            'rut' => 'rut_unique:testing.users,rut_num,rut_vd'
        ]);

        static::assertFalse($validator->fails());
    }

    public function testUniqueWithColumnGuessing()
    {
        do {
            $rut = RutGenerator::make()->generate();
        } while (User::where(['rut_num', $rut->num, 'rut_vd', $rut->vd])->exists());

        $validator = Validator::make([
            'rut' => $rut->toFormattedString(),
        ], [
            'rut' => 'rut_unique:testing.users'
        ]);

        static::assertFalse($validator->fails());
    }

    public function testUniqueFailsWhenNotUnique()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => 'rut_unique:testing.users,rut_num,rut_vd'
        ]);

        static::assertTrue($validator->fails());
    }

    public function testUniqueFailsWhenInvalidRut()
    {
        $validator = Validator::make([
            'rut' => '18.765.432-1',
        ], [
            'rut' => 'rut_unique:testing.users,rut_num,rut_vd'
        ]);

        static::assertTrue($validator->fails());
    }

}