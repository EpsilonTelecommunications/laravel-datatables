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
use Log;
use Config;

class PropelDataTablesDriver
{
    private $query;
    private $originalQuery;
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
        $this->originalQuery = clone $config->getQuery();
    }

    public function resetQuery()
    {
        $this->query = clone $this->originalQuery;
    }

    public function makeResponse()
    {
        $results = $this->runQuery();
        $this->resetQuery();

        $className = false;

        $output = [];

        foreach ($results['data'] as $row) {
            $rowOutput = [];

            if (!$className) {
                $className = $this->getClassName($row);
            }
            $rowOutput[$className . 'Object'] = $row;
            foreach ($this->config->getColumns() as $column) {

                try {

                    if ($column instanceof JoinColumn) {
                        $joinModel = $row;

                        $this->traverseQuery(
                            $column,
                            [
                                'postEachQueryUp' => function($query, $relation) use ($column, &$joinModel, &$rowOutput) {

                                    if ($relation->getType() == RelationMap::MANY_TO_MANY || $relation->getType() == RelationMap::ONE_TO_MANY) {
                                        $getterName = $relation->getPluralName();
                                    } else {
                                        $getterName = $relation->getName();
                                    }

                                    $getFunction = sprintf('get%s', $getterName);
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
                        if ($getFunction == 'getDateOfBirth') {
                        }
                        if (method_exists($row, $getFunction)) {
                            $rowOutput[$column->getName()] = $row->$getFunction();
                        } else {
                            $rowOutput[$column->getName()] = '';
                        }
                    }

                } catch (Exception $e) {
                    $this->handleException($e);
                    $rowOutput[$column->getName()] = '';
                } finally {
                    if (!isset($rowOutput[$column->getName()])) {
                        $rowOutput[$column->getName()] = '';
                    }
                    $this->resetQuery();
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

    private function getClassName($class)
    {
        $className = explode('\\', get_class($class));
        return end($className);
    }

    private function runQuery()
    {
        $this->doJoins();
        $this->query->groupBy('Id');
        $recordsTotal = $this->query->select('Id')->count();
        $this->resetQuery();

        $this->doFilter();
        $this->query->groupBy('Id');
        $recordsFiltered = $this->query->count();
        $this->resetQuery();

        $this->doFilter();
        $this->doLimit();
        $this->query->groupBy('Id');

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
                            'afterAll' => function (&$query) {
                                $query->groupBy('Id');
                            }
                        ]
                    );
                }
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    public function doFilter()
    {
        $searches = $this->request->get('search', []);
        $orders = $this->request->get('order', ['column' => 0, 'dir' => 'asc']);

        $query = $this->query;
		//$query->filterById(0, Criteria::GREATER_THAN); // Hack to ensure all the following is OR'd as the query might have other filters applied. DID YOU EVEN TEST THIS!?!?!?!
        foreach ($this->config->getColumns() as $columnConfig) {
            if ($columnConfig->getSearchable()) {
               // $query->_or();
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
                            'topJoin' => function (&$query, &$join, $relation) use ($searches, $orders) {
                                if (isset($searches['value']) && strlen($searches['value'])) {
                                    $query->filterBy($join->getColumnName(), sprintf('%%%s%%', $searches['value']), Criteria::LIKE)->_or();
                                }
                                if (!in_array($relation->getType(), [ RelationMap::MANY_TO_MANY, RelationMap::ONE_TO_MANY ])) {
                                    foreach ($orders as $order) {
                                        if (isset($order['column']) && $this->config->getIndexForColumn($join) == $order['column']) {
                                            $query->orderBy($join->getColumnName(), $order['dir']);
                                        }
                                    }
                                }
                            },
                            'afterAll' => function (&$query) {
                                $query->_or();
                            }
                        ]
                    );
                } else {
                    $column = sprintf('%s.%s', $query->getTableMap()->getPhpName(), $columnConfig->getColumnName());
                    if (!$this->isNeverSearchable($query, $columnConfig)) {
                        $query->where(sprintf('%s LIKE ?', $column), sprintf('%%%s%%', $searches['value']))->_or();
                    }
                    foreach ($orders as $order) {
                        if (isset($order['column']) && $this->config->getIndexForColumn($columnConfig) == $order['column']) {
                            $query->orderBy($column, $order['dir']);
                        }
                    }
                }
            }
            $query->_or();
            $this->query = $query;
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
        $index = $this->config->getIndexForColumn($join);
        $query = $this->query;

        $joinSettings = $join->getJoinSettings();


        if ($this->itemIsCallable($callbacks, 'beforeAll')) {
            $callbacks['beforeAll']($query);
        }

        $count = 0;
        foreach ($joinSettings as $key => $joinSetting) {
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
                                    $query = $query->$useFunction($relation->getName() . $index . '_' . $key, JoinColumn::getPropelJoinFromJoinType($joinSetting['JoinType']));
                                    if ($this->itemIsCallable($callbacks, 'postEachQueryUp')) {
                                        $callbacks['postEachQueryUp']($query, $relation, $joinSetting, $join);
                                    }
                                } catch(Exception $e) {
                                    $this->handleException($e);
                                }
                                $queryName = $foreignTable->getPhpName();
                            }
                        }
                    }
                } else {
                    $queryName = $relation->getName();
                }

                try {
                    if ($this->itemIsCallable($callbacks, 'preEachQueryUp')) {
                        $callbacks['preEachQueryUp']($query, $relation, $joinSetting, $join);
                    }
                    $useFunction = sprintf('use%sQuery', $queryName);
                    $query = $query->$useFunction($queryName  .$index . '_' . $key, JoinColumn::getPropelJoinFromJoinType($joinSetting['JoinType']));
                    $count++;
                    if ($this->itemIsCallable($callbacks, 'postEachQueryUp')) {
                        $callbacks['postEachQueryUp']($query, $relation, $joinSetting, $join);
                    }
                } catch (Exception $e) {
                    $this->handleException($e);
                    break;
                }
            }
        }

        try {
            $joinsToReverse = $join->getJoinSettings();
            if (count($joinsToReverse) == $count) {
                if ($this->itemIsCallable($callbacks, 'topJoin')) {
                    $callbacks['topJoin']($query, $join, $relation);
                }
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }

        $joinsToReverse = array_slice($joinsToReverse, 0, $count);
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
                $this->handleException($e);
                break;
            }
        }

        if ($this->itemIsCallable($callbacks, 'afterAll')) {
            $callbacks['afterAll']($query);
        }

        return $query;
    }

    private function handleException(Exception $e)
    {
        if (Config::get('app.debug')) {
            throw $e;
        } else {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}