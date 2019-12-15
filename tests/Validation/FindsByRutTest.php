<?php

namespace Tests\Validation;

use Tests\RegistersPackage;
use Tests\PreparesDatabase;
use Orchestra\Testbench\TestCase;
use DarkGhostHunter\RutUtils\Rut;
use Illuminate\Foundation\Auth\User;
use DarkGhostHunter\Lararut\FindsByRut;
use DarkGhostHunter\RutUtils\RutGenerator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindsByRutTest extends TestCase
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
                use FindsByRut;
                protected $table = 'users';
                protected $primaryKey = 'rut_num';

                protected function getRutAttribute()
                {
                    return new Rut($this->attributes['rut_num'], $this->attributes['rut_vd']);
                }
            };
        });

        parent::setUp();
    }

    public function testFind()
    {
        $user = $this->model->inRandomOrder()->first();

        $this->assertInstanceOf(get_class($this->model), $this->model->find($user->rut));
        $this->assertInstanceOf(get_class($this->model), $this->model->find((string)$user->rut));
    }

    public function testFindWithArray()
    {
        $ruts = $this->model->all()->pluck('rut')->map->__toString()->toArray();

        $ruts[1] = Rut::make($ruts[1]);

        $users = $this->model->find($ruts);

        $this->assertCount(User::count(), $users);
    }

    public function testFindOrFail()
    {
        $user = $this->model->inRandomOrder()->first();

        $this->assertInstanceOf(get_class($this->model), $this->model->findOrFail($user->rut));
        $this->assertInstanceOf(get_class($this->model), $this->model->findOrFail((string)$user->rut));
    }

    public function testFindOrFailWithArray()
    {
        $ruts = $this->model->all()->pluck('rut')->map->__toString()->toArray();

        $ruts[1] = Rut::make($ruts[1]);

        $users = $this->model->findOrFail($ruts);

        $this->assertInstanceOf(Collection::class, $users);
        $this->assertCount(User::count(), $users);
    }

    public function testFindOrFailExceptionWhenBelowResults()
    {
        $this->expectException(ModelNotFoundException::class);

        do {
            $absentRut = RutGenerator::make()->generate();
        } while ($this->model->find($absentRut));

        $ruts = $this->model->all()->pluck('rut')->map->__toString()->toArray();

        $ruts[1] = $absentRut;

        $users = $this->model->findOrFail($ruts);
    }

    public function testFindOrFailReturnsException()
    {
        $this->expectException(ModelNotFoundException::class);

        do {
            $rut = RutGenerator::make()->generate();
        } while ($this->model->find($rut));

        $this->model->findOrFail($rut);
    }

    public function testFindMany()
    {
        $ruts = $this->model->all()->pluck('rut')->map->__toString()->toArray();

        $ruts[1] = Rut::make($ruts[1]);

        $users = $this->model->findMany($ruts);

        $this->assertCount(User::count(), $users);
    }

    public function testFindOrNewReturnsNewInstance()
    {
        do {
            $rut = RutGenerator::make()->generate();
        } while ($this->model->find($rut));

        $user = $this->model->findOrNew($rut);

        $this->assertInstanceOf(get_class($this->model), $user);
        $this->assertFalse($user->exists);
    }

    public function testFindOrNewReturnsFindable()
    {
        $rut = $this->model->inRandomOrder()->first()->rut;

        $user = $this->model->findOrNew($rut);

        $this->assertInstanceOf(get_class($this->model), $user);
        $this->assertTrue($user->exists);
    }
}