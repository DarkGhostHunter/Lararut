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

class ValidateRuleNumUniqueTest extends TestCase
{
    use RegistersPackage,
        PreparesDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareDatabase();
    }

    public function testValidationRuleNumUnique()
    {
        $user = User::inRandomOrder()->first();

        do {
            $rut = Rut::generate();
        } while ($rut === Rut::make($user->rut_num . $user->rut_vd));

        $validator = Validator::make([
            'rut' => $rut->toFormattedString()
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleNumUniqueIgnoringId()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
                ->ignore($this->user1->getKey())
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleNumUniqueIgnoringModel()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
                ->ignoreModel($this->user1)
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleNumUniqueWhere()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
                ->where('name', 'Anything that is not John')
        ]);

        $this->assertFalse($validator->fails());

        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
                ->where('name', 'John')
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testValidationRuleNumUniqueFailsWhenNoArguments()
    {
        $this->expectException(ArgumentCountError::class);

        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::numUnique()
        ]);

        $this->assertFalse($validator->fails());
    }
}