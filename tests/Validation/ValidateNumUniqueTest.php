<?php

namespace Tests\Validation;

use DarkGhostHunter\RutUtils\Rut;
use DarkGhostHunter\RutUtils\RutGenerator;
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
        $this->afterApplicationCreated(function () {
            $this->prepareDatabase();
        });

        parent::setUp();
    }

    public function testUnique(): void
    {
        do {
            $rut = RutGenerator::make()->generate();
        } while (User::where(['rut_num', $rut->num, 'rut_vd', $rut->vd])->exists());

        $validator = Validator::make([
            'rut' => $rut->toFormattedString(),
        ], [
            'rut' => 'num_unique:testing.users,rut_num'
        ]);

        static::assertFalse($validator->fails());
    }

    public function testUniqueWithColumnGuessing(): void
    {
        do {
            $rut = RutGenerator::make()->generate();
        } while (User::where(['rut_num', $rut->num, 'rut_vd', $rut->vd])->exists());

        $validator = Validator::make([
            'rut' => $rut->toFormattedString(),
        ], [
            'rut' => 'num_unique:testing.users'
        ]);

        static::assertFalse($validator->fails());
    }

    public function testUniqueFailsWhenNotUnique(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => 'num_unique:testing.users,rut_num'
        ]);

        static::assertTrue($validator->fails());
    }

    public function testUniqueFailsWhenInvalidRut(): void
    {
        $validator = Validator::make([
            'rut' => '18.765.432-1',
        ], [
            'rut' => 'num_unique:testing.users,rut_num'
        ]);

        static::assertTrue($validator->fails());
    }

}