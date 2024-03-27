<?php

namespace SevenD\LaravelDataTables\Filters;

use Illuminate\Support\Str;

abstract class FormElementFilter extends Filter
{
    protected $label = null;

    public function __construct($relationshipPath, $requestPath = null)
    {
        parent::__construct($relationshipPath, $requestPath);
        $this->setLabel(
            ucwords(
                Str::of(
                    $this->getRequestPath()
                )->snake()->replaceLast('_id', '')->replace('_', ' ')
            )
        );
    }

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
        $data = collect($data)
            ->map(function ($item) {
                return str_replace("'", "\'", $item);
            })
            ->toJson();

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
