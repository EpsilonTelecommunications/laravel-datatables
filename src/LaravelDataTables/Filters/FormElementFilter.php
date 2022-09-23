<?php

namespace SevenD\LaravelDataTables\Filters;

abstract class FormElementFilter extends Filter
{
    protected $label = null;

    abstract public function buildHtml();

    /**
     * @return null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param null $label
     * @return FormElementFilter
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    public function getVBindName()
    {
        return "dynamic_{$this->getHash()}";
    }

    protected function toJSObject($data)
    {
        $data = json_encode($data);
        $data = str_replace('{"', '{\'', $data);
        $data = str_replace('{"', '{\'', $data);
        $data = str_replace('"}', '\'}', $data);
        $data = str_replace(',"', ',\'', $data);
        $data = str_replace('",', '\',', $data);
        $data = str_replace('":', '\':', $data);
        $data = str_replace(':"', ':\'', $data);
        return $data;
    }
}
