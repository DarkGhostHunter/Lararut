<?php

namespace Tests\Validation;

use ArgumentCountError;
use DarkGhostHunter\RutUtils\Rut;
use DarkGhostHunter\RutUtils\RutGenerator;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Orchestra\Testbench\TestCase;
use Tests\PreparesDatabase;
use Tests\RegistersPackage;


class ValidateRuleNumExistsTest extends TestCase
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

    public function testValidationRuleRutExists()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::numExists('testing.users', 'rut_num')
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleNumExistsWithColumnGuessing()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num, $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::numExists('testing.users')
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleNumExistsWithWhere()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::numExists('testing.users', 'rut_num')
                ->where('name', $user->name)
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleNumExistsFailsWithNoArguments()
    {
        $this->expectException(ArgumentCountError::class);

        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::numExists()
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleNumExistsFailWhenRutInvalid()
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
            'rut' => Rule::numExists('testing.users', 'rut_num')
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testValidationRuleNumExistsFailWhenRutDoesntExists()
    {
        $user = User::inRandomOrder()->first();

        do {
            $rut = RutGenerator::make()->generate();
        } while ($rut === Rut::make($user->rut_num . $user->rut_vd));

        $validator = Validator::make([
            'rut' => $rut->toFormattedString()
        ], [
            'rut' => Rule::numExists('testing.users', 'rut_num')
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testValidationRuleNumExistsFailWhenAbsentColumn()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => (new Rut($user->rut_num, $user->rut_vd))->toFormattedString()
        ], [
            'rut' => Rule::numExists('testing.users', 'absent_num')
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testValidationRuleNumExistsFailWhenInvalidColumn()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::numExists('testing.users', 'absent_num')
        ]);

        $this->assertTrue($validator->fails());
    }
}