<?php namespace SevenD\LaravelDataTables\Drivers;

use SevenD\LaravelDataTables\Config\DataTableConfig;
use SevenD\LaravelDataTables\Columns\Column;
use SevenD\LaravelDataTables\Columns\JoinColumn;
use Illuminate\Http\Request;
use Propel\Runtime\ActiveQuery\Join;

class PropelDataTablesDriver
{
    private $query;
    private $request;
    private $config;

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function setConfig(DataTableConfig $config)
    {
        $this->config = $config;
        $this->query = clone $config->getQuery();
    }

    public function makeResponse()
    {
        $results = $this->runQuery();
        $className = false;

        $output = [];
        foreach ($results['data'] as $row) {
            $rowOutput = [];

            if (!$className) {
                $className = explode('\\', get_class($row));
                $className = end($className);
            }

            $rowOutput[$className . 'Object'] = $row;
            $rowData = $row->toArray();

            foreach ($this->config->getColumns() as $column) {
                if ($column instanceof JoinColumn) {
                    $getFunction = 'get' . $column->getJoinName();
                    $joinModel = $row->$getFunction();

                    // Handle Nulls from Left join queries
                    if ($joinModel) {
                        // Not Null
                        $rowOutput[$column->getName()] = $joinModel->toArray()[$column->getColumnName()];
                    } else {
                        // Null
                        $rowOutput[$column->getName()] = '';
                    }
                } else {
                    $rowOutput[$column->getName()] = $rowData[$column->getColumnName()];
                }
            }
            $output[] = $rowOutput;
        }

        return [
            'data' => $output,
            'recordsFiltered' => $results['recordsFiltered'],
            'recordsTotal' => $results['recordsTotal'],
        ];
    }

    private function runQuery()
    {
        $this->doOrderBy();

        $recordsTotal = $this->query->find()->count();

        $this->doFilter();

        $recordsFiltered = $this->query->find()->count();

        $this->doLimit();

        return [
            'data' => $this->query->find(),
            'recordsFiltered' => $recordsFiltered,
            'recordsTotal' => $recordsTotal,
        ];
    }

    private function doOrderBy()
    {
        $orders = $this->request->get('order', []);

        foreach ($orders as $order) {
            $columnConfig = $this->config->getColumnByIndex($order['column']);
            if ($columnConfig instanceof JoinColumn) {
                $column = implode('.', [$columnConfig->getJoinName(), $columnConfig->getColumnName()]);
            } else {
                $column = $this->query->getTableMap()->getPhpName() . '.' . $columnConfig->getColumnName();
            }

            $this->query->orderBy($column, $order['dir']);
        }
    }

    public function doFilter()
    {
        $searches = $this->request->get('search', []);

        if (isset($searches['value'])) {
            foreach ($this->config->getColumns() as $columnConfig) {
                if ($columnConfig->getSearchable()) {
                    if ($columnConfig instanceof JoinColumn) {
                        $column = implode('.', [$columnConfig->getJoinName(), $columnConfig->getColumnName()]);
                    } else {
                        $column = $this->query->getTableMap()->getPhpName() . '.' . $columnConfig->getColumnName();
                    }

                    $this->query->where(sprintf('%s LIKE ?', $column), sprintf('%%%s%%', $searches['value']))->_or();
                }
            }
        }
    }

    public function doLimit()
    {
        $limit = $this->request->input('length');
        $offset = $this->request->get('start');

        if ($limit) {
            $this->query->limit($limit);
        }
        if ($offset) {
            $this->query->offset($offset);
        }
    }
}