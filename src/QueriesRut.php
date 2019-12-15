<?php

namespace DarkGhostHunter\Lararut;

use DarkGhostHunter\RutUtils\Rut;
use Illuminate\Database\Eloquent\Builder;

trait QueriesRut
{
    /**
     * Adds a `WHERE` clause to the query with the RUT number
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  string|\DarkGhostHunter\RutUtils\Rut $rut
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereRut(Builder $builder, $rut)
    {
        return $builder->where($this->getRutNumberColumn(), Rut::make($rut)->num);
    }

    /**
     * Returns the column that holds the RUT Number
     *
     * @return string
     */
    protected function getRutNumberColumn()
    {
        return $this->rutNumberColumn ?? 'rut_num';
    }
}