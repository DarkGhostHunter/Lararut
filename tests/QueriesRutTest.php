<?php

namespace Tests;

use DarkGhostHunter\Lararut\QueriesRut;
use DarkGhostHunter\RutUtils\Rut;
use DarkGhostHunter\RutUtils\RutGenerator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User;
use Orchestra\Testbench\TestCase;

class QueriesRutTest extends TestCase
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
                use QueriesRut;
                protected $table = 'users';
                protected function getRutAttribute()
                {
                    return new Rut($this->attributes['rut_num'], $this->attributes['rut_vd']);
                }
            };
        });

        parent::setUp();
    }

    public function testWhereRut()
    {
        $rut = $this->model->inRandomOrder()->first(['rut_num', 'rut_vd']);

        static::assertTrue($this->model->whereRut($rut)->exists());
        static::assertTrue($this->model->whereRut((string)$rut)->exists());

        do {
            $rut = RutGenerator::make()->generate();
        } while ($this->model->whereRut($rut)->exists());

        static::assertTrue($this->model->whereRut($rut)->doesntExist());
    }

    public function testWhereRutUsesCustomColumn()
    {
        $this->app->make('db')->connection()
            ->getSchemaBuilder()
            ->create('users_test', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
                $table->unsignedBigInteger('test_column_num')->nullable();
                $table->string('test_column_vd')->nullable();
            });

        $model = new class extends User {
            use QueriesRut;
            protected $table = 'users_test';
            protected $rutNumberColumn = 'test_column_num';
            protected function getRutAttribute()
            {
                return new Rut($this->attributes['test_column_num'], $this->attributes['test_column_vd']);
            }
        };

        $model->make()->forceFill([
            'id' => 1,
            'name' => 'John',
            'email' => 'john.doe@email.com',
            'password' => '123456',
            'test_column_num' => ($rut = RutGenerator::make()->generate())->num,
            'test_column_vd' => strtoupper($rut->vd),
        ])->save();

        static::assertTrue($model->whereRut($rut)->exists());
    }
}