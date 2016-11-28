<?php namespace SevenD\LaravelDataTables\Drivers;

use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Map\RelationMap;
use SevenD\LaravelDataTables\Columns\BaseColumn;
use SevenD\LaravelDataTables\Config\DataTableConfig;
use SevenD\LaravelDataTables\Columns\Column;
use SevenD\LaravelDataTables\Columns\JoinColumn;
use Illuminate\Http\Request;
use Propel\Runtime\ActiveQuery\Join;
use Exception;

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
                $className = $this->getClassName($row);
            }

            try {
                $rowOutput[$className . 'Object'] = $row;
                foreach ($this->config->getColumns() as $column) {
                    if ($column instanceof JoinColumn) {
                        $joinModel = $row;
                        foreach ($column->getJoins() as $join) {
                            $getFunction = 'get' . $join['Name'];
                            if (method_exists($joinModel, $getFunction)) {
                                $rowOutput[$this->getClassName($joinModel) . 'Object'] = $joinModel;
                                $joinModel = $joinModel->$getFunction();
                                if (is_null($joinModel) && $join['JoinType'] == JoinColumn::INNER_JOIN) {
                                    throw new Exception; // Don't want this row as we cannot 'join'
                                } else {
                                    $rowOutput[$column->getName()] = '';
                                }
                            } else {
                                $rowOutput[$column->getName()] = '';
                            }
                        }
                        if ($joinModel) {

                            if ($joinModel instanceof ObjectCollection) {
                                $joinModels = [];
                                foreach ($joinModel as $jm) {
                                    $joinModels[] = $jm;
                                }
                            }

                            $getFunction = sprintf('get%s', $column->getColumnName());
                            if (is_callable([$joinModel, $getFunction])) {
                                $rowOutput[$this->getClassName($joinModel) . 'Object'] = $joinModel;
                                $rowOutput[$column->getName()] = $joinModel->$getFunction();
                            }
                        } else {
                            $rowOutput[$column->getName()] = '';
                        }
                    } else {
                        $getFunction = sprintf('get%s', $column->getColumnName());
                        if (is_callable([$row, $getFunction])) {
                            $rowOutput[$column->getName()] = $row->$getFunction();
                        }
                    }
                }
                $output[] = $rowOutput;
            } catch (Exception $e) {
                // Mute
                throw $e;
            }
        }

        return [
            'data' => $output,
            'recordsFiltered' => $results['recordsFiltered'],
            'recordsTotal' => $results['recordsTotal'],
        ];
    }

    private function getClassName($class)
    {
        $className = explode('\\', get_class($class));
        return end($className);
    }

    private function runQuery()
    {
        $this->doJoins();

        $this->doOrderBy();

        $recordsTotal = $this->query->count();

        $this->doFilter();

        $recordsFiltered = $this->query->count();

        $this->doLimit();

        return [
            'data' => $this->query->find(),
            'recordsFiltered' => $recordsFiltered,
            'recordsTotal' => $recordsTotal,
        ];
    }

    private function doJoins()
    {
        try {
            foreach ($this->config->getColumns() as $column) {
                if ($column instanceof JoinColumn) {
                    $query = $this->query;
                    $count = 0;
                    foreach ($column->getJoinSettings() as $settings) {
                        if ($query->getTableMap()->hasRelation($settings['Name'])) {
                            $relation = $query->getTableMap()->getRelation($settings['Name']);
                            $queryName = false;
                            if ($relation->getType() == RelationMap::MANY_TO_MANY) {
                                $localTableMap = $relation->getLocalTable(); // This is actually the foreign
                                foreach ($query->getTableMap()->getRelations() as $relation) {
                                    if ($relation->getType() == RelationMap::ONE_TO_MANY) {
                                        $foreignTable = $relation->getLocalTable()->getRelation($settings['Name'])->getForeignTable();
                                        if ($foreignTable == $localTableMap) {
                                            $query->join($relation->getName(), JoinColumn::getPropelJoinFromJoinType($settings['JoinType']));
                                            $useQueryFunction = sprintf('use%sQuery', $relation->getName());
                                            $query = $query->$useQueryFunction();
                                            $queryName = $settings['Name'];
                                            $count++;
                                        }
                                    }
                                }
                            } elseif ($relation->getType() == RelationMap::MANY_TO_ONE) {
                                $queryName = $relation->getName();
                            }

                            if ($queryName != false) {
                                $query->join($queryName, JoinColumn::getPropelJoinFromJoinType($settings['JoinType']));
                                $useQueryFunction = sprintf('use%sQuery', $queryName);
                                $query = $query->$useQueryFunction();
                                $count++;
                            }
                        }
                    }
                    for ($count; $count > 0; $count--) {
                        $query = $query->endUse();
                    }
                    $this->query = $query;
                }
            }
            $this->query->groupBy('Id');
        } catch (Exception $e) {
            throw $e;
        }
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

        if (isset($searches['value']) && strlen($searches['value'])) {
            foreach ($this->config->getColumns() as $columnConfig) {
                if ($columnConfig->getSearchable()) {
                    $query = $this->query;
                    if ($columnConfig instanceof JoinColumn) {
                        foreach ($columnConfig->getJoinSettings() as $settings) {
                            $query->join($settings['Name'], $settings['JoinType']);
                            $useQueryFunction = sprintf('use%sQuery', $settings['Name']);
                            $query = $query->_or()->$useQueryFunction();
                        }

                        if (!$this->isNeverSearchable($query, $columnConfig)) {
                            $query->filterBy($columnConfig->getColumnName(), sprintf('%%%s%%', $searches['value']), Criteria::LIKE)->_or();
                        }

                        foreach (array_reverse($columnConfig->getJoinSettings()) as $settings) {
                            $query = $query->endUse()->_or();
                        }
                    } else {
                        $column = $this->query->getTableMap()->getPhpName() . '.' . $columnConfig->getColumnName();
                        if (!$this->isNeverSearchable($this->query, $columnConfig)) {
                            $this->query->where(sprintf('%s LIKE ?', $column), sprintf('%%%s%%', $searches['value']))->_or();
                        }
                    }
                }
            }
        }
    }

    private function isNeverSearchable($query, $columnConfig)
    {
        $tableMap = $query->getTableMap();
        $columnName = snake_case($columnConfig->getColumnName());
        if ($tableMap->hasColumn($columnName)) {
            return in_array(
                $tableMap->getColumn($columnName)->getType(),
                [
                    PropelTypes::BINARY,
                    PropelTypes::BLOB,
                    PropelTypes::BLOB_NATIVE_TYPE,
                    PropelTypes::BOOLEAN,
                    PropelTypes::TIMESTAMP,
                    PropelTypes::DATE,
                    PropelTypes::TIME
                ]
            );
        }
        return true;
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