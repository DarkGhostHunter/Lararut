<?php

namespace DarkGhostHunter\Lararut;

use Illuminate\Validation\Rule;
use DarkGhostHunter\RutUtils\Rut;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Blueprint;

class LararutServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRules();
        $this->addRutCollectionCallback();
        $this->macroRules();
        $this->macroBlueprint();
    }

    /**
     * Register the Validator rules
     *
     * @return void
     */
    protected function registerRules()
    {
        $this->app->resolving('validator', function ($validator) {
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
    }

    /**
     * Let the Rut collection be retrieved as a collection
     *
     * @return void
     */
    protected function addRutCollectionCallback()
    {
        Rut::after(function ($ruts) {
            return new Collection($ruts);
        });
    }

    /**
     * Register the RUT helper for the blueprint
     *
     * @return void
     */
    protected function macroBlueprint()
    {
        Blueprint::macro('rut', function () {
            $num = $this->unsignedInteger('rut_num');
            $this->char('rut_vd', 1);

            return $num;
        });
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