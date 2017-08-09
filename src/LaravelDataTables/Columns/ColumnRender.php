<?php namespace SevenD\LaravelDataTables\Columns;

class ColumnRender
{
    protected $render;
    protected $renderData = [];

    public function __construct($render = null, $renderData = null)
    {
        if ($render) {
            $this->setRender($render);
        }

        if ($renderData) {
            $this->setRenderData($renderData);
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

    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->setRenderData($key);
        } elseif (!is_null($value)) {
            $this->addRenderData([$key => $value]);
        }
    }

    public function getRenderData()
    {
        return $this->renderData;
    }

    public function setRenderData($renderData)
    {
        $this->renderData = $renderData;

        return $this;
    }

    public function addRenderData($item)
    {
        $this->renderData = array_merge($item, $this->getRenderData()); // We don't want to overwrite anything that already exists.
    }
}