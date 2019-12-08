<?php

namespace DarkGhostHunter\Lararut;

use DarkGhostHunter\Lararut\Rules\NumExists;
use DarkGhostHunter\Lararut\Rules\NumUnique;
use DarkGhostHunter\Lararut\Rules\RutExists;
use DarkGhostHunter\Lararut\Rules\RutUnique;
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
        $this->app->resolving('validator', function ($validator, $app) {
            /** @var \Illuminate\Contracts\Validation\Factory $validator */
            $rules = [
                'rut'        => [
                    'DarkGhostHunter\Lararut\ValidatesRut@validateRut',
                    'lararut::validation.rut',
                ],
                'rut_strict' => [
                    'DarkGhostHunter\Lararut\ValidatesRut@validateRutStrict',
                    'lararut::validation.strict',
                ],
                'rut_equal'  => [
                    'DarkGhostHunter\Lararut\ValidatesRut@validateRutEqual',
                    'lararut::validation.equal',
                ],
                'rut_exists' => [
                    'DarkGhostHunter\Lararut\ValidatesRut@validateRutExists',
                    'lararut::validation.exists',
                ],
                'rut_unique' => [
                    'DarkGhostHunter\Lararut\ValidatesRut@validateRutUnique',
                    'lararut::validation.unique',
                ],
                'num_exists' => [
                    'DarkGhostHunter\Lararut\ValidatesRut@validateNumExists',
                    'lararut::validation.num_exists',
                ],
                'num_unique' => [
                    'DarkGhostHunter\Lararut\ValidatesRut@validateNumUnique',
                    'lararut::validation.num_unique',
                ],
            ];

            foreach ($rules as $key => $rule) {
                $validator->extend($key, ...$rule);
            }
        });

        $this->macroRules();
    }

    /**
     * Register the macro for the Rule class
     *
     * @return void
     */
    protected function macroRules()
    {
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
    }

}