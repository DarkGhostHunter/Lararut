<?php

namespace Tests\Validation;

use DarkGhostHunter\RutUtils\Rut;
use DarkGhostHunter\RutUtils\RutGenerator;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase;
use Tests\PreparesDatabase;
use Tests\RegistersPackage;

class ValidateNumExistsTest extends TestCase
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

    public function testNumExists(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => 'num_exists:testing.users'
        ]);

        static::assertFalse($validator->fails());
    }

    public function testNumExistsWithColumnGuessing(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => (new Rut($user->rut_num, $user->rut_vd))->toFormattedString()
        ], [
            'rut' => 'num_exists:testing.users'
        ]);

        static::assertFalse($validator->fails());
    }

    public function testNumExistsFailsWhenDoesntExists(): void
    {
        $user = User::inRandomOrder()->first();

        do {
            $rut = RutGenerator::make()->generate();
        } while ($rut === Rut::make($user->rut_num . $user->rut_vd));

        $validator = Validator::make([
            'rut' => $rut->toFormattedString()
        ], [
            'rut' => 'num_exists:testing.users,rut_num'
        ]);

        static::assertTrue($validator->fails());
    }

    public function testNumExistsFailsWhenInvalidRut(): void
    {
        User::make()->forceFill([
            'name' => 'Alice',
            'email' => 'alice.doe@email.com',
            'password' => '123456',
            'rut_num' => 18765432,
            'rut_vd' => 1,
        ])->save();

        $validator = Validator::make([
            'rut' => '18.765.432-1'
        ], [
            'rut' => 'num_exists:testing.users,rut_num'
        ]);

        static::assertTrue($validator->fails());
    }

    public function testNumExistsFailsWhenInvalidColumn(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => (new Rut($user->rut_num, $user->rut_vd))->toFormattedString()
        ], [
            'rut' => 'num_exists:testing.users,invalid_column'
        ]);

        static::assertTrue($validator->fails());
    }

    public function testNumExistsFailsWhenAbsentOneParameters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => 'num_exists'
        ]);

        static::assertTrue($validator->fails());
    }
}