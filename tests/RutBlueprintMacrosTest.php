<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Orchestra\Testbench\TestCase;

class RutBlueprintMacrosTest extends TestCase
{
    use RegistersPackage;

    public function testHelperReturnsRutNumberColumn()
    {
        $blueprint = new Blueprint('test_table');

        $number = $blueprint->rut();

        static::assertInstanceOf(ColumnDefinition::class, $number);
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

        static::assertTrue($schema->hasColumn('test_table', 'rut_num'));
        static::assertTrue($schema->hasColumn('test_table', 'rut_vd'));
        static::assertEquals('integer', $schema->getColumnType('test_table', 'rut_num'));
        static::assertEquals('string', $schema->getColumnType('test_table', 'rut_vd'));

        static::assertFalse($blueprint->getColumns()[0]->autoIncrement);
        static::assertTrue($blueprint->getColumns()[0]->unsigned);

        static::assertEquals(1, $blueprint->getColumns()[1]->length);

        $schema->create('test_table_with_index', function(Blueprint $table) {
            $table->rut()->index();
        });

        $indexes = $schema->getConnection()
            ->getDoctrineSchemaManager()
            ->listTableDetails('test_table_with_index')
            ->getIndexes();

        static::assertArrayHasKey('test_table_with_index_rut_num_index', $indexes);

        $schema->create('test_table_with_primary', function(Blueprint $table) {
            $table->rut()->primary();
        });

        $primary = $schema->getConnection()
            ->getDoctrineSchemaManager()
            ->listTableDetails('test_table_with_primary')
            ->getPrimaryKey();

        static::assertCount(1, $primary->getColumns());
        static::assertContains('rut_num', $primary->getColumns());

        $schema->create('test_table_with_unique', function(Blueprint $table) {
            $table->rut()->unique();
        });

        $unique = $schema->getConnection()
            ->getDoctrineSchemaManager()
            ->listTableDetails('test_table_with_unique')
            ->getIndexes('rut_num');

        static::assertCount(1, $unique);
        static::assertArrayHasKey('test_table_with_unique_rut_num_unique', $unique);
    }

}