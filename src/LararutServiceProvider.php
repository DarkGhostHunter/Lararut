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
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot()
    {
        /** @var \Illuminate\Validation\Factory $validator */
        $validator = $this->app->make('validator');

        $rules = [
            'is_rut'        => 'DarkGhostHunter\Lararut\RutValidationRules@isRut',
            'is_rut_strict' => 'DarkGhostHunter\Lararut\RutValidationRules@isRutStrict',
            'is_rut_equal'  => 'DarkGhostHunter\Lararut\RutValidationRules@isRutEqual',
            'rut_exists'    => 'DarkGhostHunter\Lararut\RutValidationRules@rutExists',
            'rut_unique'    => 'DarkGhostHunter\Lararut\RutValidationRules@rutUnique',
        ];

        foreach ($rules as $key => $rule) {
            $validator->extend($key, $rule);
        }
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