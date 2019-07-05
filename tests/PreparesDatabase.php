<?php

namespace Tests;

use DarkGhostHunter\RutUtils\Rut;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User;

trait PreparesDatabase
{
    /** @var User */
    protected $user1;

    /** @var User */
    protected $user2;

    protected function prepareDatabase()
    {
        $this->loadLaravelMigrations();

        /** @var \Illuminate\Database\DatabaseManager $db */
        $db = $this->app->make('db');

        $db->connection()
            ->getSchemaBuilder()
            ->table('users', function (Blueprint $table) {
                $table->bigInteger('rut_num')->nullable();
                $table->string('rut_vd')->nullable();
            });

        $this->user1 = User::make()->forceFill([
            'name' => 'John',
            'email' => 'john.doe@email.com',
            'password' => '123456',
            'rut_num' => ($rut = Rut::generate())->num,
            'rut_vd' => strtolower($rut->vd),
        ]);

        $this->user1->save();

        $this->user2 = User::make()->forceFill([
            'name' => 'Michael',
            'email' => 'michael.doe@email.com',
            'password' => '123456',
            'rut_num' => ($rut = Rut::generate())->num,
            'rut_vd' => strtolower($rut->vd),
        ]);

        $this->user2->save();
    }
}