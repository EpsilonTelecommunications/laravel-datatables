<?php namespace SevenD\LaravelDataTables\Columns;

class ColumnRender
{
    protected $render;

    public function __construct($render = null)
    {
        if ($render) {
            $this->setRender($render);
        }
    }

    public function getRender()
    {
        return $this->render;
    }

    public function setRender($render)
    {
        $this->render = $render;

        return $this;
    }
}