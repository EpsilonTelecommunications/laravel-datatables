<?php namespace SevenD\LaravelDataTables\Config;

use SevenD\LaravelDataTables\Columns\BaseColumn;
use SevenD\LaravelDataTables\Columns\ColumnRender;

abstract class DataTableConfig
{
    protected $query;
    protected $endpoint;
    protected $defaultColumnType = '';
    protected $columns = [];
    protected $columnsCsv = [];
    protected $sorting = [];

    public function __construct()
    {
        $this->setUpEndpoint();
        $this->setUpQuery();
        $this->setUpColumns();
    }

    abstract function setUpEndpoint();

    abstract function setUpQuery();

    abstract function setUpColumns();

    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    public function query($closure)
    {
        $closure($this->query);
        
        return $this;
    }

    protected function getColumnTypePropertyName($type)
    {
        if (is_null($type)) {
            $type = $this->getDefaultColumnType();
        }

        $type = sprintf('columns%s', studly_case($type));
        if (property_exists($this, $type)) {
            return $type;
        }
        return 'columns';
    }

    public function addColumn(BaseColumn $column, $type = null)
    {
        $columnType = $this->getColumnTypePropertyName($type);
        $this->$columnType[$column->getName()] = $column;
		return $this;
    }

    public function getColumns($type = null)
    {
        $columnType = $this->getColumnTypePropertyName($type);
        return $this->$columnType;
    }

    public function getColumnWithIndex($type = null)
    {
        return array_values($this->getColumns($type));
    }

    public function getColumn($name)
    {
        $columns = $this->getColumns();
        if (isset($columns[$name])) {
            return $columns[$name];
        }
        return null;
    }

    public function getColumnByIndex($index, $type = null)
    {
        $count = 0;
        foreach ($this->getColumns($type) as $column) {
            if ($count == $index) {
                return $column;
            }
            $count++;
        }
        return null;
    }

    public function getIndexForColumn(BaseColumn $column, $type = null)
    {
        foreach ($this->getColumnWithIndex($type) as $key => $col) {
            if ($column == $col) {
                return $key;
            }
        }
        return null;
    }

    public function sortBy($columns, $type = null)
    {
        $sorting = [];
        foreach ($columns as $sortColumn) {
            foreach ($this->getColumnWithIndex($type) as $key => $column) {
                if ($sortColumn[0]->getColumnName() == $column->getColumnName()) {
                    $sorting[] = [$key, $sortColumn[1]];
                }
            }
        }
        $this->sorting = $sorting;
    }

    public function getJson($type = null)
    {
        $config = [];

        $config['endpoint'] = $this->endpoint;

        $config['columns'] = $this->getColumnsJson($type);

        $config['order'] = $this->sorting;

        return json_encode($config, true);
    }

    public function getHtml($id, $withJson = false)
    {
        $html = [];

        $html[] = sprintf(
            '<table id="%s" class="table table-striped table-hover dataTable no-footer"%s>',
            $id,
            ($withJson) ? sprintf(' data-datatableconfig="%s"', htmlentities($this->getJson())) : ''
        );
        $html[] = '<thead>';
        foreach ($this->getColumns() as $columnConfig) {
            $html[] = sprintf('<th>%s</th>', $columnConfig->getTitle() ?: $columnConfig->getName());
        }
        $html[] = '</thead>';
        $html[] = '<tbody>';
        $html[] = '</tbody>';
        $html[] = '</table>';

        return implode('', $html);
    }

    public function getHtmlWithJson($id) {
        return $this->getHtml($id, true);
    }

    private function getColumnsJson($type = null)
    {
        $columns = [];

        foreach ($this->getColumns($type) as $columnConfig) {
            $column = [];
            $column['data'] = $columnConfig->getName();

            if (!is_null($columnConfig->getCellType())) {
                $column['cellType'] = $columnConfig->getCellType();
            }
            if (!is_null($columnConfig->getClassName())) {
                $column['className'] = $columnConfig->getClassName();
            }
            if (!is_null($columnConfig->getContentPadding())) {
                $column['contentPadding'] = $columnConfig->getContentPadding();
            }
            if (!is_null($columnConfig->getCreatedCell())) {
                $column['createdCell'] = $columnConfig->getCreatedCell();
            }
            if (!is_null($columnConfig->getDefaultContent())) {
                $column['defaultContent'] = $columnConfig->getDefaultContent();
            }
            if (!is_null($columnConfig->getName())) {
                $column['name'] = $columnConfig->getName();
            }
            if (!is_null($columnConfig->getOrderable())) {
                $column['orderable'] = $columnConfig->getOrderable();
            }
            if (!is_null($columnConfig->getOrderData())) {
                $column['orderData'] = $columnConfig->getOrderData();
            }
            if (!is_null($columnConfig->getOrderDataType())) {
                $column['orderDataType'] = $columnConfig->getOrderDataType();
            }
            if (!is_null($columnConfig->getOrderSequence())) {
                $column['orderSequence'] = $columnConfig->getOrderSequence();
            }
            if (!is_null($columnConfig->getRender()) && !($columnConfig->getRender() instanceof ColumnRender)) {
                $column['render'] = $columnConfig->getRender();
            }
            if (!is_null($columnConfig->getSearchable())) {
                $column['searchable'] = $columnConfig->getSearchable();
            }
            if (!is_null($columnConfig->getTitle())) {
                $column['title'] = $columnConfig->getTitle();
            }
            if (!is_null($columnConfig->getType())) {
                $column['type'] = $columnConfig->getType();
            }
            if (!is_null($columnConfig->getVisible())) {
                $column['visible'] = $columnConfig->getVisible();
            }
            if (!is_null($columnConfig->getWidth())) {
                $column['width'] = $columnConfig->getWidth();
            }
            $columns[] = $column;
        }

        return $columns;
    }

    /**
     * @return string
     */
    public function getDefaultColumnType()
    {
        return $this->defaultColumnType;
    }

    /**
     * @param string $defaultColumnType
     * @return DataTableConfig
     */
    public function setDefaultColumnType($defaultColumnType)
    {
        $this->defaultColumnType = $defaultColumnType;
        return $this;
    }
}