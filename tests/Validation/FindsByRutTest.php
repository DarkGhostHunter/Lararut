<?php

namespace Tests\Validation;

use DarkGhostHunter\Lararut\FindsByRut;
use DarkGhostHunter\RutUtils\Rut;
use DarkGhostHunter\RutUtils\RutGenerator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\User;
use Orchestra\Testbench\TestCase;
use Tests\PreparesDatabase;
use Tests\RegistersPackage;

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

        static::assertInstanceOf(get_class($this->model), $this->model->find($user->rut));
        static::assertInstanceOf(get_class($this->model), $this->model->find((string)$user->rut));
    }

    public function testFindWithArray()
    {
        $ruts = $this->model->all()->pluck('rut')->map->__toString()->toArray();

        $ruts[1] = Rut::make($ruts[1]);

        $users = $this->model->find($ruts);

        static::assertCount(User::count(), $users);
    }

    public function testFindOrFail()
    {
        $user = $this->model->inRandomOrder()->first();

        static::assertInstanceOf(get_class($this->model), $this->model->findOrFail($user->rut));
        static::assertInstanceOf(get_class($this->model), $this->model->findOrFail((string)$user->rut));
    }

    public function testFindOrFailWithArray()
    {
        $ruts = $this->model->all()->pluck('rut')->map->__toString()->toArray();

        $ruts[1] = Rut::make($ruts[1]);

        $users = $this->model->findOrFail($ruts);

        static::assertInstanceOf(Collection::class, $users);
        static::assertCount(User::count(), $users);
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

        static::assertCount(User::count(), $users);
    }

    public function testFindOrNewReturnsNewInstance()
    {
        do {
            $rut = RutGenerator::make()->generate();
        } while ($this->model->find($rut));

        $user = $this->model->findOrNew($rut);

        static::assertInstanceOf(get_class($this->model), $user);
        static::assertFalse($user->exists);
    }

    public function testFindOrNewReturnsFindable()
    {
        $rut = $this->model->inRandomOrder()->first()->rut;

        $user = $this->model->findOrNew($rut);

        static::assertInstanceOf(get_class($this->model), $user);
        static::assertTrue($user->exists);
    }
}