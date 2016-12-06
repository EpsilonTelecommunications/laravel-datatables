<?php namespace App\DataTables;

use App\Models\Adult;
use App\Models\AdultQuery;
use App\Models\ParentQuery;
use SevenD\LaravelDataTables\Columns\Column;
use SevenD\LaravelDataTables\Columns\GroupedJoinColumn;
use SevenD\LaravelDataTables\Columns\JoinColumn;
use SevenD\LaravelDataTables\Config\DataTableConfig;

class TestDataTable extends DataTableConfig
{
    function setUpEndpoint()
    {
        $this->setEndpoint('/data-table');
    }

    function setUpQuery()
    {
        $this->setQuery(AdultQuery::create());
    }

    function setUpColumns()
    {
        $this->addColumn(Column::create('Name'));
        $this->addColumn(Column::create('Id'));

        $this->addColumn(JoinColumn::create([
            [
                'Name' => 'Gender',
                'JoinType' => JoinColumn::LEFT_JOIN,
            ], 'Name']));

        $this->addColumn(
            GroupedJoinColumn::create(['Child', 'Name'])
                ->setSeparator('-')
        );

        $this->addColumn(
            GroupedJoinColumn::create(['Gender', 'Race', 'SubRace', 'Name'])
                ->setSeparator('-')
        );

        $this->addColumn(
            JoinColumn::create(['Child', 'Name'])
        );

        $this->addColumn(JoinColumn::create(['Gender', 'Race', 'Name']));

        $this->addColumn(JoinColumn::create(['Gender', 'Something']));

        $this->addColumn(JoinColumn::create(['Gender', 'SomethingElse', 'Name']));

        $this->addColumn(Column::create('DateOfBirth'));
    }

}