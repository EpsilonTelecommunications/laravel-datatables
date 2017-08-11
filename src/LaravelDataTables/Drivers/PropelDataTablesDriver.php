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
    private $classNamesCache;
    private $methodExistsCache;

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
        $originalColumnType = $this->config->getDefaultColumnType();
        $this->config->setDefaultColumnType('');
        $results = $this->runQuery($originalColumnType);
        $this->resetQuery();
        $this->config->setDefaultColumnType($originalColumnType);

        $className = false;

        $output = [];

        $dataTableColumns = $this->config->getColumns();

        $totalTimes = [
            'traverse' => 0,
            'flatCol' => 0,
            'postEachQueryUp' => 0,
            'postEachQueryUpCount' => 0,
            'topJoin' => 0,
            'topJoinCount' => 0,
        ];

        $getterChain = [];
        foreach ($results['data'] as $row) {
            $rowOutput = [];

            if (!$className) {
                $className = $this->getClassName($row);
            }

            $rowOutput[$className . 'Object'] = $row;
            foreach ($dataTableColumns as $column) {
                try {
                    if ($column instanceof JoinColumn) {
                        $joinModel = $row;

                        if (!isset($getterChain[$column->getName()])) {
                            $getterChain[$column->getName()] = [];
                            $this->traverseQuery(
                                $column,
                                [
                                    'postEachQueryUp' => function($query, $relation) use ($column, &$joinModel, &$rowOutput, &$getterChain) {
                                        if ($relation->getType() == RelationMap::MANY_TO_MANY || $relation->getType() == RelationMap::ONE_TO_MANY) {
                                            $getterName = $relation->getPluralName();
                                        } else {
                                            $getterName = $relation->getName();
                                        }

                                        $getFunction = sprintf('get%s', $getterName);
                                        if (method_exists($joinModel, $getFunction)) {
                                            $joinModel = $joinModel->$getFunction();
                                            $getterChain[$column->getName()][] = $getFunction;
                                        }
                                    },
                                    'topJoin' => function() use ($column, &$joinModel, &$rowOutput, &$totalTimes, &$getterChain) {
                                        $getFunction = sprintf('get%s', $column->getColumnName());
                                        $getterChain[$column->getName()][] = $getFunction;
                                    },
                                ]
                            );
                        }

                        foreach ($getterChain[$column->getName()] as $getter) {
                            if (is_string($joinModel)) {
                                break;
                            } elseif (is_array($joinModel)) {
                                foreach ($joinModel as $jm) {
                                    if (method_exists($jm, $getter)) {
                                        $value = $jm->$getter();
                                        if (is_string($value) || is_numeric($value)) {
                                            $results[] = $value;
                                        }
                                    }
                                }
                                $joinModel = implode(', ', $results);
                            } elseif (method_exists($joinModel, $getter)) {
                                $joinModel = $joinModel->$getter();
                            } else {
                                $joinModel = '';
                            }
                        }
                        $rowOutput[$column->getName()] = $joinModel;

                    } else {
                        $eStart = microtime(true);
                        $getFunction = sprintf('get%s', $column->getColumnName());
                        if (method_exists($row, $getFunction)) {
                            $rowOutput[$column->getName()] = $row->$getFunction();
                        } else {
                            $rowOutput[$column->getName()] = '';
                        }
                        $totalTimes['flatCol'] = $totalTimes['flatCol'] + (microtime(true) - $eStart);
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

        $response = [ 'data' => $output ];

        if (isset($results['recordsFiltered'])) {
            $response['recordsFiltered'] = $results['recordsFiltered'];
        }

        if (isset($results['recordsTotal'])) {
            $response['recordsTotal'] = $results['recordsTotal'];
        }

        return $response;
    }

    private function methodExists($class, $methodName)
    {
        $className = $this->getClassName($class);
        $key = sprintf('%s::%s', $className, $methodName);
        if (!isset($this->methodExistsCache[$key])) {
            $this->methodExistsCache[$key] = method_exists($class, $methodName);
        }

        return $this->methodExistsCache[$key];
    }

    private function getClassName($class)
    {
        $class = get_class($class);
        if (!isset($this->classNamesCache[$class])) {
            $className = explode('\\', $class);
            $this->classNamesCache[$class] = end($className);
        }
        return $this->classNamesCache[$class];
    }

    private function runQuery($columnType)
    {
        $response = [];
        if ($columnType != 'csv') {
            $this->doJoins();
            $this->query->groupBy('Id');
            $response['recordsTotal'] = $this->query->select('Id')->count();
            $this->resetQuery();

            $this->doFilter();
            $this->query->groupBy('Id');
            $response['recordsFiltered'] = $this->query->count();
            $this->resetQuery();
        }

        $this->doFilter();
        $this->doLimit();
        $this->query->groupBy('Id');

        $response['data'] = $this->query->find();

        return $response;
    }

    private function doJoins()
    {
        try {
            foreach ($this->config->getColumns() as $column) {
                if ($column instanceof JoinColumn) {
                    $this->traverseQuery(
                        $column,
                        [
                            'afterAll' => function (&$query, $level) {
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
        $isFirstFilterBy = true;
        foreach ($this->config->getColumns() as $key => $columnConfig) {
            if ($columnConfig->getSearchable()) {
                if ($columnConfig instanceof JoinColumn) {
                    $query = $this->traverseQuery(
                        $columnConfig,
                        [
//                            'beforeAll' => function (&$query, $level) use ($c) {
//                                if ($c == 0) {
//	                                $query->_and();
//	                            }
//                            },
//                            'preEachQueryUp' => function (&$query, $relation, $joinSetting, $join, $level) use ($c)  {
//                                ($c == 0 && $level == 0) ? $query->_and() : $query->_or();
//                            },
//                            'postEachQueryUp' => function (&$query) {
//                                $query->_or();
//                            },
//                            'preEachQueryDown' => function (&$query) {
//                                $query->_or();
//                            },
//                            'postEachQueryDown' => function (&$query) {
//                                $query->_or();
//                            },
                            'topJoin' => function (&$query, &$join, $relation) use ($searches, $orders, &$isFirstFilterBy) {
                                if (isset($searches['value']) && strlen($searches['value'])) {
                                    if ($isFirstFilterBy) {
                                        $query->_and();
                                        $isFirstFilterBy = false;
                                    } else {
                                        $query->_or();
                                    }
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
//                            'afterAll' => function (&$query) {
//                                $query->_or();
//                            }
                        ]
                    );
                } else {
                    $column = sprintf('%s.%s', $query->getTableMap()->getPhpName(), $columnConfig->getColumnName());
                    if (!$this->isNeverSearchable($query, $columnConfig)) {
                        if ($isFirstFilterBy) {
                            $query->_and();
                            $isFirstFilterBy = false;
                        } else {
                            $query->_or();
                        }
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
            $callbacks['beforeAll']($query, 0);
        }

        $count = 0;
        $level = 0;
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
                                        $callbacks['preEachQueryUp']($query, $relation, $joinSetting, $join, $level);
                                    }
                                    $useFunction = sprintf('use%sQuery', $relation->getName());
                                    $query = $query->$useFunction($relation->getName() . $index . '_' . $key, JoinColumn::getPropelJoinFromJoinType($joinSetting['JoinType']));
                                    if ($this->itemIsCallable($callbacks, 'postEachQueryUp')) {
                                        $callbacks['postEachQueryUp']($query, $relation, $joinSetting, $join, $level);
                                    }
                                } catch(Exception $e) {
                                    $this->handleException($e);
                                }
                                $queryName = $foreignTable->getPhpName();
                            }
                        }
                    }
                    $level++;
                } else {
                    $queryName = $relation->getName();
                }

                try {
                    if ($this->itemIsCallable($callbacks, 'preEachQueryUp')) {
                        $callbacks['preEachQueryUp']($query, $relation, $joinSetting, $join, $level);
                    }
                    $useFunction = sprintf('use%sQuery', $queryName);
                    $query = $query->$useFunction($queryName  .$index . '_' . $key, JoinColumn::getPropelJoinFromJoinType($joinSetting['JoinType']));
                    $count++;
                    if ($this->itemIsCallable($callbacks, 'postEachQueryUp')) {
                        $callbacks['postEachQueryUp']($query, $relation, $joinSetting, $join, $level);
                    }
                } catch (Exception $e) {
                    $this->handleException($e);
                    break;
                }
            }
            $level++;
        }

        try {
            $joinsToReverse = $join->getJoinSettings();
            if (count($joinsToReverse) == $count) {
                if ($this->itemIsCallable($callbacks, 'topJoin')) {
                    $callbacks['topJoin']($query, $join, $relation, $level);
                }
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }

        $joinsToReverse = array_slice($joinsToReverse, 0, $count);
        foreach (array_reverse($joinsToReverse) as $joinSetting) {
            $level--;
            try {
                if ($relation->getType() == RelationMap::MANY_TO_MANY) {
                    $level--;
                    if ($this->itemIsCallable($callbacks, 'preEachQueryDown')) {
                        $callbacks['preEachQueryDown']($query, $joinSetting, $join, $level);
                    }
                    $query = $query->endUse();
                    if ($this->itemIsCallable($callbacks, 'postEachQueryDown')) {
                        $callbacks['postEachQueryDown']($query, $joinSetting, $join, $level);
                    }
                }

                if ($this->itemIsCallable($callbacks, 'preEachQueryDown')) {
                    $callbacks['preEachQueryDown']($query, $joinSetting, $join, $level);
                }
                $query = $query->endUse();
                if ($this->itemIsCallable($callbacks, 'postEachQueryDown')) {
                    $callbacks['postEachQueryDown']($query, $joinSetting, $join, $level);
                }
            } catch (Exception $e) {
                $this->handleException($e);
                break;
            }
        }

        if ($this->itemIsCallable($callbacks, 'afterAll')) {
            $callbacks['afterAll']($query, 0);
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
