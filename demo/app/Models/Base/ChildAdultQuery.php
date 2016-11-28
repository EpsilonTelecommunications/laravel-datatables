<?php

namespace App\Models\Base;

use \Exception;
use \PDO;
use App\Models\Adult as ChildAdult;
use App\Models\ChildAdult as ChildChildAdult;
use App\Models\ChildAdultQuery as ChildChildAdultQuery;
use App\Models\Map\ChildAdultTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'child_adult' table.
 *
 *
 *
 * @method     ChildChildAdultQuery orderByChildId($order = Criteria::ASC) Order by the child_id column
 * @method     ChildChildAdultQuery orderByParentId($order = Criteria::ASC) Order by the parent_id column
 *
 * @method     ChildChildAdultQuery groupByChildId() Group by the child_id column
 * @method     ChildChildAdultQuery groupByParentId() Group by the parent_id column
 *
 * @method     ChildChildAdultQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildChildAdultQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildChildAdultQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildChildAdultQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildChildAdultQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildChildAdultQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildChildAdultQuery leftJoinChild($relationAlias = null) Adds a LEFT JOIN clause to the query using the Child relation
 * @method     ChildChildAdultQuery rightJoinChild($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Child relation
 * @method     ChildChildAdultQuery innerJoinChild($relationAlias = null) Adds a INNER JOIN clause to the query using the Child relation
 *
 * @method     ChildChildAdultQuery joinWithChild($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Child relation
 *
 * @method     ChildChildAdultQuery leftJoinWithChild() Adds a LEFT JOIN clause and with to the query using the Child relation
 * @method     ChildChildAdultQuery rightJoinWithChild() Adds a RIGHT JOIN clause and with to the query using the Child relation
 * @method     ChildChildAdultQuery innerJoinWithChild() Adds a INNER JOIN clause and with to the query using the Child relation
 *
 * @method     ChildChildAdultQuery leftJoinAdult($relationAlias = null) Adds a LEFT JOIN clause to the query using the Adult relation
 * @method     ChildChildAdultQuery rightJoinAdult($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Adult relation
 * @method     ChildChildAdultQuery innerJoinAdult($relationAlias = null) Adds a INNER JOIN clause to the query using the Adult relation
 *
 * @method     ChildChildAdultQuery joinWithAdult($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Adult relation
 *
 * @method     ChildChildAdultQuery leftJoinWithAdult() Adds a LEFT JOIN clause and with to the query using the Adult relation
 * @method     ChildChildAdultQuery rightJoinWithAdult() Adds a RIGHT JOIN clause and with to the query using the Adult relation
 * @method     ChildChildAdultQuery innerJoinWithAdult() Adds a INNER JOIN clause and with to the query using the Adult relation
 *
 * @method     \App\Models\ChildQuery|\App\Models\AdultQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildChildAdult findOne(ConnectionInterface $con = null) Return the first ChildChildAdult matching the query
 * @method     ChildChildAdult findOneOrCreate(ConnectionInterface $con = null) Return the first ChildChildAdult matching the query, or a new ChildChildAdult object populated from the query conditions when no match is found
 *
 * @method     ChildChildAdult findOneByChildId(int $child_id) Return the first ChildChildAdult filtered by the child_id column
 * @method     ChildChildAdult findOneByParentId(int $parent_id) Return the first ChildChildAdult filtered by the parent_id column *

 * @method     ChildChildAdult requirePk($key, ConnectionInterface $con = null) Return the ChildChildAdult by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildChildAdult requireOne(ConnectionInterface $con = null) Return the first ChildChildAdult matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildChildAdult requireOneByChildId(int $child_id) Return the first ChildChildAdult filtered by the child_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildChildAdult requireOneByParentId(int $parent_id) Return the first ChildChildAdult filtered by the parent_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildChildAdult[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildChildAdult objects based on current ModelCriteria
 * @method     ChildChildAdult[]|ObjectCollection findByChildId(int $child_id) Return ChildChildAdult objects filtered by the child_id column
 * @method     ChildChildAdult[]|ObjectCollection findByParentId(int $parent_id) Return ChildChildAdult objects filtered by the parent_id column
 * @method     ChildChildAdult[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class ChildAdultQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \App\Models\Base\ChildAdultQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'mysql', $modelName = '\\App\\Models\\ChildAdult', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildChildAdultQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildChildAdultQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildChildAdultQuery) {
            return $criteria;
        }
        $query = new ChildChildAdultQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj = $c->findPk(array(12, 34), $con);
     * </code>
     *
     * @param array[$child_id, $parent_id] $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildChildAdult|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ChildAdultTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = ChildAdultTableMap::getInstanceFromPool(serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]))))) {
            // the object is already in the instance pool
            return $obj;
        }

        return $this->findPkSimple($key, $con);
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildChildAdult A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT child_id, parent_id FROM child_adult WHERE child_id = :p0 AND parent_id = :p1';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key[0], PDO::PARAM_INT);
            $stmt->bindValue(':p1', $key[1], PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildChildAdult $obj */
            $obj = new ChildChildAdult();
            $obj->hydrate($row);
            ChildAdultTableMap::addInstanceToPool($obj, serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]));
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildChildAdult|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, ConnectionInterface $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(array(12, 56), array(832, 123), array(123, 456)), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return $this|ChildChildAdultQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        $this->addUsingAlias(ChildAdultTableMap::COL_CHILD_ID, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(ChildAdultTableMap::COL_PARENT_ID, $key[1], Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildChildAdultQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        if (empty($keys)) {
            return $this->add(null, '1<>1', Criteria::CUSTOM);
        }
        foreach ($keys as $key) {
            $cton0 = $this->getNewCriterion(ChildAdultTableMap::COL_CHILD_ID, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(ChildAdultTableMap::COL_PARENT_ID, $key[1], Criteria::EQUAL);
            $cton0->addAnd($cton1);
            $this->addOr($cton0);
        }

        return $this;
    }

    /**
     * Filter the query on the child_id column
     *
     * Example usage:
     * <code>
     * $query->filterByChildId(1234); // WHERE child_id = 1234
     * $query->filterByChildId(array(12, 34)); // WHERE child_id IN (12, 34)
     * $query->filterByChildId(array('min' => 12)); // WHERE child_id > 12
     * </code>
     *
     * @see       filterByChild()
     *
     * @param     mixed $childId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildChildAdultQuery The current query, for fluid interface
     */
    public function filterByChildId($childId = null, $comparison = null)
    {
        if (is_array($childId)) {
            $useMinMax = false;
            if (isset($childId['min'])) {
                $this->addUsingAlias(ChildAdultTableMap::COL_CHILD_ID, $childId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($childId['max'])) {
                $this->addUsingAlias(ChildAdultTableMap::COL_CHILD_ID, $childId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ChildAdultTableMap::COL_CHILD_ID, $childId, $comparison);
    }

    /**
     * Filter the query on the parent_id column
     *
     * Example usage:
     * <code>
     * $query->filterByParentId(1234); // WHERE parent_id = 1234
     * $query->filterByParentId(array(12, 34)); // WHERE parent_id IN (12, 34)
     * $query->filterByParentId(array('min' => 12)); // WHERE parent_id > 12
     * </code>
     *
     * @see       filterByAdult()
     *
     * @param     mixed $parentId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildChildAdultQuery The current query, for fluid interface
     */
    public function filterByParentId($parentId = null, $comparison = null)
    {
        if (is_array($parentId)) {
            $useMinMax = false;
            if (isset($parentId['min'])) {
                $this->addUsingAlias(ChildAdultTableMap::COL_PARENT_ID, $parentId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($parentId['max'])) {
                $this->addUsingAlias(ChildAdultTableMap::COL_PARENT_ID, $parentId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ChildAdultTableMap::COL_PARENT_ID, $parentId, $comparison);
    }

    /**
     * Filter the query by a related \App\Models\Child object
     *
     * @param \App\Models\Child|ObjectCollection $child The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildChildAdultQuery The current query, for fluid interface
     */
    public function filterByChild($child, $comparison = null)
    {
        if ($child instanceof \App\Models\Child) {
            return $this
                ->addUsingAlias(ChildAdultTableMap::COL_CHILD_ID, $child->getId(), $comparison);
        } elseif ($child instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ChildAdultTableMap::COL_CHILD_ID, $child->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByChild() only accepts arguments of type \App\Models\Child or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Child relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildChildAdultQuery The current query, for fluid interface
     */
    public function joinChild($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Child');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'Child');
        }

        return $this;
    }

    /**
     * Use the Child relation Child object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \App\Models\ChildQuery A secondary query class using the current class as primary query
     */
    public function useChildQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinChild($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Child', '\App\Models\ChildQuery');
    }

    /**
     * Filter the query by a related \App\Models\Adult object
     *
     * @param \App\Models\Adult|ObjectCollection $adult The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildChildAdultQuery The current query, for fluid interface
     */
    public function filterByAdult($adult, $comparison = null)
    {
        if ($adult instanceof \App\Models\Adult) {
            return $this
                ->addUsingAlias(ChildAdultTableMap::COL_PARENT_ID, $adult->getId(), $comparison);
        } elseif ($adult instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ChildAdultTableMap::COL_PARENT_ID, $adult->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByAdult() only accepts arguments of type \App\Models\Adult or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Adult relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildChildAdultQuery The current query, for fluid interface
     */
    public function joinAdult($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Adult');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'Adult');
        }

        return $this;
    }

    /**
     * Use the Adult relation Adult object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \App\Models\AdultQuery A secondary query class using the current class as primary query
     */
    public function useAdultQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinAdult($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Adult', '\App\Models\AdultQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildChildAdult $childAdult Object to remove from the list of results
     *
     * @return $this|ChildChildAdultQuery The current query, for fluid interface
     */
    public function prune($childAdult = null)
    {
        if ($childAdult) {
            $this->addCond('pruneCond0', $this->getAliasedColName(ChildAdultTableMap::COL_CHILD_ID), $childAdult->getChildId(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(ChildAdultTableMap::COL_PARENT_ID), $childAdult->getParentId(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

    /**
     * Deletes all rows from the child_adult table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ChildAdultTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ChildAdultTableMap::clearInstancePool();
            ChildAdultTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    /**
     * Performs a DELETE on the database based on the current ModelCriteria
     *
     * @param ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public function delete(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ChildAdultTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ChildAdultTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            ChildAdultTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ChildAdultTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // ChildAdultQuery
