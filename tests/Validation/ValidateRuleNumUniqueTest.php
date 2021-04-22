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


class ValidateRuleNumUniqueTest extends TestCase
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

    public function testValidationRuleNumUnique(): void
    {
        do {
            $rut = RutGenerator::make()->generate();
        } while (User::where('rut_num', $rut->num)->exists());

        $validator = Validator::make([
            'rut' => $rut->toFormattedString()
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
        ]);

        static::assertFalse($validator->fails());
    }

    public function testValidationRuleNumUniqueIgnoringId(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
                ->ignore($user->getKey())
        ]);

        static::assertFalse($validator->fails());
    }

    public function testValidationRuleNumUniqueIgnoringModel(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
                ->ignoreModel($user)
        ]);

        static::assertFalse($validator->fails());
    }

    public function testValidationRuleNumUniqueWhere(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
                ->where('name', 'Anything that is not John')
        ]);

        static::assertFalse($validator->fails());

        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
                ->where('name', $user->name)
        ]);

        static::assertTrue($validator->fails());
    }

    public function testValidationRuleNumUniqueFailsWhenNoArguments(): void
    {
        $this->expectException(ArgumentCountError::class);

        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::make($user->rut_num . $user->rut_vd)->toFormattedString()
        ], [
            'rut' => Rule::numUnique()
        ]);

        static::assertFalse($validator->fails());
    }
}