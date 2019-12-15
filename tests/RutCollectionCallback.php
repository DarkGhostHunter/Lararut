<?php

namespace Tests;

use Orchestra\Testbench\TestCase;
use DarkGhostHunter\RutUtils\Rut;
use Illuminate\Support\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

class RutCollectionCallback extends TestCase
{
    use RegistersPackage;

    public function testReturnsCollection()
    {
        /** @var Collection $collection */
        $collection = Rut::many([
            $rut_1 = rut()->generate(),
            $rut_2 = rut()->generate(),
        ]);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals($rut_1, $collection->first());
        $this->assertEquals($rut_2, $collection->last());
    }

}