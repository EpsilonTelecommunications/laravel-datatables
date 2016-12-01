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
                // throw $e;
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

//    private function doJoins()
//    {
//        try {
//            foreach ($this->config->getColumns() as $column) {
//                if ($column instanceof JoinColumn) {
//                    $this->traverseQuery(
//                        $column,
//                        [
//                            'preEachQueryUp' => function (&$query, $queryName, $joinSettings) {
//                                $query->join($queryName, JoinColumn::getPropelJoinFromJoinType($joinSettings['JoinType']));
//                            },
//                            'afterAll' => function (&$query) {
//                                $query->groupBy('Id');
//                            }
//                        ]
//                    );
//                }
//            }
//        } catch (Exception $e) {
//            throw $e;
//        }
//    }

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


                        $this->traverseQuery(
                            $columnConfig,
                            [
                                'preEachQueryUp' => function (&$query) {
                                    $query->_or();
                                },
                                'postEachQueryUp' => function (&$query) {
                                    $query->_or();
                                },
                                'topJoin' => function (&$query, &$join) use ($searches) {
                                    $query->filterBy($join->getColumnName(), sprintf('%%%s%%', $searches['value']), Criteria::LIKE)->_or();
                                }
                            ]
                        );
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

    public function traverseQuery(JoinColumn $join, $callbacks = [])
    {
        $query = $this->query;

        $joinSettings = $join->getJoinSettings();

        foreach ($joinSettings as $joinSetting) {

            if ($query->getTableMap()->hasRelation($joinSetting['Name'])) {
                $relation = $query->getTableMap()->getRelation($joinSetting['Name']);
                $queryName = false;
                if ($relation->getType() == RelationMap::MANY_TO_MANY) {
                    $localTableMap = $relation->getLocalTable(); // This is actually the foreign
                    foreach ($query->getTableMap()->getRelations() as $relation) {
                        if ($relation->getType() == RelationMap::ONE_TO_MANY) {
                            $foreignTable = $relation->getLocalTable()->getRelation($joinSetting['Name'])->getForeignTable();
                            if ($foreignTable == $localTableMap) {
                                $useFunction = sprintf('use%sQuery', $relation->getName());
                                if ($this->itemIsCallable($callbacks, 'preEachQueryUp')) {
                                    $callbacks['preEachQueryUp']($query, $relation->getName(), $joinSetting, $join);
                                }
                                try {
                                    $query = $query->$useFunction(null, JoinColumn::getPropelJoinFromJoinType($joinSettings['JoinType']));
                                } catch(Exception $e) {
                                    return false;
                                }
                                if ($this->itemIsCallable($callbacks, 'postEachQueryUp')) {
                                    $callbacks['postEachQueryUp']($query, $relation->getName(), $joinSetting, $join);
                                }
                                $queryName = $joinSetting['Name'];
                            }
                        }
                    }
                } elseif ($relation->getType() == RelationMap::MANY_TO_ONE) {
                    $queryName = $relation->getName();
                }
            } else {
                $queryName = '';
            }

            $useFunction = sprintf('use%sQuery', $queryName);
            try {
                if ($this->itemIsCallable($callbacks, 'preEachQueryUp')) {
                    $callbacks['preEachQueryUp']($query, $queryName, $joinSetting, $join);
                }
                $query = $query->$useFunction(null, JoinColumn::getPropelJoinFromJoinType($joinSettings['JoinType']));
                if ($this->itemIsCallable($callbacks, 'postEachQueryUp')) {
                    $callbacks['postEachQueryUp']($query, $queryName, $joinSetting, $join);
                }
            } catch (Exception $e) {
                return false;
            }
        }

        if ($this->itemIsCallable($callbacks, 'topJoin')) {
            $callbacks['topJoin']($query, $join);
        }

        foreach (array_reverse($join->getJoinSettings()) as $joinSetting) {
            if ($this->itemIsCallable($callbacks, 'preEachQueryDown')) {
                $callbacks['preEachQueryDown']($query, $joinSetting, $join);
            }
            try {
                if ($relation->getType() == RelationMap::MANY_TO_MANY) {
                    $query = $query->endUse();
                }
                $query = $query->endUse();
            } catch (Exception $e) {
                return false;
            }
            if ($this->itemIsCallable($callbacks, 'postEachQueryDown')) {
                $callbacks['postEachQueryDown']($query, $joinSetting, $join);
            }
        }

        if ($this->itemIsCallable($callbacks, 'afterAll')) {
            $callbacks['afterAll']($query);
        }

        return true;
    }

    private function itemIsCallable($array, $index)
    {
        return (isset($array[$index]) && is_callable($array[$index]));
    }
}