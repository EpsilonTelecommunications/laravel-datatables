<?php namespace SevenD\LaravelDataTables\Config;

use Illuminate\Support\Str;
use SevenD\LaravelDataTables\Filters\Filter;
use SevenD\LaravelDataTables\Columns\BaseColumn;
use SevenD\LaravelDataTables\Columns\ColumnRender;
use SevenD\LaravelDataTables\Filters\FormElementFilter;

abstract class DataTableConfig
{
    protected $query;
    protected $endpoint;
    protected $defaultColumnType = '';
    protected $columns = [];
    protected $columnsCsv = [];
    protected $sorting = [];
    protected $filters = [];
    protected $title;
    protected $csvTitle;
    protected $timezone = 'UTC';
    protected $includeConfigInResponse = false;
    protected  $filterInfoMessage = null;

    public function __construct()
    {
        $this->setUpEndpoint();
        $this->setUpQuery();
        $this->setUpColumns();
    }

    abstract function setUpEndpoint();

    abstract function setUpQuery();

    abstract function setUpColumns();

    public function getTitle()
    {
        return $this->title ?? Str::before(basename(static::class), 'DataTable');
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getCsvTitle()
    {
        return (is_null($this->csvTitle)) ? $this->csvTitle : $this->getTitle();
    }

    public function setCsvTitle($csvTitle)
    {
        $this->csvTitle = $csvTitle;
        return $this;
    }

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
        $this->{$columnType}[$column->getName()] = $column;
        return $this;
    }

    public function removeColumn(BaseColumn $removeColumn, $type = null)
    {
        $columnType = $this->getColumnTypePropertyName($type);
        $i = 0;
        foreach ($this->getColumns($type) as $key => $column) {
            if ($removeColumn == $column) {
                array_splice($this->$columnType, $i, 1);
            }
            $i++;
        }
    }

    public function removeColumnByName($name, $type = null)
    {
        $this->removeColumn($this->getColumn($name, $type), $type);
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

    public function getColumn($name, $type = null)
    {
        $columns = $this->getColumns($type);
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

    public function getConfigArray($type = null)
    {
        $config = [];

        $config['endpoint'] = $this->endpoint;

        $config['columns'] = $this->getColumnsJson($type);

        $config['order'] = $this->sorting;

        return $config;
    }

    public function getJson($type = null)
    {
        return json_encode($this->getConfigArray($type), JSON_PRETTY_PRINT);
    }

    public function getHtml($id, $withJson = false)
    {
        $html = [];

        if (count($this->getFilters()) > 0) {
            $html[] = sprintf('<data-table-filter data-table-id="%s">', $id);
            $html[] = '<template slot="form">';
        }

        if ($this->getFilterInfoMessage()) {
            $html[] = '<div class="col-md-12">';
            $html[] = '<div class="alert alert-info mbn">';
            $html[] = '<i class="fa fa-info-circle mr5"></i>';
            $html[] = $this->getFilterInfoMessage();
            $html[] = '</div>';
            $html[] = '</div>';
        }

        foreach ($this->getFilters() as $filter) {
            if ($filter instanceof FormElementFilter) {
                $html[] = $filter->buildHtml();
            }
        }

        if (count($this->getFilters()) > 0) {
            $html[] = '</template>';
            $html[] = '</data-table-filter>';
        }

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

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     * @return DataTableConfig
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * @return boolean
     */
    public function includeConfigInResponse()
    {
        return $this->includeConfigInResponse;
    }

    /**
     * @param boolean $includeConfigInResponse
     * @return DataTableConfig
     */
    public function setIncludeConfigInResponse($includeConfigInResponse)
    {
        $this->includeConfigInResponse = $includeConfigInResponse;
        return $this;
    }


    public function addFilter(Filter $filter): self
    {
        $this->filters[$filter->getHash()] = $filter;

        return $this;
    }

    public function removeFilter(Filter $filter): self
    {
        unset($this->filters[$filter->getHash()]);

        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getFilterInfoMessage(): ?string
    {
        return $this->filterInfoMessage;
    }

    public function setFilterInfoMessage(?string $filterInfoMessage): void
    {
        $this->filterInfoMessage = $filterInfoMessage;
    }
}