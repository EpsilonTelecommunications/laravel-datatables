<?php

namespace SevenD\LaravelDataTables\Filters;

class SelectFilter extends FormElementFilter
{
    protected $options;
    protected $placeholder = 'Please select';
    protected $allowClear = true;
    protected $multiple = false;
    protected $defaultOption;

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
            '<label class="field-label text-muted fs18 mb10">%s <span v-text="test"></span></label>',
            $this->getLabel()
        );

        $select = sprintf(
            '<select2 data-requestpath="%s" data-defaultvalue="%s" :options="%s" :settings="%s" v-bind:value.sync="%s"></select2>',
            $this->getRequestPath(),
            $this->getDefaultOption(),
            $this->toJSObject($this->getOptions()),
            $this->toJSObject($this->getSettings()),
            $this->getVBindName()
        );

        return sprintf('<div class="%s mt10" data-dtfevbindname="%s">%s%s</div>',
            $this->getCssCol(),
            $this->getVBindName(),
            $label,
            $select);
    }


    /**
     * @return mixed
     */
    public function getDefaultOption()
    {
        return $this->defaultOption;
    }

    /**
     * @param mixed $defaultOption
     * @return SelectFilter
     */
    public function setDefaultOption($defaultOption)
    {
        $this->defaultOption = $defaultOption;
        return $this;
    }

    public function setDefaultByIndex($index)
    {
        $this->setDefaultOption(
            data_get(collect($this->options)->get($index), 'id')
        );
        return $this;
    }
}
