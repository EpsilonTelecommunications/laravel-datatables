<?php namespace SevenD\LaravelDataTables\Columns;

class GroupedJoinColumn extends JoinColumn
{
    protected $separator = ',';

    public static function create($columnDefinition = null, $settings = [])
    {
        return new GroupedJoinColumn($columnDefinition, $settings);
    }

    public function getSeparator()
    {
        return $this->separator;
    }

    public function setSeparator($separator)
    {
        $this->separator = $separator;
        return $this;
    }
}