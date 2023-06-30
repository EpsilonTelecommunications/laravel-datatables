<?php

namespace SevenD\LaravelDataTables\Filters;

class SelectMultipleFilter extends SelectFilter
{
    protected $multiple = true;
    protected $template;

    public function getSettings(): array
    {
        return array_merge(parent::getSettings(), [
            'multiple' => $this->isMultiple()
        ]);
    }

    protected function toJSObject($data)
    {
        return e(json_encode($data));
    }
}
