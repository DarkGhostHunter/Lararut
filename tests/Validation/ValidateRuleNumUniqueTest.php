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
        do {
            $rut = Rut::generate();
        } while ($rut === $this->getRut($this->user1));

        $validator = Validator::make([
            'rut' => $rut->toFormattedString()
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleNumUniqueIgnoringId()
    {
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
                ->ignore($this->user1->getKey())
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleNumUniqueIgnoringModel()
    {
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
                ->ignoreModel($this->user1)
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleNumUniqueWhere()
    {
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
                ->where('name', 'Anything that is not John')
        ]);

        $this->assertFalse($validator->fails());

        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
                ->where('name', 'John')
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testValidationRuleNumUniqueFailsWhenNoArguments()
    {
        $this->expectException(ArgumentCountError::class);

        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
        ], [
            'rut' => Rule::numUnique()
        ]);

        $this->assertFalse($validator->fails());
    }
}