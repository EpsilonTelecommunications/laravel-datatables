<?php

namespace SevenD\LaravelDataTables\Filters;

abstract class FormElementFilter extends Filter
{
    protected $label = null;

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
        return "dynamic-{$this->getHash()}";
    }
}
