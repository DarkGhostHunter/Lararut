<?php

namespace DarkGhostHunter\Lararut;

use DarkGhostHunter\RutUtils\Rut;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rule;
use DarkGhostHunter\Lararut\ValidatesRut;

class LararutServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Rut::class);

        $this->app->resolving('validator', function ($validator, $app) {
            /** @var \Illuminate\Contracts\Validation\Factory $validator */
            $rules = [
                'rut'           => 'DarkGhostHunter\Lararut\ValidatesRut@validateRut',
                'rut_strict'    => 'DarkGhostHunter\Lararut\ValidatesRut@validateRutStrict',
                'rut_equal'     => 'DarkGhostHunter\Lararut\ValidatesRut@validateRutEqual',
                'rut_exists'    => 'DarkGhostHunter\Lararut\ValidatesRut@validateRutExists',
                'rut_unique'    => 'DarkGhostHunter\Lararut\ValidatesRut@validateRutUnique',
                'num_exists'    => 'DarkGhostHunter\Lararut\ValidatesRut@validateNumExists',
                'num_unique'    => 'DarkGhostHunter\Lararut\ValidatesRut@validateNumUnique',
            ];

            foreach ($rules as $key => $rule) {
                $validator->extend($key, $rule);
            }
        });

        Rule::macro('rutExists', function ($table, $numColumn = 'NULL', $rutColumn = 'NULL') {
            return new Rules\RutExists($table, $numColumn, $rutColumn);
        });

        Rule::macro('rutUnique', function ($table, $numColumn = 'NULL', $rutColumn = 'NULL') {
            return new Rules\RutUnique($table, $numColumn, $rutColumn);
        });

        Rule::macro('numExists', function ($table, $column = 'NULL') {
            return new Rules\NumExists($table, $column);
        });

        Rule::macro('numUnique', function ($table, $column = 'NULL') {
            return new Rules\NumUnique($table, $column);
        });

        Rule::macro('rutEqual', function (...$ruts) {
            return new Rules\RutEqual(...$ruts);
        });
    }

}