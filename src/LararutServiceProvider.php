<?php

namespace DarkGhostHunter\Lararut;

use DarkGhostHunter\RutUtils\Rut;
use Illuminate\Support\ServiceProvider;

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

        $validator->extend('is_rut', 'DarkGhostHunter\Lararut\RutValidationRules@isRut');
        $validator->extend('is_rut_strict', 'DarkGhostHunter\Lararut\RutValidationRules@isRutStrict');
        $validator->extend('is_rut_equal', 'DarkGhostHunter\Lararut\RutValidationRules@isRutEqual');
        $validator->extend('rut_exists', 'DarkGhostHunter\Lararut\RutValidationRules@rutExists');
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