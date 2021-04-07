<?php

namespace DarkGhostHunter\Lararut;

use DarkGhostHunter\RutUtils\Rut;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rule;

class LararutServiceProvider extends ServiceProvider
{
    /**
     * Rules to register into the validator.
     *
     * @var array
     */
    protected const RULES = [
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
        $this->app->resolving('validator', static function (Factory $validator) {
            foreach (static::RULES as $key => $rule) {
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
        Rule::macro('rutExists', static function ($table, $numColumn = 'NULL', $rutColumn = 'NULL') {
            return new Rules\RutExists($table, $numColumn, $rutColumn);
        });

        Rule::macro('rutUnique', static function ($table, $numColumn = 'NULL', $rutColumn = 'NULL') {
            return new Rules\RutUnique($table, $numColumn, $rutColumn);
        });

        Rule::macro('numExists', static function ($table, $column = 'NULL') {
            return new Rules\NumExists($table, $column);
        });

        Rule::macro('numUnique', static function ($table, $column = 'NULL') {
            return new Rules\NumUnique($table, $column);
        });
    }
}