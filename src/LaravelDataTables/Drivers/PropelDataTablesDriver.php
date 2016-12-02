<?php namespace SevenD\LaravelDataTables\Drivers;

use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Map\RelationMap;
use SevenD\LaravelDataTables\Columns\BaseColumn;
use SevenD\LaravelDataTables\Columns\GroupedJoinColumn;
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

                        $this->traverseQuery(
                            $column,
                            [
                                'postEachQueryUp' => function($query, $relation) use ($column, &$joinModel, &$rowOutput) {
                                    $getFunction = sprintf('get%s', ($relation->getType() == RelationMap::MANY_TO_MANY) ? $relation->getPluralName() : $relation->getName());
                                    if (method_exists($joinModel, $getFunction)) {
                                        $joinModel = $joinModel->$getFunction();
                                    } else {
                                        $rowOutput[$column->getName()] = '';
                                    }
                                },
                                'topJoin' => function() use ($column, &$joinModel, &$rowOutput) {
                                    if ($joinModel instanceof ObjectCollection) {
                                        $joinModels = [];
                                        foreach ($joinModel as $k => $v) {
                                            $joinModels[] = $v;
                                        }
                                    } else {
                                        $joinModels = [ $joinModel ];
                                    }
                                    $rowOutput[$column->getName()] = '';
                                    $getFunction = sprintf('get%s', $column->getColumnName());
                                    $results = [];
                                    foreach ($joinModels as $jm) {
                                        if (method_exists($jm, $getFunction)) {
                                            $results[] = $jm->$getFunction();
                                        }
                                    }
                                    $rowOutput[$column->getName()] = implode(($column instanceof GroupedJoinColumn) ? $column->getSeparator() : ', ', $results);
                                },
                            ]
                        );
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
                    $this->traverseQuery(
                        $column,
                        [
                            'preEachQueryUp' => function (&$query, $relation, $joinSettings) {
                                $query->join($relation->getName(), JoinColumn::getPropelJoinFromJoinType($joinSettings['JoinType']));
                            },
                            'afterAll' => function (&$query) {
                                $query->groupBy('Id');
                            }
                        ]
                    );
                }
            }
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
            $query = $this->query;
            $query->_or();
            foreach ($this->config->getColumns() as $columnConfig) {
                if ($columnConfig->getSearchable()) {
                    $query->_or();
                    if ($columnConfig instanceof JoinColumn) {
                        $query = $this->traverseQuery(
                            $columnConfig,
                            [
                                'beforeAll' => function (&$query) {
                                    $query->_or();
                                },
                                'preEachQueryUp' => function (&$query) {
                                    $query->_or();
                                },
                                'postEachQueryUp' => function (&$query) {
                                    $query->_or();
                                },
                                'preEachQueryDown' => function (&$query) {
                                    $query->_or();
                                },
                                'postEachQueryDown' => function (&$query) {
                                    $query->_or();
                                },
                                'topJoin' => function (&$query, &$join) use ($searches) {
                                    $query->_or()->filterBy($join->getColumnName(), sprintf('%%%s%%', $searches['value']), Criteria::LIKE)->_or();
                                },
                                'afterAll' => function (&$query) {
                                    $query->_or();
                                }
                            ]
                        );
                    } else {
                        if (!$this->isNeverSearchable($query, $columnConfig)) {
                            $column = sprintf('%s.%s', $query->getTableMap()->getPhpName(), $columnConfig->getColumnName());
                            $query->_or()->where(sprintf('%s LIKE ?', $column), sprintf('%%%s%%', $searches['value']))->_or();
                        }
                    }
                }
                $query->_or();
                $this->query = $query;
            }
        }
        $this->query->_or();
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

    private function itemIsCallable($array, $index)
    {
        return (isset($array[$index]) && is_callable($array[$index]));
    }

    public function traverseQuery(JoinColumn $join, $callbacks = [])
    {
        $query = $this->query;

        $joinSettings = $join->getJoinSettings();


        if ($this->itemIsCallable($callbacks, 'beforeAll')) {
            $callbacks['beforeAll']($query);
        }

        $count = 0;
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
                                try {
                                    if ($this->itemIsCallable($callbacks, 'preEachQueryUp')) {
                                        $callbacks['preEachQueryUp']($query, $relation, $joinSetting, $join);
                                    }
                                    $useFunction = sprintf('use%sQuery', $relation->getName());
                                    $query = $query->$useFunction(null, JoinColumn::getPropelJoinFromJoinType($joinSetting['JoinType']));
                                    if ($this->itemIsCallable($callbacks, 'postEachQueryUp')) {
                                        $callbacks['postEachQueryUp']($query, $relation, $joinSetting, $join);
                                    }
                                } catch(Exception $e) {
                                    break 2;
                                }
                                $queryName = $foreignTable->getPhpName();
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
                    $callbacks['preEachQueryUp']($query, $relation, $joinSetting, $join);
                }
                $query = $query->$useFunction(null, JoinColumn::getPropelJoinFromJoinType($joinSetting['JoinType']));
                $count++;
                if ($this->itemIsCallable($callbacks, 'postEachQueryUp')) {
                    $callbacks['postEachQueryUp']($query, $relation, $joinSetting, $join);
                }
            } catch (Exception $e) {
                break;
            }
        }

        if ($this->itemIsCallable($callbacks, 'topJoin')) {
            $callbacks['topJoin']($query, $join);
        }

        $joinsToReverse = array_slice($join->getJoinSettings(), 0, $count);

        foreach (array_reverse($joinsToReverse) as $joinSetting) {

            try {

                if ($relation->getType() == RelationMap::MANY_TO_MANY) {
                    if ($this->itemIsCallable($callbacks, 'preEachQueryDown')) {
                        $callbacks['preEachQueryDown']($query, $joinSetting, $join);
                    }
                    $query = $query->endUse();
                    if ($this->itemIsCallable($callbacks, 'postEachQueryDown')) {
                        $callbacks['postEachQueryDown']($query, $joinSetting, $join);
                    }
                }

                if ($this->itemIsCallable($callbacks, 'preEachQueryDown')) {
                    $callbacks['preEachQueryDown']($query, $joinSetting, $join);
                }
                $query = $query->endUse();
                if ($this->itemIsCallable($callbacks, 'postEachQueryDown')) {
                    $callbacks['postEachQueryDown']($query, $joinSetting, $join);
                }

            } catch (Exception $e) {
                break;
            }

        }

        if ($this->itemIsCallable($callbacks, 'afterAll')) {
            $callbacks['afterAll']($query);
        }

        return $query;
    }
}