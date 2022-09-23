<?php

namespace SevenD\LaravelDataTables\Filters;

class SelectFilter extends FormElementFilter
{
    protected $options;
    protected $placeholder = null;
    protected $allowClear = true;
    protected $multiple = false;

    public function __construct($relationshipPath, $requestPath)
    {
        parent::__construct($relationshipPath, $requestPath);
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        if (is_callable($this->options)) {
            $this->options = ($this->options)();
        }
        return $this->options;
    }

    /**
     * @param mixed $options
     * @return SelectFilter
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return null
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * @param null $placeholder
     * @return SelectFilter
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowClear(): bool
    {
        return $this->allowClear;
    }

    /**
     * @param bool $allowClear
     * @return SelectFilter
     */
    public function setAllowClear(bool $allowClear): SelectFilter
    {
        $this->allowClear = $allowClear;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * @param bool $multiple
     * @return SelectFilter
     */
    public function setMultiple(bool $multiple): SelectFilter
    {
        $this->multiple = $multiple;
        return $this;
    }

    public function getSettings()
    {
        return collect([
                'allowClear' => $this->isAllowClear(),
                'placeholder' => $this->getPlaceholder(),
            ])->filter()->toArray();
    }

    public function buildHtml()
    {
        $label = sprintf(
            '<label class="field-label text-muted fs18 mb10">%s</label>',
            $this->getLabel()
        );

        $select = sprintf(
            '<select2 :options="%s" :settings="%s" v-bind:value.sync="%s"></select2>',
            json_encode($this->getOptions()),
            json_encode($this->getSettings()),
            $this->getVBindName()
        );

        return sprintf('<div class="col-md-3 mt10">%s%s</div>', $label, $select);
    }
}
