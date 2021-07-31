<?php

namespace DarkGhostHunter\Lararut;

use DarkGhostHunter\RutUtils\Rut;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
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
        ['rut', 'validateRut', 'lararut::validation.rut'],
        ['rut_strict', 'validateRutStrict', 'lararut::validation.strict'],
        ['rut_equal', 'validateRutEqual', 'lararut::validation.equal'],
        ['rut_exists', 'validateRutExists', 'lararut::validation.exists'],
        ['rut_unique', 'validateRutUnique', 'lararut::validation.unique'],
        ['num_exists', 'validateNumExists', 'lararut::validation.exists'],
        ['num_unique', 'validateNumUnique', 'lararut::validation.unique'],
    ];

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
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
    protected function registerRules(): void
    {
        $this->app->resolving('validator', static function (Factory $validator, Application $app): void {
            /** @var \Illuminate\Contracts\Translation\Translator $translator */
            $translator = $app->make('translator');

            foreach (static::RULES as [$rule, $extension, $key]) {
                $validator->extend($rule, [ValidatesRut::class, $extension], $translator->get($key));
            }
        });
    }

    /**
     * Let the Rut collection be retrieved as a collection
     *
     * @return void
     */
    protected function addRutCollectionCallback(): void
    {
        Rut::after(static fn (iterable $ruts): Collection => new Collection($ruts));
    }

    /**
     * Register the RUT helper for the blueprint
     *
     * @return void
     */
    protected function macroBlueprint(): void
    {
        Blueprint::macro('rut', function(): ColumnDefinition {
            /** @var \Illuminate\Database\Schema\Blueprint $this */
            return tap($this->unsignedInteger('rut_num'), function (): void {
                /** @var \Illuminate\Database\Schema\Blueprint $this */
                $this->char('rut_vd', 1);
            });
        });

        Blueprint::macro('rutNullable', function (): ColumnDefinition {
            /** @var \Illuminate\Database\Schema\Blueprint $this */
            return tap($this->unsignedInteger('rut_num')->nullable(), function (): void {
                /** @var \Illuminate\Database\Schema\Blueprint $this */
                $this->char('rut_vd', 1)->nullable();
            });
        });
    }

    /**
     * Register the macro for the Rule class
     *
     * @return void
     */
    protected function macroRules(): void
    {
        Rule::macro('rutExists', static function ($table, $numColumn = 'NULL', $rutColumn = 'NULL'): Rules\RutExists {
            return new Rules\RutExists($table, $numColumn, $rutColumn);
        });

        Rule::macro('rutUnique', static function ($table, $numColumn = 'NULL', $rutColumn = 'NULL'): Rules\RutUnique {
            return new Rules\RutUnique($table, $numColumn, $rutColumn);
        });

        Rule::macro('numExists', static function ($table, $column = 'NULL'): Rules\NumExists {
            return new Rules\NumExists($table, $column);
        });

        Rule::macro('numUnique', static function ($table, $column = 'NULL'): Rules\NumUnique {
            return new Rules\NumUnique($table, $column);
        });
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'lararut');
        $this->publishes([
            __DIR__ . '/../resources/lang' => $this->app->resourcePath('lang/vendor/lararut')
        ], 'translations');
    }
}