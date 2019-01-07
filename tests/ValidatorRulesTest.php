<?php

namespace Tests;

use DarkGhostHunter\Lararut\Facades\Rut;
use DarkGhostHunter\RutUtils\RutBuilder;
use Orchestra\Testbench\TestCase;

class ValidatorRulesTest extends TestCase
{
    /** @var Rut */
    protected $rut;

    protected function getPackageProviders($app)
    {
        return ['DarkGhostHunter\Lararut\LararutServiceProvider'];
    }

    protected function getPackageAliases($app)
    {
        return ['Rut' => 'DarkGhostHunter\Lararut\Facades\Rut'];
    }

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->rut = Rut::generate();

        $this->loadLaravelMigrations();

        /** @var \Illuminate\Database\DatabaseManager $db */
        $db = $this->app->make('db');

        $db->connection()
            ->getSchemaBuilder()
            ->table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->bigInteger('rut_number')->nullable();
                $table->string('rut_vd')->nullable();
            });

        $db->table('users')->insert([
            'name' => 'johndoe',
            'email' => 'johndoe@email.com',
            'password' => '123456',
            'rut_number' => $this->rut->num,
            'rut_vd' => strtolower($this->rut->vd),
        ]);

    }

    public function testExtendsValidator()
    {
        /** @var \Illuminate\Validation\Factory $validator */
        $validator = $this->app->make('validator');

        $validates = $validator->make([
            'rut1' => ($builder = new RutBuilder)->generate()->toRawString(),
            'rut2' => $builder->generate()->toFormattedString(),
            'rut3' => $equal = $builder->generate()->toRawString(),
            'rut4' => $this->rut,
        ], [
            'rut1' => 'required|is_rut',
            'rut2' => 'required|is_rut_strict',
            'rut3' => 'required|is_rut_equal:' . $equal,
            'rut4' => 'required|rut_exists:testing.users,rut_number,rut_vd'
        ]);

        $this->assertTrue($validates->passes());
    }

    public function testValidatorFails()
    {
        /** @var \Illuminate\Validation\Factory $validator */
        $validator = $this->app->make('validator');

        $validates = $validator->make([
            'rut1' => 'asdasdas',
            'rut2' => '14328145-0',
            'rut3' => '143281450',
            'rut4' => '94.328.145-0',
        ], [
            'rut1' => 'required|is_rut',
            'rut2' => 'required|is_rut_strict',
            'rut3' => 'required|is_rut_equal:' . '94.328.145-0',
            'rut4' => 'required|rut_exists:testing.users,rut_number,rut_vd'
        ]);

        $this->assertTrue($validates->fails());

        $this->assertArrayHasKey('rut1', $validates->failed());
        $this->assertArrayHasKey('rut2', $validates->failed());
        $this->assertArrayHasKey('rut3', $validates->failed());
        $this->assertArrayHasKey('rut4', $validates->failed());
    }

    public function testValidatorIsRutEqualFails()
    {
        /** @var \Illuminate\Validation\Factory $validator */
        $validator = $this->app->make('validator');

        $validates = $validator->make([
            'rut1' => 'asdasdas',
            'rut2' => '14328145-0',
        ], [
            'rut1' => 'required|is_rut_equal:' . '94.328.145-0',
            'rut2' => 'required|is_rut_equal:' . 'asdasdasd',
        ]);

        $this->assertTrue($validates->fails());

        $this->assertArrayHasKey('rut1', $validates->failed());
        $this->assertArrayHasKey('rut2', $validates->failed());
    }
}