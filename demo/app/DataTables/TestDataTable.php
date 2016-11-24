<?php namespace App\DataTables;

use App\Models\AdultQuery;
use App\Models\ParentQuery;
use SevenD\LaravelDataTables\Columns\Column;
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
        $this->addColumn(Column::create('Id'));
        $this->addColumn(Column::create('Name'));

        $this->addColumn(JoinColumn::create([
            [
                'Name' => 'Gender',
                'JoinType' => JoinColumn::LEFT_JOIN,
            ], 'Name']));

//        $this->addColumn(JoinColumn::create(['Gender', 'Name']));

//        $this->addColumn(JoinColumn::create(['Gender', 'Race', 'Name']));
//
//        $this->addColumn(JoinColumn::create(['Gender', 'Race', 'Test']));


        $this->addColumn(Column::create('DateOfBirth'));
    }

}