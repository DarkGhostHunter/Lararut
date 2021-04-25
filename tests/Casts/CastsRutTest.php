<?php

namespace Tests\Casts;

use DarkGhostHunter\Lararut\Casts\CastRut;
use DarkGhostHunter\Lararut\HasRut;
use DarkGhostHunter\Lararut\QueriesRut;
use DarkGhostHunter\RutUtils\Rut;
use DarkGhostHunter\RutUtils\RutGenerator;
use Illuminate\Foundation\Auth\User;
use Orchestra\Testbench\TestCase;
use Tests\PreparesDatabase;
use Tests\RegistersPackage;

class CastsRutTest extends TestCase
{
    use RegistersPackage;
    use PreparesDatabase;

    /** @var \Illuminate\Foundation\Auth\User */
    protected $model;

    protected function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            $this->prepareDatabase();

            $this->model = new class extends User {
                use HasRut;
                protected $table = 'users';
                protected $casts = ['rut' => CastRut::class];
            };
        });

        parent::setUp();
    }

    public function test_registers_cast()
    {
        static::assertEquals(
            [
                'id'  => 'int',
                'rut' => CastRut::class,
            ],
            $this->model->getCasts()
        );
    }

    public function test_casts_ruts_gets_rut()
    {
        $user = $this->model->first();

        static::assertInstanceOf(Rut::class, $user->rut);
        static::assertEquals($user->rut->num, $user->rut_num);
        static::assertEquals($user->rut->vd, $user->rut_vd);
    }

    public function test_casts_ruts_sets_rut()
    {
        $rut = RutGenerator::make()->generate();

        $this->model->make()->forceFill(
            [
                'name' => 'John',
                'email' => 'anything@cmail.com',
                'password' => '123456',
                'rut' => $rut->toBasicString(),
            ]
        )->save();

        $user = $this->model->find(4);

        static::assertInstanceOf(Rut::class, $user->rut);
        static::assertEquals($rut->num, $user->rut_num);
        static::assertEquals($rut->vd, $user->rut_vd);
    }
}