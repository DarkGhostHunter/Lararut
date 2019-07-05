<?php

namespace Tests\Validation;

use ArgumentCountError;
use DarkGhostHunter\RutUtils\Rut;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Orchestra\Testbench\TestCase;
use Tests\PreparesDatabase;
use Tests\RegistersPackage;

class ValidateRuleRutExistsTest extends TestCase
{
    use RegistersPackage,
        PreparesDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareDatabase();
    }

    public function testValidationRuleRutExists()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::rutExists('testing.users', 'rut_num', 'rut_vd')
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleRutExistsWithColumnGuessing()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::rutExists('testing.users')
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleRutExistsWithWhere()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::rutExists('testing.users', 'rut_num', 'rut_vd')
                ->where('name', 'John')
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleRutExistsFailsWhenNoArguments()
    {
        $this->expectException(ArgumentCountError::class);

        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::rutExists()
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleRutExistsFailWhenRutInvalid()
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
            'rut' => Rule::rutExists('testing.users', 'rut_num', 'rut_vd')
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testValidationRuleRutExistsFailWhenRutDoesntExists()
    {
        $user = User::inRandomOrder()->first();

        do {
            $rut = Rut::generate();
        } while ($rut === Rut::make($user->rut_num . $user->rut_vd));

        $validator = Validator::make([
            'rut' => $rut->toFormattedString()
        ], [
            'rut' => Rule::rutExists('testing.users', 'rut_num', 'rut_vd')
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testValidationRuleRutExistsFailWhenInvalidColumn()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::rutExists('testing.users', 'absent_num', 'absent_vd')
        ]);

        $this->assertTrue($validator->fails());
    }
}