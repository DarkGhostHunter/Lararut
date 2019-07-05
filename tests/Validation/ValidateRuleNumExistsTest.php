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

class ValidateRuleNumExistsTest extends TestCase
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
            'rut' => Rule::numExists('testing.users', 'rut_num')
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleNumExistsWithColumnGuessing()
    {
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
        ], [
            'rut' => Rule::numExists('testing.users')
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleNumExistsWithWhere()
    {
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
        ], [
            'rut' => Rule::numExists('testing.users', 'rut_num')
                ->where('name', 'John')
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testValidationRuleNumExistsFailsWithNoArguments()
    {
        $this->expectException(ArgumentCountError::class);

        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
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
        do {
            $rut = Rut::generate();
        } while ($rut === $this->getRut($this->user1));

        $validator = Validator::make([
            'rut' => $rut->toFormattedString()
        ], [
            'rut' => Rule::numExists('testing.users', 'rut_num')
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testValidationRuleNumExistsFailWhenAbsentColumn()
    {
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
        ], [
            'rut' => Rule::numExists('testing.users', 'absent_num')
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testValidationRuleNumExistsFailWhenInvalidColumn()
    {
        $validator = Validator::make([
            'rut' => $this->getRut($this->user1)->toFormattedString()
        ], [
            'rut' => Rule::numExists('testing.users', 'absent_num')
        ]);

        $this->assertTrue($validator->fails());
    }
}