<?php

namespace Tests\Validation;

use DarkGhostHunter\RutUtils\Rut;
use DarkGhostHunter\RutUtils\RutGenerator;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Validator;
use Orchestra\Testbench\TestCase;
use Tests\PreparesDatabase;
use Tests\RegistersPackage;


class ValidateRutExistsTest extends TestCase
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

    public function testRutExists()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString(),
        ], [
            'rut' => 'rut_exists:testing.users,rut_num,rut_vd'
        ]);

        static::assertFalse($validator->fails());
    }

    public function testRutExistsWithColumnGuessing()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => 'rut_exists:testing.users'
        ]);

        static::assertFalse($validator->fails());

        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => 'rut_exists:testing.users,rut_num'
        ]);

        static::assertFalse($validator->fails());
    }

    public function testRutExistsFailsWhenDoesntExists()
    {
        $user = User::inRandomOrder()->first();

        do {
            $rut = RutGenerator::make()->generate();
        } while ($rut === Rut::make($user->rut_num . $user->rut_vd));

        $validator = Validator::make([
            'rut' => $rut->toFormattedString(),
        ], [
            'rut' => 'rut_exists:testing.users,rut_num,rut_vd'
        ]);

        static::assertTrue($validator->fails());
    }

    public function testRutExistsFailsWhenItsInvalid()
    {
        User::make()->forceFill([
            'name' => 'Alice',
            'email' => 'alice.doe@email.com',
            'password' => '123456',
            'rut_num' => 18765432,
            'rut_vd' => 1,
        ])->save();

        $validator = Validator::make([
            'rut' => '18.765.432-1',
        ], [
            'rut' => 'rut_exists:testing.users,rut_num,rut_vd'
        ]);

        static::assertTrue($validator->fails());
    }
}