<?php

namespace Tests\Validation;

use DarkGhostHunter\Lararut\ValidatesRut;
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
        parent::setUp();

        $this->prepareDatabase();
    }

    public function testUnique()
    {
        do {
            $rut = RutGenerator::make()->generate();
        } while (User::where(['rut_num', $rut->num, 'rut_vd', $rut->vd])->exists());

        $validator = Validator::make([
            'rut' => $rut->toFormattedString(),
        ], [
            'rut' => 'rut_unique:testing.users,rut_num,rut_vd'
        ]);

        $this->assertFalse($validator->fails());
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

        $this->assertFalse($validator->fails());
    }

    public function testUniqueLowercase()
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

    public function testUniqueFailsWhenNotUnique()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => 'rut_unique:testing.users,rut_num,rut_vd'
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testUniqueFailsWhenInvalidRut()
    {
        $validator = Validator::make([
            'rut' => '18.765.432-1',
        ], [
            'rut' => 'rut_unique:testing.users,rut_num,rut_vd'
        ]);

        $this->assertTrue($validator->fails());
    }

}