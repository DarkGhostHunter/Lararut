<?php

namespace Tests\Validation;

use DarkGhostHunter\RutUtils\Rut;
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
        parent::setUp();

        $this->prepareDatabase();
    }

    public function testRutExists()
    {
        $user = User::first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd),
        ], [
            'rut' => 'rut_exists:testing.users,rut_num,rut_vd'
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRutExistsWithColumnGuessing()
    {
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString(),
        ], [
            'rut' => 'rut_exists:testing.users'
        ]);

        $this->assertFalse($validator->fails());

        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString(),
        ], [
            'rut' => 'rut_exists:testing.users,rut_num'
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRutExistsFailsWhenDoesntExists()
    {
        do {
            $rut = Rut::generate();
        } while ($rut === $this->getRut($this->user1));

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