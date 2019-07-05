<?php

namespace DarkGhostHunter\Lararut\Rules;

use Illuminate\Validation\Rules\DatabaseRule;

class RutExists
{
    use DatabaseRule;

    /**
     * Column of the RUT number
     *
     * @var string
     */
    protected $numColumn;

    /**
     * Column of the RUT verification digit
     *
     * @var string
     */
    protected $vdColumn;

    /**
     * Create a new rule instance.
     *
     * @param string $table
     * @param string $numColumn
     * @param string $vdColumn
     */
    public function __construct($table, $numColumn = 'NULL', $vdColumn = 'NULL')
    {
        $this->table = $table;
        $this->numColumn = $numColumn;
        $this->vdColumn = $vdColumn;
    }

    /**
     * Convert the rule to a validation string.
     *
     * @return string
     */
    public function __toString()
    {
        return rtrim(sprintf('rut_exists:%s,%s,%s,%s',
            $this->table,
            $this->numColumn,
            $this->vdColumn,
            $this->formatWheres()
        ), ',');
    }
}