<?php

namespace Tests;

use DarkGhostHunter\RutUtils\Rut;
use Illuminate\Support\Collection;
use Orchestra\Testbench\TestCase;

class RutCollectionCallbackTest extends TestCase
{
    use RegistersPackage;

    public function testReturnsCollection()
    {
        /** @var Collection $collection */
        $collection = Rut::many([
            $rut_1 = rut()->generate(),
            $rut_2 = rut()->generate(),
        ]);

        static::assertInstanceOf(Collection::class, $collection);
        static::assertEquals($rut_1, $collection->first());
        static::assertEquals($rut_2, $collection->last());
    }

}