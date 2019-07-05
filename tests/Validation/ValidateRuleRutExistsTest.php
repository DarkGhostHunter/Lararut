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
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
        ], [
            'rut' => Rule::rutExists('testing.users', 'rut_num', 'rut_vd')
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleRutExistsWithColumnGuessing()
    {
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
        ], [
            'rut' => Rule::rutExists('testing.users')
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleRutExistsWithWhere()
    {
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
        ], [
            'rut' => Rule::rutExists('testing.users', 'rut_num', 'rut_vd')
                ->where('name', 'John')
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleRutExistsFailsWhenNoArguments()
    {
        $this->expectException(ArgumentCountError::class);

        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
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
        do {
            $rut = Rut::generate();
        } while ($rut === $this->getRut($this->user1));

        $validator = Validator::make([
            'rut' => $rut->toFormattedString()
        ], [
            'rut' => Rule::rutExists('testing.users', 'rut_num', 'rut_vd')
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testValidationRuleRutExistsFailWhenInvalidColumn()
    {
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
        ], [
            'rut' => Rule::rutExists('testing.users', 'absent_num', 'absent_vd')
        ]);

        $this->assertTrue($validator->fails());
    }
}