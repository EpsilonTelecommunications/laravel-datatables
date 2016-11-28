<?php namespace SevenD\LaravelDataTables\Columns;

class GroupedJoinColumn extends JoinColumn
{
    protected $separator = ',';

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