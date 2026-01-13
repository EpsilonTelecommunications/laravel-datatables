<?php

namespace SevenD\LaravelDataTables\Filters;

use DateTime;

class DateRangeFilter extends FormElementFilter
{
    protected $cast = self::CAST_DATETIME;
    protected $withTime = true;
    protected $labelFrom = null;
    protected $labelTo = null;
    protected $placeholderFrom = 'Please select start date';
    protected $placeholderTo = 'Please select end date';

    public function isWithTime(): bool
    {
        return $this->withTime;
    }

    public function setWithTime(bool $withTime)
    {
        $this->withTime = $withTime;

        return $this;
    }

    /**
     * @return null
     */
    public function getLabelFrom()
    {
        return $this->labelFrom ? $this->labelFrom : 'From ' . $this->getLabel();
    }

    /**
     * @param null $labelFrom
     */
    public function setLabelFrom($labelFrom)
    {
        $this->labelFrom = $labelFrom;

        return $this;
    }

    /**
     * @return null
     */
    public function getLabelTo()
    {
        return $this->labelTo ? $this->labelTo : 'To ' . $this->getLabel();
    }

    /**
     * @param null $labelTo
     */
    public function setLabelTo($labelTo)
    {
        $this->labelTo = $labelTo;

        return $this;
    }

    public function getPlaceholderFrom(): string
    {
        return $this->placeholderFrom;
    }

    public function setPlaceholderFrom(string $placeholderFrom)
    {
        $this->placeholderFrom = $placeholderFrom;

        return $this;
    }

    public function getPlaceholderTo(): string
    {
        return $this->placeholderTo;
    }

    public function setPlaceholderTo(string $placeholderTo)
    {
        $this->placeholderTo = $placeholderTo;

        return $this;
    }

    public function buildHtml()
    {
        $labelFrom = sprintf(
            '<label class="field-label text-muted fs18 mb10">%s</label>',
            $this->getLabelFrom()
        );

        $from = sprintf(
            '<date-picker data-requestpath="%s[0]" :with-time="%s" placeholder="%s" class="gui-input"></date-picker>',
            $this->getRequestPath(),
            $this->isWithTime() ? 'true' : 'false',
            $this->getPlaceholderFrom()
        );

        $labelTo = sprintf(
            '<label class="field-label text-muted fs18 mb10">%s</label>',
            $this->getLabelTo()
        );

        $to = sprintf(
            '<date-picker data-requestpath="%s[1]" :with-time="%s" placeholder="%s" class="gui-input"></date-picker>',
            $this->getRequestPath(),
            $this->isWithTime() ? 'true' : 'false',
            $this->getPlaceholderTo()
        );

        return sprintf('<div class="%s mt10" data-dtfevbindname="%s">%s%s</div><div class="%s mt10" data-dtfevbindname="%s">%s%s</div>',
            $this->getCssCol(),
            $this->getVBindName(),
            $labelFrom,
            $from,
            $this->getCssCol(),
            $this->getVBindName(),
            $labelTo,
            $to
        );
    }

    public function castValue($value)
    {
        $value = parent::castValue($value);

        return $value->sort(
            fn (DateTime $a, DateTime $b) => $a->getTimestamp() <=> $b->getTimestamp()
        )->values()
            ->mapWithKeys(
                fn (DateTime $date, $key) => [ $key === 0 ? 'min' : 'max' => $date->format('Y-m-d H:i:s') ]
            )
            ->toArray();
    }
}
