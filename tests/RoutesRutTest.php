<?php

namespace Tests;

use DarkGhostHunter\Lararut\HasRut;
use DarkGhostHunter\Lararut\RoutesRut;
use DarkGhostHunter\RutUtils\RutGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;

class RoutesRutTest extends TestCase
{
    use RegistersPackage;
    use PreparesDatabase;

    /** @var \Illuminate\Foundation\Auth\User */
    protected $model;

    protected function setUp(): void
    {
        $this->afterApplicationCreated(
            function () {
                $this->prepareDatabase();

                $this->model = new DummyRoutableModel();
            }
        );

        parent::setUp();
    }

    public function test_routes_user_by_rut(): void
    {
        Route::get('/user/{routable:rut}', function (DummyRoutableModel $routable) {
            return $routable->getKey();
        })->middleware('web');

        $this->get("/user/20490006-K")->assertOk()->assertSee(3);
    }

    public function test_routes_bypassed_if_not_rut_field(): void
    {
        Route::get('/user/{routable}', function (DummyRoutableModel $routable) {
            return $routable->rut->toBasicString();
        })->middleware('web');

        $this->get('user/3')->assertSee('20490006-K');
    }

    public function test_routes_not_found_if_rut_not_in_database(): void
    {
        Route::get('/user/{routable:rut}', function (DummyRoutableModel $routable) {
            return $routable->getKey();
        })->middleware('web');

        do {
            $rut = RutGenerator::make()->generate();
        } while (DB::table('users')->where('rut_num', $rut->num)->exists());

        $this->get("/user/{$rut->toBasicString()}")->assertNotFound();
    }

    public function test_routes_not_found_if_rut_invalid(): void
    {
        Route::get('/user/{routable:rut}', function (DummyRoutableModel $routable) {
            return $routable->getKey();
        })->middleware('web');

        $this->get("/user/invalid-rut")->assertNotFound();
    }

    public function test_exception_routes_rut_without_trait()
    {
        Route::get('/user/{routable:rut}', function (DummyUnroutableModel $routable) {
            return $routable->getKey();
        })->middleware('web');

        $this->get("/user/anyrut")->assertStatus(500);
    }
}

class DummyRoutableModel extends Model
{
    use HasRut;
    use RoutesRut;

    protected $table = 'users';
}

class DummyUnroutableModel extends Model
{
    use RoutesRut;

    protected $table = 'users';
}