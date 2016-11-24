<?php

namespace App\Models\Base;

use \Exception;
use \PDO;
use App\Models\Gender as ChildGender;
use App\Models\GenderQuery as ChildGenderQuery;
use App\Models\Map\GenderTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'gender' table.
 *
 *
 *
 * @method     ChildGenderQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildGenderQuery orderByRaceId($order = Criteria::ASC) Order by the race_id column
 * @method     ChildGenderQuery orderByName($order = Criteria::ASC) Order by the name column
 *
 * @method     ChildGenderQuery groupById() Group by the id column
 * @method     ChildGenderQuery groupByRaceId() Group by the race_id column
 * @method     ChildGenderQuery groupByName() Group by the name column
 *
 * @method     ChildGenderQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildGenderQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildGenderQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildGenderQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildGenderQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildGenderQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildGenderQuery leftJoinRace($relationAlias = null) Adds a LEFT JOIN clause to the query using the Race relation
 * @method     ChildGenderQuery rightJoinRace($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Race relation
 * @method     ChildGenderQuery innerJoinRace($relationAlias = null) Adds a INNER JOIN clause to the query using the Race relation
 *
 * @method     ChildGenderQuery joinWithRace($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Race relation
 *
 * @method     ChildGenderQuery leftJoinWithRace() Adds a LEFT JOIN clause and with to the query using the Race relation
 * @method     ChildGenderQuery rightJoinWithRace() Adds a RIGHT JOIN clause and with to the query using the Race relation
 * @method     ChildGenderQuery innerJoinWithRace() Adds a INNER JOIN clause and with to the query using the Race relation
 *
 * @method     ChildGenderQuery leftJoinAdult($relationAlias = null) Adds a LEFT JOIN clause to the query using the Adult relation
 * @method     ChildGenderQuery rightJoinAdult($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Adult relation
 * @method     ChildGenderQuery innerJoinAdult($relationAlias = null) Adds a INNER JOIN clause to the query using the Adult relation
 *
 * @method     ChildGenderQuery joinWithAdult($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Adult relation
 *
 * @method     ChildGenderQuery leftJoinWithAdult() Adds a LEFT JOIN clause and with to the query using the Adult relation
 * @method     ChildGenderQuery rightJoinWithAdult() Adds a RIGHT JOIN clause and with to the query using the Adult relation
 * @method     ChildGenderQuery innerJoinWithAdult() Adds a INNER JOIN clause and with to the query using the Adult relation
 *
 * @method     ChildGenderQuery leftJoinChild($relationAlias = null) Adds a LEFT JOIN clause to the query using the Child relation
 * @method     ChildGenderQuery rightJoinChild($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Child relation
 * @method     ChildGenderQuery innerJoinChild($relationAlias = null) Adds a INNER JOIN clause to the query using the Child relation
 *
 * @method     ChildGenderQuery joinWithChild($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Child relation
 *
 * @method     ChildGenderQuery leftJoinWithChild() Adds a LEFT JOIN clause and with to the query using the Child relation
 * @method     ChildGenderQuery rightJoinWithChild() Adds a RIGHT JOIN clause and with to the query using the Child relation
 * @method     ChildGenderQuery innerJoinWithChild() Adds a INNER JOIN clause and with to the query using the Child relation
 *
 * @method     \App\Models\RaceQuery|\App\Models\AdultQuery|\App\Models\ChildQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildGender findOne(ConnectionInterface $con = null) Return the first ChildGender matching the query
 * @method     ChildGender findOneOrCreate(ConnectionInterface $con = null) Return the first ChildGender matching the query, or a new ChildGender object populated from the query conditions when no match is found
 *
 * @method     ChildGender findOneById(int $id) Return the first ChildGender filtered by the id column
 * @method     ChildGender findOneByRaceId(int $race_id) Return the first ChildGender filtered by the race_id column
 * @method     ChildGender findOneByName(string $name) Return the first ChildGender filtered by the name column *

 * @method     ChildGender requirePk($key, ConnectionInterface $con = null) Return the ChildGender by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGender requireOne(ConnectionInterface $con = null) Return the first ChildGender matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildGender requireOneById(int $id) Return the first ChildGender filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGender requireOneByRaceId(int $race_id) Return the first ChildGender filtered by the race_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGender requireOneByName(string $name) Return the first ChildGender filtered by the name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildGender[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildGender objects based on current ModelCriteria
 * @method     ChildGender[]|ObjectCollection findById(int $id) Return ChildGender objects filtered by the id column
 * @method     ChildGender[]|ObjectCollection findByRaceId(int $race_id) Return ChildGender objects filtered by the race_id column
 * @method     ChildGender[]|ObjectCollection findByName(string $name) Return ChildGender objects filtered by the name column
 * @method     ChildGender[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class GenderQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \App\Models\Base\GenderQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'mysql', $modelName = '\\App\\Models\\Gender', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildGenderQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildGenderQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildGenderQuery) {
            return $criteria;
        }
        $query = new ChildGenderQuery();
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
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildGender|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(GenderTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = GenderTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildGender A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, race_id, name FROM gender WHERE id = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildGender $obj */
            $obj = new ChildGender();
            $obj->hydrate($row);
            GenderTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildGender|array|mixed the result, formatted by the current formatter
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
     * $objs = $c->findPks(array(12, 56, 832), $con);
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
     * @return $this|ChildGenderQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(GenderTableMap::COL_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildGenderQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(GenderTableMap::COL_ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id > 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildGenderQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(GenderTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(GenderTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GenderTableMap::COL_ID, $id, $comparison);
    }

    /**
     * Filter the query on the race_id column
     *
     * Example usage:
     * <code>
     * $query->filterByRaceId(1234); // WHERE race_id = 1234
     * $query->filterByRaceId(array(12, 34)); // WHERE race_id IN (12, 34)
     * $query->filterByRaceId(array('min' => 12)); // WHERE race_id > 12
     * </code>
     *
     * @see       filterByRace()
     *
     * @param     mixed $raceId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildGenderQuery The current query, for fluid interface
     */
    public function filterByRaceId($raceId = null, $comparison = null)
    {
        if (is_array($raceId)) {
            $useMinMax = false;
            if (isset($raceId['min'])) {
                $this->addUsingAlias(GenderTableMap::COL_RACE_ID, $raceId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($raceId['max'])) {
                $this->addUsingAlias(GenderTableMap::COL_RACE_ID, $raceId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GenderTableMap::COL_RACE_ID, $raceId, $comparison);
    }

    /**
     * Filter the query on the name column
     *
     * Example usage:
     * <code>
     * $query->filterByName('fooValue');   // WHERE name = 'fooValue'
     * $query->filterByName('%fooValue%', Criteria::LIKE); // WHERE name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $name The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildGenderQuery The current query, for fluid interface
     */
    public function filterByName($name = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($name)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GenderTableMap::COL_NAME, $name, $comparison);
    }

    /**
     * Filter the query by a related \App\Models\Race object
     *
     * @param \App\Models\Race|ObjectCollection $race The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildGenderQuery The current query, for fluid interface
     */
    public function filterByRace($race, $comparison = null)
    {
        if ($race instanceof \App\Models\Race) {
            return $this
                ->addUsingAlias(GenderTableMap::COL_RACE_ID, $race->getId(), $comparison);
        } elseif ($race instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(GenderTableMap::COL_RACE_ID, $race->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByRace() only accepts arguments of type \App\Models\Race or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Race relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildGenderQuery The current query, for fluid interface
     */
    public function joinRace($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Race');

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
            $this->addJoinObject($join, 'Race');
        }

        return $this;
    }

    /**
     * Use the Race relation Race object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \App\Models\RaceQuery A secondary query class using the current class as primary query
     */
    public function useRaceQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinRace($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Race', '\App\Models\RaceQuery');
    }

    /**
     * Filter the query by a related \App\Models\Adult object
     *
     * @param \App\Models\Adult|ObjectCollection $adult the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGenderQuery The current query, for fluid interface
     */
    public function filterByAdult($adult, $comparison = null)
    {
        if ($adult instanceof \App\Models\Adult) {
            return $this
                ->addUsingAlias(GenderTableMap::COL_ID, $adult->getGenderId(), $comparison);
        } elseif ($adult instanceof ObjectCollection) {
            return $this
                ->useAdultQuery()
                ->filterByPrimaryKeys($adult->getPrimaryKeys())
                ->endUse();
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
     * @return $this|ChildGenderQuery The current query, for fluid interface
     */
    public function joinAdult($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
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
    public function useAdultQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAdult($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Adult', '\App\Models\AdultQuery');
    }

    /**
     * Filter the query by a related \App\Models\Child object
     *
     * @param \App\Models\Child|ObjectCollection $child the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGenderQuery The current query, for fluid interface
     */
    public function filterByChild($child, $comparison = null)
    {
        if ($child instanceof \App\Models\Child) {
            return $this
                ->addUsingAlias(GenderTableMap::COL_ID, $child->getGenderId(), $comparison);
        } elseif ($child instanceof ObjectCollection) {
            return $this
                ->useChildQuery()
                ->filterByPrimaryKeys($child->getPrimaryKeys())
                ->endUse();
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
     * @return $this|ChildGenderQuery The current query, for fluid interface
     */
    public function joinChild($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
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
    public function useChildQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinChild($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Child', '\App\Models\ChildQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildGender $gender Object to remove from the list of results
     *
     * @return $this|ChildGenderQuery The current query, for fluid interface
     */
    public function prune($gender = null)
    {
        if ($gender) {
            $this->addUsingAlias(GenderTableMap::COL_ID, $gender->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the gender table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(GenderTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            GenderTableMap::clearInstancePool();
            GenderTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(GenderTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(GenderTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            GenderTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            GenderTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // GenderQuery
