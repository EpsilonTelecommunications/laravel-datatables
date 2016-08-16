<?php namespace SevenD\LaravelDataTables\Columns;

use Stringy\Stringy;

abstract class BaseColumn
{
    protected $columnAttributes;

    public function __construct($columnName = null, $settings = [])
    {
        if ($columnName) {
            $this->setColumnName($columnName);
        }
    }

    public function getColumnAttribute($key)
    {
        if (isset($this->columnAttributes[$key])) {
            return $this->columnAttributes[$key];
        } else {
            return null;
        }
    }

    public function setColumnAttribute($key, $value)
    {
        $this->columnAttributes[$key] = $value;

        return $this;
    }

    public function getColumnName()
    {
        return $this->getColumnAttribute('columnName');
    }

    public function setColumnName($value)
    {
        $this->setColumnAttribute('columnName', $value);

        if (is_null($this->getName())) {
            $this->setName($value);
            $name = (string) Stringy::create($value)->underscored()->humanize();
            $this->setTitle($name);
        }

        return $this;
    }

    public function getCellType() {
        return $this->getColumnAttribute('cellType');
    }

    public function setCellType($value) {
        $this->setColumnAttribute('cellType', $value);
        return $this;
    }

    public function getClassName() {
        return $this->getColumnAttribute('className');
    }

    public function setClassName($value) {
        $this->setColumnAttribute('className', $value);
        return $this;
    }

    public function getContentPadding() {
        return $this->getColumnAttribute('contentPadding');
    }

    public function setContentPadding($value) {
        $this->setColumnAttribute('contentPadding', $value);
        return $this;
    }

    public function getCreatedCell() {
        return $this->getColumnAttribute('createdCell');
    }

    public function setCreatedCell($value) {
        $this->setColumnAttribute('createdCell', $value);
        return $this;
    }

    public function getDefaultContent() {
        return $this->getColumnAttribute('DefaultContent');
    }

    public function setDefaultContent($value) {
        $this->setColumnAttribute('DefaultContent', $value);
        return $this;
    }

    public function getName()
    {
        return $this->getColumnAttribute('name');
    }

    public function setName($value)
    {
        $this->setColumnAttribute('name', $value);
        return $this;
    }

    public function getOrderable() {
        return $this->getColumnAttribute('orderable');
    }

    public function setOrderable($value) {
        $this->setColumnAttribute('orderable', $value);
        return $this;
    }

    public function getOrderData() {
        return $this->getColumnAttribute('orderData');
    }

    public function setOrderData($value) {
        $this->setColumnAttribute('orderData', $value);
        return $this;
    }

    public function getOrderDataType() {
        return $this->getColumnAttribute('orderDataType');
    }

    public function setOrderDataType($value) {
        $this->setColumnAttribute('orderDataType', $value);
        return $this;
    }

    public function getOrderSequence() {
        return $this->getColumnAttribute('orderSequence');
    }

    public function setOrderSequence($value) {
        $this->setColumnAttribute('orderSequence', $value);
        return $this;
    }

    public function getRender()
    {
        return $this->getColumnAttribute('render');
    }

    public function setRender($value)
    {
        $this->setColumnAttribute('render', $value);
        return $this;
    }

    public function getSearchable()
    {
        return $this->getColumnAttribute('searchable');
    }

    public function setSearchable($value)
    {
        $this->setColumnAttribute('searchable', $value);
        return $this;
    }

    public function getTitle() {
        return $this->getColumnAttribute('title');
    }

    public function setTitle($value) {
        $this->setColumnAttribute('title', $value);
        return $this;
    }

    public function getType() {
        return $this->getColumnAttribute('type');
    }

    public function setType($value) {
        $this->setColumnAttribute('type', $value);
        return $this;
    }

    public function getVisible()
    {
        return $this->getColumnAttribute('visible');
    }

    public function setVisible($value)
    {
        $this->setColumnAttribute('visible', $value);
        return $this;
    }

    public function getWidth() {
        return $this->getColumnAttribute('width');
    }

    public function setWidth($value) {
        $this->setColumnAttribute('width', $value);
        return $this;
    }
}