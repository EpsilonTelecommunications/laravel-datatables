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

    public function buildHtml(): string
    {
        return $this->getTemplate();
    }

    public function setTemplate(string $htmlTemplate): static
    {
        $this->template = $htmlTemplate;
        return $this;
    }

    public function getTemplate(): string
    {
        return $this->template ?? parent::buildHtml();
    }

    protected function toJSObject($data)
    {
        return e(json_encode($data));
    }

}
