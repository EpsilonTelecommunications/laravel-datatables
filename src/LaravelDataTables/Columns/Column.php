<?php namespace SevenD\LaravelDataTables\Columns;

use SevenD\LaravelDataTables\Columns\BaseColumn;

class Column extends BaseColumn
{
    public function __construct($columnName = null, $settings = [])
    {
        parent::__construct($columnName, $settings);
    }
    
    public static function create($columnName = null, $settings = [])
    {
	    return new Column($columnName, $settings);
    }
}