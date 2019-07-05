<?php

namespace Tests\Validation;

use DarkGhostHunter\RutUtils\Rut;
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
        parent::setUp();

        $this->prepareDatabase();
    }

    public function testNumExists()
    {
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString(),
        ], [
            'rut' => 'num_exists:testing.users,rut_num'
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testNumExistsWithColumnGuessing()
    {
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString(),
        ], [
            'rut' => 'num_exists:testing.users'
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testNumExistsFailsWhenDoesntExists()
    {
        do {
            $rut = Rut::generate();
        } while ($rut === $this->getRut($this->user1));

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
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)
        ], [
            'rut' => 'num_exists:testing.users,invalid_column'
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testNumExistsFailsWhenAbsentOneParameters()
    {
        $this->expectException(InvalidArgumentException::class);

        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)
        ], [
            'rut' => 'num_exists'
        ]);

        $this->assertTrue($validator->fails());
    }
}