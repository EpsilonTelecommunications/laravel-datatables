<?php namespace SevenD\LaravelDataTables\Columns;

use SevenD\LaravelDataTables\Columns\BaseColumn;

class VirtualColumn extends BaseColumn
{
    protected $sql = '';

    public function __construct($columnName = null, $settings = [])
    {
        parent::__construct($columnName, $settings);
    }

    public static function create($columnName = null, $settings = [])
    {
        return new VirtualColumn($columnName, $settings);
    }

    public function setColumnSql($sql)
    {
        $this->sql = $sql;
        return $this;
    }

    public function getColumnSql()
    {
        return $this->sql;
    }
}