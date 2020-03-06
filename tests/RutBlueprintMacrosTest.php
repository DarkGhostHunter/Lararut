<?php

namespace Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\DB;
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

        $conn = \Doctrine\DBAL\DriverManager::getConnection([
            'pdo' => DB::connection('testing')->getPdo()
        ]);

        $schema->create('test_table_with_index', function(Blueprint $table) {
            $table->rut()->index();
        });

        $indexes = $conn->getSchemaManager()->listTableDetails('test_table_with_index')->getIndexes();

        $this->assertArrayHasKey('test_table_with_index_rut_num_index', $indexes);

        $schema->create('test_table_with_primary', function(Blueprint $table) {
            $table->rut()->primary();
        });

        $primary = $conn->getSchemaManager()->listTableDetails('test_table_with_primary')->getPrimaryKey();

        $this->assertCount(1, $primary->getColumns());
        $this->assertContains('rut_num', $primary->getColumns());

        $schema->create('test_table_with_unique', function(Blueprint $table) {
            $table->rut()->unique();
        });

        $unique = $conn->getSchemaManager()->listTableDetails('test_table_with_unique')->getIndexes('rut_num');

        $this->assertCount(1, $unique);
        $this->assertArrayHasKey('test_table_with_unique_rut_num_unique', $unique);
    }

}