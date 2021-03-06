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

    public function testNumExists()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => 'num_exists:testing.users'
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testNumExistsWithColumnGuessing()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => (new Rut($user->rut_num, $user->rut_vd))->toFormattedString()
        ], [
            'rut' => 'num_exists:testing.users'
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testNumExistsFailsWhenDoesntExists()
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

        $this->assertTrue($validator->fails());
    }

    public function testNumExistsFailsWhenInvalidRut()
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

        $this->assertTrue($validator->fails());
    }

    public function testNumExistsFailsWhenInvalidColumn()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => (new Rut($user->rut_num, $user->rut_vd))->toFormattedString()
        ], [
            'rut' => 'num_exists:testing.users,invalid_column'
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testNumExistsFailsWhenAbsentOneParameters()
    {
        $this->expectException(InvalidArgumentException::class);

        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => 'num_exists'
        ]);

        $this->assertTrue($validator->fails());
    }
}