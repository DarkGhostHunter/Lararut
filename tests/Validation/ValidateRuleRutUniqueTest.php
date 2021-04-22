<?php

namespace Tests\Validation;

use DarkGhostHunter\RutUtils\Rut;
use DarkGhostHunter\RutUtils\RutGenerator;
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
        $this->afterApplicationCreated(function () {
            $this->prepareDatabase();
        });

        parent::setUp();
    }

    public function testValidationRuleRutUnique()
    {
        do {
            $rut = RutGenerator::make()->generate();
        } while (User::where('rut_num', $rut->num)->exists());

        $validator = Validator::make([
            'rut' => $rut->toFormattedString()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
        ]);

        static::assertFalse($validator->fails());
    }

    public function testValidationRuleRutUniqueIgnoringId()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->ignore($user->getKey())
        ]);

        static::assertFalse($validator->fails());
    }

    public function testValidationRuleRutUniqueIgnoringModel()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->ignoreModel($user)
        ]);

        static::assertFalse($validator->fails());
    }

    public function testValidationRuleRutUniqueIgnoringModelInIgnoreMethod()
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->ignore($user)
        ]);

        static::assertFalse($validator->fails());
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

        static::assertFalse($validator->fails());

        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->where('name', $user->name)
        ]);

        static::assertTrue($validator->fails());
    }
}