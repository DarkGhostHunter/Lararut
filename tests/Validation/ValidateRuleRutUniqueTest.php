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

class ValidateRuleRutUniqueTest extends TestCase
{
    use RegistersPackage,
        PreparesDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareDatabase();
    }

    public function testValidationRuleRutUnique()
    {
        $user = User::inRandomOrder()->first();

        do {
            $rut = Rut::generate();
        } while ($rut === Rut::make($user->rut_num . $user->rut_vd));

        $validator = Validator::make([
            'rut' => $rut->toFormattedString()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleRutUniqueIgnoringId()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->ignore($this->user1->getKey())
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleRutUniqueIgnoringModel()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->ignoreModel($this->user1)
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleRutUniqueIgnoringModelInIgnoreMethod()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->ignore($this->user1)
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleRutUniqueWhere()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->where('name', 'Anything that is not John')
        ]);

        $this->assertFalse($validator->fails());

        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->where('name', 'John')
        ]);

        $this->assertTrue($validator->fails());
    }
}