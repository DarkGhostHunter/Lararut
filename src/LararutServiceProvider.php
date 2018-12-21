<?php

namespace DarkGhostHunter\Lararut;

use DarkGhostHunter\RutUtils\Exceptions\InvalidRutException;
use DarkGhostHunter\RutUtils\Rut;
use DarkGhostHunter\RutUtils\RutHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class LararutServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        /** @var \Illuminate\Validation\Factory $validator */
        $validator = $this->app->make('validator');

        $validator->extend('is_rut', function ($attribute, $value, $parameters, $validator) {
            return RutHelper::validate($value);
        });

        $validator->extend('is_rut_strict', function ($attribute, $value, $parameters, $validator) {
            return RutHelper::validateStrict($value);
        });

        $validator->extend('is_rut_equal', function ($attribute, $value, $parameters, $validator) {
            return RutHelper::isEqual($value, $parameters[0]);
        });

        $validator->extend('rut_exists', function ($attribute, $value, $parameters, $validator) {
            try { $rut = Rut::makeValid($value); }
            catch (InvalidRutException $exception) { return false; }

            [$connection, $table] = Str::contains($parameters[0], '.')
                ? explode('.', $parameters[0], 2)
                : [null, $parameters[0]];

            return $this->app->make('db')
                ->connection($connection)
                ->table($table)
                ->where(trim($parameters[1], ' '), $rut->num)
                ->where(trim($parameters[2], ' '), $rut->vd)
                ->exists();
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Rut::class);
    }

}