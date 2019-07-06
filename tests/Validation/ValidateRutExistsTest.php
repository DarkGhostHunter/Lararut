<?php

namespace Tests\Validation;

use DarkGhostHunter\Lararut\ValidatesRut;
use DarkGhostHunter\RutUtils\Rut;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Orchestra\Testbench\TestCase;
use Tests\PreparesDatabase;
use Tests\RegistersPackage;

class ValidateRutExistsTest extends TestCase
{
    use RegistersPackage,
        PreparesDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareDatabase();
    }

    public function testRutExists()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString(),
        ], [
            'rut' => 'rut_exists:testing.users,rut_num,rut_vd'
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRutExistsWithColumnGuessing()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => 'rut_exists:testing.users'
        ]);

        $this->assertFalse($validator->fails());

        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => 'rut_exists:testing.users,rut_num'
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRutExistsLowercase()
    {
        $user = User::make()->forceFill([
            'name' => 'Karen',
            'email' => 'karen.doe@email.com',
            'password' => '123456',
            'rut_num' => '12435756',
            'rut_vd' => 'k',
        ]);

        $user->save();

        ValidatesRut::useLowercase();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => 'rut_exists:testing.users'
        ]);

        $this->assertFalse($validator->fails());

        ValidatesRut::useUppercase();
    }

    public function testRutExistsFailsWhenDoesntExists()
    {
        $user = User::inRandomOrder()->first();

        do {
            $rut = Rut::generate();
        } while ($rut === Rut::make($user->rut_num . $user->rut_vd));

        $validator = Validator::make([
            'rut' => $rut->toFormattedString(),
        ], [
            'rut' => 'rut_exists:testing.users,rut_num,rut_vd'
        ]);

        $this->assertTrue($validator->fails());
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

        $this->assertTrue($validator->fails());
    }
}