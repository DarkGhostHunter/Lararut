<?php

namespace Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

class RutBlueprintMacrosTest extends TestCase
{
    use RegistersPackage;

    public function testHelperReturnsRutNumberColumn()
    {
        $blueprint = new Blueprint('test_table');

        $number = $blueprint->rut();

        $this->assertInstanceOf(ColumnDefinition::class, $number);
    }

    public function testCreatesDatabaseWithRutColumns()
    {
        /** @var \Illuminate\Database\Schema\Builder $schema */
        $schema = $this->app->make('db')->connection()->getSchemaBuilder();

        /** @var \Illuminate\Database\Schema\Blueprint $blueprint */
        $blueprint = null;

        $schema->create('test_table', function(Blueprint $table) use (&$blueprint) {
            $table->rut();
            $blueprint = $table;
        });

        $this->assertTrue($schema->hasColumn('test_table', 'rut_num'));
        $this->assertTrue($schema->hasColumn('test_table', 'rut_vd'));
        $this->assertEquals('integer', $schema->getColumnType('test_table', 'rut_num'));
        $this->assertEquals('string', $schema->getColumnType('test_table', 'rut_vd'));

        $this->assertFalse($blueprint->getColumns()[0]->autoIncrement);
        $this->assertTrue($blueprint->getColumns()[0]->unsigned);

        $this->assertEquals(1, $blueprint->getColumns()[1]->length);

        /** @var \Illuminate\Database\Schema\Blueprint $blueprint */
        $blueprint = null;

        $schema->create('test_table_with_index', function(Blueprint $table) use (&$blueprint) {
            $table->rut()->index()->primary()->unique();
            $blueprint = $table;
        });

        $this->assertTrue($blueprint->getColumns()[0]->index);
        $this->assertTrue($blueprint->getColumns()[0]->primary);
        $this->assertTrue($blueprint->getColumns()[0]->unique);
    }

}