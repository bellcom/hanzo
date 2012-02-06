<?php

namespace Hanzo\Model\om;

use \Criteria;
use \ModelCriteria;
use \ModelJoin;
use \PDO;
use \Propel;
use \PropelCollection;
use \PropelException;
use \PropelPDO;
use Hanzo\Model\Addresses;
use Hanzo\Model\Countries;
use Hanzo\Model\CountriesPeer;
use Hanzo\Model\CountriesQuery;
use Hanzo\Model\Orders;
use Hanzo\Model\ZipToCity;

/**
 * Base class that represents a query for the 'countries' table.
 *
 * 
 *
 * @method     CountriesQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     CountriesQuery orderByName($order = Criteria::ASC) Order by the name column
 * @method     CountriesQuery orderByLocalName($order = Criteria::ASC) Order by the local_name column
 * @method     CountriesQuery orderByCode($order = Criteria::ASC) Order by the code column
 * @method     CountriesQuery orderByIso2($order = Criteria::ASC) Order by the iso2 column
 * @method     CountriesQuery orderByIso3($order = Criteria::ASC) Order by the iso3 column
 * @method     CountriesQuery orderByContinent($order = Criteria::ASC) Order by the continent column
 * @method     CountriesQuery orderByCurrencyId($order = Criteria::ASC) Order by the currency_id column
 * @method     CountriesQuery orderByCurrencyCode($order = Criteria::ASC) Order by the currency_code column
 * @method     CountriesQuery orderByCurrencyName($order = Criteria::ASC) Order by the currency_name column
 *
 * @method     CountriesQuery groupById() Group by the id column
 * @method     CountriesQuery groupByName() Group by the name column
 * @method     CountriesQuery groupByLocalName() Group by the local_name column
 * @method     CountriesQuery groupByCode() Group by the code column
 * @method     CountriesQuery groupByIso2() Group by the iso2 column
 * @method     CountriesQuery groupByIso3() Group by the iso3 column
 * @method     CountriesQuery groupByContinent() Group by the continent column
 * @method     CountriesQuery groupByCurrencyId() Group by the currency_id column
 * @method     CountriesQuery groupByCurrencyCode() Group by the currency_code column
 * @method     CountriesQuery groupByCurrencyName() Group by the currency_name column
 *
 * @method     CountriesQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     CountriesQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     CountriesQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     CountriesQuery leftJoinAddresses($relationAlias = null) Adds a LEFT JOIN clause to the query using the Addresses relation
 * @method     CountriesQuery rightJoinAddresses($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Addresses relation
 * @method     CountriesQuery innerJoinAddresses($relationAlias = null) Adds a INNER JOIN clause to the query using the Addresses relation
 *
 * @method     CountriesQuery leftJoinZipToCity($relationAlias = null) Adds a LEFT JOIN clause to the query using the ZipToCity relation
 * @method     CountriesQuery rightJoinZipToCity($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ZipToCity relation
 * @method     CountriesQuery innerJoinZipToCity($relationAlias = null) Adds a INNER JOIN clause to the query using the ZipToCity relation
 *
 * @method     CountriesQuery leftJoinOrdersRelatedByBillingCountriesId($relationAlias = null) Adds a LEFT JOIN clause to the query using the OrdersRelatedByBillingCountriesId relation
 * @method     CountriesQuery rightJoinOrdersRelatedByBillingCountriesId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the OrdersRelatedByBillingCountriesId relation
 * @method     CountriesQuery innerJoinOrdersRelatedByBillingCountriesId($relationAlias = null) Adds a INNER JOIN clause to the query using the OrdersRelatedByBillingCountriesId relation
 *
 * @method     CountriesQuery leftJoinOrdersRelatedByDeliveryCountriesId($relationAlias = null) Adds a LEFT JOIN clause to the query using the OrdersRelatedByDeliveryCountriesId relation
 * @method     CountriesQuery rightJoinOrdersRelatedByDeliveryCountriesId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the OrdersRelatedByDeliveryCountriesId relation
 * @method     CountriesQuery innerJoinOrdersRelatedByDeliveryCountriesId($relationAlias = null) Adds a INNER JOIN clause to the query using the OrdersRelatedByDeliveryCountriesId relation
 *
 * @method     Countries findOne(PropelPDO $con = null) Return the first Countries matching the query
 * @method     Countries findOneOrCreate(PropelPDO $con = null) Return the first Countries matching the query, or a new Countries object populated from the query conditions when no match is found
 *
 * @method     Countries findOneById(int $id) Return the first Countries filtered by the id column
 * @method     Countries findOneByName(string $name) Return the first Countries filtered by the name column
 * @method     Countries findOneByLocalName(string $local_name) Return the first Countries filtered by the local_name column
 * @method     Countries findOneByCode(int $code) Return the first Countries filtered by the code column
 * @method     Countries findOneByIso2(string $iso2) Return the first Countries filtered by the iso2 column
 * @method     Countries findOneByIso3(string $iso3) Return the first Countries filtered by the iso3 column
 * @method     Countries findOneByContinent(string $continent) Return the first Countries filtered by the continent column
 * @method     Countries findOneByCurrencyId(int $currency_id) Return the first Countries filtered by the currency_id column
 * @method     Countries findOneByCurrencyCode(string $currency_code) Return the first Countries filtered by the currency_code column
 * @method     Countries findOneByCurrencyName(string $currency_name) Return the first Countries filtered by the currency_name column
 *
 * @method     array findById(int $id) Return Countries objects filtered by the id column
 * @method     array findByName(string $name) Return Countries objects filtered by the name column
 * @method     array findByLocalName(string $local_name) Return Countries objects filtered by the local_name column
 * @method     array findByCode(int $code) Return Countries objects filtered by the code column
 * @method     array findByIso2(string $iso2) Return Countries objects filtered by the iso2 column
 * @method     array findByIso3(string $iso3) Return Countries objects filtered by the iso3 column
 * @method     array findByContinent(string $continent) Return Countries objects filtered by the continent column
 * @method     array findByCurrencyId(int $currency_id) Return Countries objects filtered by the currency_id column
 * @method     array findByCurrencyCode(string $currency_code) Return Countries objects filtered by the currency_code column
 * @method     array findByCurrencyName(string $currency_name) Return Countries objects filtered by the currency_name column
 *
 * @package    propel.generator.src.Hanzo.Model.om
 */
abstract class BaseCountriesQuery extends ModelCriteria
{
	
	/**
	 * Initializes internal state of BaseCountriesQuery object.
	 *
	 * @param     string $dbName The dabase name
	 * @param     string $modelName The phpName of a model, e.g. 'Book'
	 * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
	 */
	public function __construct($dbName = 'default', $modelName = 'Hanzo\\Model\\Countries', $modelAlias = null)
	{
		parent::__construct($dbName, $modelName, $modelAlias);
	}

	/**
	 * Returns a new CountriesQuery object.
	 *
	 * @param     string $modelAlias The alias of a model in the query
	 * @param     Criteria $criteria Optional Criteria to build the query from
	 *
	 * @return    CountriesQuery
	 */
	public static function create($modelAlias = null, $criteria = null)
	{
		if ($criteria instanceof CountriesQuery) {
			return $criteria;
		}
		$query = new CountriesQuery();
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
	 * @param     mixed $key Primary key to use for the query
	 * @param     PropelPDO $con an optional connection object
	 *
	 * @return    Countries|array|mixed the result, formatted by the current formatter
	 */
	public function findPk($key, $con = null)
	{
		if ($key === null) {
			return null;
		}
		if ((null !== ($obj = CountriesPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
			// the object is alredy in the instance pool
			return $obj;
		}
		if ($con === null) {
			$con = Propel::getConnection(CountriesPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}
		$this->basePreSelect($con);
		if ($this->formatter || $this->modelAlias || $this->with || $this->select
		 || $this->selectColumns || $this->asColumns || $this->selectModifiers
		 || $this->map || $this->having || $this->joins) {
			return $this->findPkComplex($key, $con);
		} else {
			return $this->findPkSimple($key, $con);
		}
	}

	/**
	 * Find object by primary key using raw SQL to go fast.
	 * Bypass doSelect() and the object formatter by using generated code.
	 *
	 * @param     mixed $key Primary key to use for the query
	 * @param     PropelPDO $con A connection object
	 *
	 * @return    Countries A model object, or null if the key is not found
	 */
	protected function findPkSimple($key, $con)
	{
		$sql = 'SELECT `ID`, `NAME`, `LOCAL_NAME`, `CODE`, `ISO2`, `ISO3`, `CONTINENT`, `CURRENCY_ID`, `CURRENCY_CODE`, `CURRENCY_NAME` FROM `countries` WHERE `ID` = :p0';
		try {
			$stmt = $con->prepare($sql);
			$stmt->bindValue(':p0', $key, PDO::PARAM_INT);
			$stmt->execute();
		} catch (Exception $e) {
			Propel::log($e->getMessage(), Propel::LOG_ERR);
			throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), $e);
		}
		$obj = null;
		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$obj = new Countries();
			$obj->hydrate($row);
			CountriesPeer::addInstanceToPool($obj, (string) $key);
		}
		$stmt->closeCursor();

		return $obj;
	}

	/**
	 * Find object by primary key.
	 *
	 * @param     mixed $key Primary key to use for the query
	 * @param     PropelPDO $con A connection object
	 *
	 * @return    Countries|array|mixed the result, formatted by the current formatter
	 */
	protected function findPkComplex($key, $con)
	{
		// As the query uses a PK condition, no limit(1) is necessary.
		$criteria = $this->isKeepQuery() ? clone $this : $this;
		$stmt = $criteria
			->filterByPrimaryKey($key)
			->doSelect($con);
		return $criteria->getFormatter()->init($criteria)->formatOne($stmt);
	}

	/**
	 * Find objects by primary key
	 * <code>
	 * $objs = $c->findPks(array(12, 56, 832), $con);
	 * </code>
	 * @param     array $keys Primary keys to use for the query
	 * @param     PropelPDO $con an optional connection object
	 *
	 * @return    PropelObjectCollection|array|mixed the list of results, formatted by the current formatter
	 */
	public function findPks($keys, $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection($this->getDbName(), Propel::CONNECTION_READ);
		}
		$this->basePreSelect($con);
		$criteria = $this->isKeepQuery() ? clone $this : $this;
		$stmt = $criteria
			->filterByPrimaryKeys($keys)
			->doSelect($con);
		return $criteria->getFormatter()->init($criteria)->format($stmt);
	}

	/**
	 * Filter the query by primary key
	 *
	 * @param     mixed $key Primary key to use for the query
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function filterByPrimaryKey($key)
	{
		return $this->addUsingAlias(CountriesPeer::ID, $key, Criteria::EQUAL);
	}

	/**
	 * Filter the query by a list of primary keys
	 *
	 * @param     array $keys The list of primary key to use for the query
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function filterByPrimaryKeys($keys)
	{
		return $this->addUsingAlias(CountriesPeer::ID, $keys, Criteria::IN);
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
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function filterById($id = null, $comparison = null)
	{
		if (is_array($id) && null === $comparison) {
			$comparison = Criteria::IN;
		}
		return $this->addUsingAlias(CountriesPeer::ID, $id, $comparison);
	}

	/**
	 * Filter the query on the name column
	 *
	 * Example usage:
	 * <code>
	 * $query->filterByName('fooValue');   // WHERE name = 'fooValue'
	 * $query->filterByName('%fooValue%'); // WHERE name LIKE '%fooValue%'
	 * </code>
	 *
	 * @param     string $name The value to use as filter.
	 *              Accepts wildcards (* and % trigger a LIKE)
	 * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function filterByName($name = null, $comparison = null)
	{
		if (null === $comparison) {
			if (is_array($name)) {
				$comparison = Criteria::IN;
			} elseif (preg_match('/[\%\*]/', $name)) {
				$name = str_replace('*', '%', $name);
				$comparison = Criteria::LIKE;
			}
		}
		return $this->addUsingAlias(CountriesPeer::NAME, $name, $comparison);
	}

	/**
	 * Filter the query on the local_name column
	 *
	 * Example usage:
	 * <code>
	 * $query->filterByLocalName('fooValue');   // WHERE local_name = 'fooValue'
	 * $query->filterByLocalName('%fooValue%'); // WHERE local_name LIKE '%fooValue%'
	 * </code>
	 *
	 * @param     string $localName The value to use as filter.
	 *              Accepts wildcards (* and % trigger a LIKE)
	 * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function filterByLocalName($localName = null, $comparison = null)
	{
		if (null === $comparison) {
			if (is_array($localName)) {
				$comparison = Criteria::IN;
			} elseif (preg_match('/[\%\*]/', $localName)) {
				$localName = str_replace('*', '%', $localName);
				$comparison = Criteria::LIKE;
			}
		}
		return $this->addUsingAlias(CountriesPeer::LOCAL_NAME, $localName, $comparison);
	}

	/**
	 * Filter the query on the code column
	 *
	 * Example usage:
	 * <code>
	 * $query->filterByCode(1234); // WHERE code = 1234
	 * $query->filterByCode(array(12, 34)); // WHERE code IN (12, 34)
	 * $query->filterByCode(array('min' => 12)); // WHERE code > 12
	 * </code>
	 *
	 * @param     mixed $code The value to use as filter.
	 *              Use scalar values for equality.
	 *              Use array values for in_array() equivalent.
	 *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
	 * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function filterByCode($code = null, $comparison = null)
	{
		if (is_array($code)) {
			$useMinMax = false;
			if (isset($code['min'])) {
				$this->addUsingAlias(CountriesPeer::CODE, $code['min'], Criteria::GREATER_EQUAL);
				$useMinMax = true;
			}
			if (isset($code['max'])) {
				$this->addUsingAlias(CountriesPeer::CODE, $code['max'], Criteria::LESS_EQUAL);
				$useMinMax = true;
			}
			if ($useMinMax) {
				return $this;
			}
			if (null === $comparison) {
				$comparison = Criteria::IN;
			}
		}
		return $this->addUsingAlias(CountriesPeer::CODE, $code, $comparison);
	}

	/**
	 * Filter the query on the iso2 column
	 *
	 * Example usage:
	 * <code>
	 * $query->filterByIso2('fooValue');   // WHERE iso2 = 'fooValue'
	 * $query->filterByIso2('%fooValue%'); // WHERE iso2 LIKE '%fooValue%'
	 * </code>
	 *
	 * @param     string $iso2 The value to use as filter.
	 *              Accepts wildcards (* and % trigger a LIKE)
	 * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function filterByIso2($iso2 = null, $comparison = null)
	{
		if (null === $comparison) {
			if (is_array($iso2)) {
				$comparison = Criteria::IN;
			} elseif (preg_match('/[\%\*]/', $iso2)) {
				$iso2 = str_replace('*', '%', $iso2);
				$comparison = Criteria::LIKE;
			}
		}
		return $this->addUsingAlias(CountriesPeer::ISO2, $iso2, $comparison);
	}

	/**
	 * Filter the query on the iso3 column
	 *
	 * Example usage:
	 * <code>
	 * $query->filterByIso3('fooValue');   // WHERE iso3 = 'fooValue'
	 * $query->filterByIso3('%fooValue%'); // WHERE iso3 LIKE '%fooValue%'
	 * </code>
	 *
	 * @param     string $iso3 The value to use as filter.
	 *              Accepts wildcards (* and % trigger a LIKE)
	 * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function filterByIso3($iso3 = null, $comparison = null)
	{
		if (null === $comparison) {
			if (is_array($iso3)) {
				$comparison = Criteria::IN;
			} elseif (preg_match('/[\%\*]/', $iso3)) {
				$iso3 = str_replace('*', '%', $iso3);
				$comparison = Criteria::LIKE;
			}
		}
		return $this->addUsingAlias(CountriesPeer::ISO3, $iso3, $comparison);
	}

	/**
	 * Filter the query on the continent column
	 *
	 * Example usage:
	 * <code>
	 * $query->filterByContinent('fooValue');   // WHERE continent = 'fooValue'
	 * $query->filterByContinent('%fooValue%'); // WHERE continent LIKE '%fooValue%'
	 * </code>
	 *
	 * @param     string $continent The value to use as filter.
	 *              Accepts wildcards (* and % trigger a LIKE)
	 * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function filterByContinent($continent = null, $comparison = null)
	{
		if (null === $comparison) {
			if (is_array($continent)) {
				$comparison = Criteria::IN;
			} elseif (preg_match('/[\%\*]/', $continent)) {
				$continent = str_replace('*', '%', $continent);
				$comparison = Criteria::LIKE;
			}
		}
		return $this->addUsingAlias(CountriesPeer::CONTINENT, $continent, $comparison);
	}

	/**
	 * Filter the query on the currency_id column
	 *
	 * Example usage:
	 * <code>
	 * $query->filterByCurrencyId(1234); // WHERE currency_id = 1234
	 * $query->filterByCurrencyId(array(12, 34)); // WHERE currency_id IN (12, 34)
	 * $query->filterByCurrencyId(array('min' => 12)); // WHERE currency_id > 12
	 * </code>
	 *
	 * @param     mixed $currencyId The value to use as filter.
	 *              Use scalar values for equality.
	 *              Use array values for in_array() equivalent.
	 *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
	 * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function filterByCurrencyId($currencyId = null, $comparison = null)
	{
		if (is_array($currencyId)) {
			$useMinMax = false;
			if (isset($currencyId['min'])) {
				$this->addUsingAlias(CountriesPeer::CURRENCY_ID, $currencyId['min'], Criteria::GREATER_EQUAL);
				$useMinMax = true;
			}
			if (isset($currencyId['max'])) {
				$this->addUsingAlias(CountriesPeer::CURRENCY_ID, $currencyId['max'], Criteria::LESS_EQUAL);
				$useMinMax = true;
			}
			if ($useMinMax) {
				return $this;
			}
			if (null === $comparison) {
				$comparison = Criteria::IN;
			}
		}
		return $this->addUsingAlias(CountriesPeer::CURRENCY_ID, $currencyId, $comparison);
	}

	/**
	 * Filter the query on the currency_code column
	 *
	 * Example usage:
	 * <code>
	 * $query->filterByCurrencyCode('fooValue');   // WHERE currency_code = 'fooValue'
	 * $query->filterByCurrencyCode('%fooValue%'); // WHERE currency_code LIKE '%fooValue%'
	 * </code>
	 *
	 * @param     string $currencyCode The value to use as filter.
	 *              Accepts wildcards (* and % trigger a LIKE)
	 * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function filterByCurrencyCode($currencyCode = null, $comparison = null)
	{
		if (null === $comparison) {
			if (is_array($currencyCode)) {
				$comparison = Criteria::IN;
			} elseif (preg_match('/[\%\*]/', $currencyCode)) {
				$currencyCode = str_replace('*', '%', $currencyCode);
				$comparison = Criteria::LIKE;
			}
		}
		return $this->addUsingAlias(CountriesPeer::CURRENCY_CODE, $currencyCode, $comparison);
	}

	/**
	 * Filter the query on the currency_name column
	 *
	 * Example usage:
	 * <code>
	 * $query->filterByCurrencyName('fooValue');   // WHERE currency_name = 'fooValue'
	 * $query->filterByCurrencyName('%fooValue%'); // WHERE currency_name LIKE '%fooValue%'
	 * </code>
	 *
	 * @param     string $currencyName The value to use as filter.
	 *              Accepts wildcards (* and % trigger a LIKE)
	 * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function filterByCurrencyName($currencyName = null, $comparison = null)
	{
		if (null === $comparison) {
			if (is_array($currencyName)) {
				$comparison = Criteria::IN;
			} elseif (preg_match('/[\%\*]/', $currencyName)) {
				$currencyName = str_replace('*', '%', $currencyName);
				$comparison = Criteria::LIKE;
			}
		}
		return $this->addUsingAlias(CountriesPeer::CURRENCY_NAME, $currencyName, $comparison);
	}

	/**
	 * Filter the query by a related Addresses object
	 *
	 * @param     Addresses $addresses  the related object to use as filter
	 * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function filterByAddresses($addresses, $comparison = null)
	{
		if ($addresses instanceof Addresses) {
			return $this
				->addUsingAlias(CountriesPeer::ID, $addresses->getCountriesId(), $comparison);
		} elseif ($addresses instanceof PropelCollection) {
			return $this
				->useAddressesQuery()
				->filterByPrimaryKeys($addresses->getPrimaryKeys())
				->endUse();
		} else {
			throw new PropelException('filterByAddresses() only accepts arguments of type Addresses or PropelCollection');
		}
	}

	/**
	 * Adds a JOIN clause to the query using the Addresses relation
	 *
	 * @param     string $relationAlias optional alias for the relation
	 * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function joinAddresses($relationAlias = null, $joinType = Criteria::INNER_JOIN)
	{
		$tableMap = $this->getTableMap();
		$relationMap = $tableMap->getRelation('Addresses');

		// create a ModelJoin object for this join
		$join = new ModelJoin();
		$join->setJoinType($joinType);
		$join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
		if ($previousJoin = $this->getPreviousJoin()) {
			$join->setPreviousJoin($previousJoin);
		}

		// add the ModelJoin to the current object
		if($relationAlias) {
			$this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
			$this->addJoinObject($join, $relationAlias);
		} else {
			$this->addJoinObject($join, 'Addresses');
		}

		return $this;
	}

	/**
	 * Use the Addresses relation Addresses object
	 *
	 * @see       useQuery()
	 *
	 * @param     string $relationAlias optional alias for the relation,
	 *                                   to be used as main alias in the secondary query
	 * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
	 *
	 * @return    \Hanzo\Model\AddressesQuery A secondary query class using the current class as primary query
	 */
	public function useAddressesQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
	{
		return $this
			->joinAddresses($relationAlias, $joinType)
			->useQuery($relationAlias ? $relationAlias : 'Addresses', '\Hanzo\Model\AddressesQuery');
	}

	/**
	 * Filter the query by a related ZipToCity object
	 *
	 * @param     ZipToCity $zipToCity  the related object to use as filter
	 * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function filterByZipToCity($zipToCity, $comparison = null)
	{
		if ($zipToCity instanceof ZipToCity) {
			return $this
				->addUsingAlias(CountriesPeer::ISO2, $zipToCity->getCountriesIso2(), $comparison);
		} elseif ($zipToCity instanceof PropelCollection) {
			return $this
				->useZipToCityQuery()
				->filterByPrimaryKeys($zipToCity->getPrimaryKeys())
				->endUse();
		} else {
			throw new PropelException('filterByZipToCity() only accepts arguments of type ZipToCity or PropelCollection');
		}
	}

	/**
	 * Adds a JOIN clause to the query using the ZipToCity relation
	 *
	 * @param     string $relationAlias optional alias for the relation
	 * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function joinZipToCity($relationAlias = null, $joinType = Criteria::INNER_JOIN)
	{
		$tableMap = $this->getTableMap();
		$relationMap = $tableMap->getRelation('ZipToCity');

		// create a ModelJoin object for this join
		$join = new ModelJoin();
		$join->setJoinType($joinType);
		$join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
		if ($previousJoin = $this->getPreviousJoin()) {
			$join->setPreviousJoin($previousJoin);
		}

		// add the ModelJoin to the current object
		if($relationAlias) {
			$this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
			$this->addJoinObject($join, $relationAlias);
		} else {
			$this->addJoinObject($join, 'ZipToCity');
		}

		return $this;
	}

	/**
	 * Use the ZipToCity relation ZipToCity object
	 *
	 * @see       useQuery()
	 *
	 * @param     string $relationAlias optional alias for the relation,
	 *                                   to be used as main alias in the secondary query
	 * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
	 *
	 * @return    \Hanzo\Model\ZipToCityQuery A secondary query class using the current class as primary query
	 */
	public function useZipToCityQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
	{
		return $this
			->joinZipToCity($relationAlias, $joinType)
			->useQuery($relationAlias ? $relationAlias : 'ZipToCity', '\Hanzo\Model\ZipToCityQuery');
	}

	/**
	 * Filter the query by a related Orders object
	 *
	 * @param     Orders $orders  the related object to use as filter
	 * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function filterByOrdersRelatedByBillingCountriesId($orders, $comparison = null)
	{
		if ($orders instanceof Orders) {
			return $this
				->addUsingAlias(CountriesPeer::ID, $orders->getBillingCountriesId(), $comparison);
		} elseif ($orders instanceof PropelCollection) {
			return $this
				->useOrdersRelatedByBillingCountriesIdQuery()
				->filterByPrimaryKeys($orders->getPrimaryKeys())
				->endUse();
		} else {
			throw new PropelException('filterByOrdersRelatedByBillingCountriesId() only accepts arguments of type Orders or PropelCollection');
		}
	}

	/**
	 * Adds a JOIN clause to the query using the OrdersRelatedByBillingCountriesId relation
	 *
	 * @param     string $relationAlias optional alias for the relation
	 * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function joinOrdersRelatedByBillingCountriesId($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
	{
		$tableMap = $this->getTableMap();
		$relationMap = $tableMap->getRelation('OrdersRelatedByBillingCountriesId');

		// create a ModelJoin object for this join
		$join = new ModelJoin();
		$join->setJoinType($joinType);
		$join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
		if ($previousJoin = $this->getPreviousJoin()) {
			$join->setPreviousJoin($previousJoin);
		}

		// add the ModelJoin to the current object
		if($relationAlias) {
			$this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
			$this->addJoinObject($join, $relationAlias);
		} else {
			$this->addJoinObject($join, 'OrdersRelatedByBillingCountriesId');
		}

		return $this;
	}

	/**
	 * Use the OrdersRelatedByBillingCountriesId relation Orders object
	 *
	 * @see       useQuery()
	 *
	 * @param     string $relationAlias optional alias for the relation,
	 *                                   to be used as main alias in the secondary query
	 * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
	 *
	 * @return    \Hanzo\Model\OrdersQuery A secondary query class using the current class as primary query
	 */
	public function useOrdersRelatedByBillingCountriesIdQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
	{
		return $this
			->joinOrdersRelatedByBillingCountriesId($relationAlias, $joinType)
			->useQuery($relationAlias ? $relationAlias : 'OrdersRelatedByBillingCountriesId', '\Hanzo\Model\OrdersQuery');
	}

	/**
	 * Filter the query by a related Orders object
	 *
	 * @param     Orders $orders  the related object to use as filter
	 * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function filterByOrdersRelatedByDeliveryCountriesId($orders, $comparison = null)
	{
		if ($orders instanceof Orders) {
			return $this
				->addUsingAlias(CountriesPeer::ID, $orders->getDeliveryCountriesId(), $comparison);
		} elseif ($orders instanceof PropelCollection) {
			return $this
				->useOrdersRelatedByDeliveryCountriesIdQuery()
				->filterByPrimaryKeys($orders->getPrimaryKeys())
				->endUse();
		} else {
			throw new PropelException('filterByOrdersRelatedByDeliveryCountriesId() only accepts arguments of type Orders or PropelCollection');
		}
	}

	/**
	 * Adds a JOIN clause to the query using the OrdersRelatedByDeliveryCountriesId relation
	 *
	 * @param     string $relationAlias optional alias for the relation
	 * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function joinOrdersRelatedByDeliveryCountriesId($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
	{
		$tableMap = $this->getTableMap();
		$relationMap = $tableMap->getRelation('OrdersRelatedByDeliveryCountriesId');

		// create a ModelJoin object for this join
		$join = new ModelJoin();
		$join->setJoinType($joinType);
		$join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
		if ($previousJoin = $this->getPreviousJoin()) {
			$join->setPreviousJoin($previousJoin);
		}

		// add the ModelJoin to the current object
		if($relationAlias) {
			$this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
			$this->addJoinObject($join, $relationAlias);
		} else {
			$this->addJoinObject($join, 'OrdersRelatedByDeliveryCountriesId');
		}

		return $this;
	}

	/**
	 * Use the OrdersRelatedByDeliveryCountriesId relation Orders object
	 *
	 * @see       useQuery()
	 *
	 * @param     string $relationAlias optional alias for the relation,
	 *                                   to be used as main alias in the secondary query
	 * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
	 *
	 * @return    \Hanzo\Model\OrdersQuery A secondary query class using the current class as primary query
	 */
	public function useOrdersRelatedByDeliveryCountriesIdQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
	{
		return $this
			->joinOrdersRelatedByDeliveryCountriesId($relationAlias, $joinType)
			->useQuery($relationAlias ? $relationAlias : 'OrdersRelatedByDeliveryCountriesId', '\Hanzo\Model\OrdersQuery');
	}

	/**
	 * Exclude object from result
	 *
	 * @param     Countries $countries Object to remove from the list of results
	 *
	 * @return    CountriesQuery The current query, for fluid interface
	 */
	public function prune($countries = null)
	{
		if ($countries) {
			$this->addUsingAlias(CountriesPeer::ID, $countries->getId(), Criteria::NOT_EQUAL);
		}

		return $this;
	}

} // BaseCountriesQuery