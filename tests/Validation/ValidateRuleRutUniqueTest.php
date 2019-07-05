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
        do {
            $rut = Rut::generate();
        } while ($rut === $this->getRut($this->user1));

        $validator = Validator::make([
            'rut' => $rut->toFormattedString()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleRutUniqueIgnoringId()
    {
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->ignore($this->user1->getKey())
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleRutUniqueIgnoringModel()
    {
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->ignoreModel($this->user1)
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleRutUniqueIgnoringModelInIgnoreMethod()
    {
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->ignore($this->user1)
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleRutUniqueWhere()
    {
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->where('name', 'Anything that is not John')
        ]);

        $this->assertFalse($validator->fails());

        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->where('name', 'John')
        ]);

        $this->assertTrue($validator->fails());
    }
}