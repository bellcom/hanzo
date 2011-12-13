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
use Hanzo\Model\Products;
use Hanzo\Model\ProductsImages;
use Hanzo\Model\ProductsImagesCategoriesSort;
use Hanzo\Model\ProductsImagesPeer;
use Hanzo\Model\ProductsImagesProductReferences;
use Hanzo\Model\ProductsImagesQuery;

/**
 * Base class that represents a query for the 'products_images' table.
 *
 * 
 *
 * @method     ProductsImagesQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ProductsImagesQuery orderByProductsId($order = Criteria::ASC) Order by the products_id column
 * @method     ProductsImagesQuery orderByImage($order = Criteria::ASC) Order by the image column
 *
 * @method     ProductsImagesQuery groupById() Group by the id column
 * @method     ProductsImagesQuery groupByProductsId() Group by the products_id column
 * @method     ProductsImagesQuery groupByImage() Group by the image column
 *
 * @method     ProductsImagesQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ProductsImagesQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ProductsImagesQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ProductsImagesQuery leftJoinProducts($relationAlias = null) Adds a LEFT JOIN clause to the query using the Products relation
 * @method     ProductsImagesQuery rightJoinProducts($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Products relation
 * @method     ProductsImagesQuery innerJoinProducts($relationAlias = null) Adds a INNER JOIN clause to the query using the Products relation
 *
 * @method     ProductsImagesQuery leftJoinProductsImagesCategoriesSort($relationAlias = null) Adds a LEFT JOIN clause to the query using the ProductsImagesCategoriesSort relation
 * @method     ProductsImagesQuery rightJoinProductsImagesCategoriesSort($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ProductsImagesCategoriesSort relation
 * @method     ProductsImagesQuery innerJoinProductsImagesCategoriesSort($relationAlias = null) Adds a INNER JOIN clause to the query using the ProductsImagesCategoriesSort relation
 *
 * @method     ProductsImagesQuery leftJoinProductsImagesProductReferences($relationAlias = null) Adds a LEFT JOIN clause to the query using the ProductsImagesProductReferences relation
 * @method     ProductsImagesQuery rightJoinProductsImagesProductReferences($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ProductsImagesProductReferences relation
 * @method     ProductsImagesQuery innerJoinProductsImagesProductReferences($relationAlias = null) Adds a INNER JOIN clause to the query using the ProductsImagesProductReferences relation
 *
 * @method     ProductsImages findOne(PropelPDO $con = null) Return the first ProductsImages matching the query
 * @method     ProductsImages findOneOrCreate(PropelPDO $con = null) Return the first ProductsImages matching the query, or a new ProductsImages object populated from the query conditions when no match is found
 *
 * @method     ProductsImages findOneById(int $id) Return the first ProductsImages filtered by the id column
 * @method     ProductsImages findOneByProductsId(int $products_id) Return the first ProductsImages filtered by the products_id column
 * @method     ProductsImages findOneByImage(string $image) Return the first ProductsImages filtered by the image column
 *
 * @method     array findById(int $id) Return ProductsImages objects filtered by the id column
 * @method     array findByProductsId(int $products_id) Return ProductsImages objects filtered by the products_id column
 * @method     array findByImage(string $image) Return ProductsImages objects filtered by the image column
 *
 * @package    propel.generator.src.Hanzo.Model.om
 */
abstract class BaseProductsImagesQuery extends ModelCriteria
{
	
	/**
	 * Initializes internal state of BaseProductsImagesQuery object.
	 *
	 * @param     string $dbName The dabase name
	 * @param     string $modelName The phpName of a model, e.g. 'Book'
	 * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
	 */
	public function __construct($dbName = 'default', $modelName = 'Hanzo\\Model\\ProductsImages', $modelAlias = null)
	{
		parent::__construct($dbName, $modelName, $modelAlias);
	}

	/**
	 * Returns a new ProductsImagesQuery object.
	 *
	 * @param     string $modelAlias The alias of a model in the query
	 * @param     Criteria $criteria Optional Criteria to build the query from
	 *
	 * @return    ProductsImagesQuery
	 */
	public static function create($modelAlias = null, $criteria = null)
	{
		if ($criteria instanceof ProductsImagesQuery) {
			return $criteria;
		}
		$query = new ProductsImagesQuery();
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
	 * @return    ProductsImages|array|mixed the result, formatted by the current formatter
	 */
	public function findPk($key, $con = null)
	{
		if ($key === null) {
			return null;
		}
		if ((null !== ($obj = ProductsImagesPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
			// the object is alredy in the instance pool
			return $obj;
		}
		if ($con === null) {
			$con = Propel::getConnection(ProductsImagesPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
	 * @return    ProductsImages A model object, or null if the key is not found
	 */
	protected function findPkSimple($key, $con)
	{
		$sql = 'SELECT `ID`, `PRODUCTS_ID`, `IMAGE` FROM `products_images` WHERE `ID` = :p0';
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
			$obj = new ProductsImages();
			$obj->hydrate($row);
			ProductsImagesPeer::addInstanceToPool($obj, (string) $row[0]);
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
	 * @return    ProductsImages|array|mixed the result, formatted by the current formatter
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
	 * @return    ProductsImagesQuery The current query, for fluid interface
	 */
	public function filterByPrimaryKey($key)
	{
		return $this->addUsingAlias(ProductsImagesPeer::ID, $key, Criteria::EQUAL);
	}

	/**
	 * Filter the query by a list of primary keys
	 *
	 * @param     array $keys The list of primary key to use for the query
	 *
	 * @return    ProductsImagesQuery The current query, for fluid interface
	 */
	public function filterByPrimaryKeys($keys)
	{
		return $this->addUsingAlias(ProductsImagesPeer::ID, $keys, Criteria::IN);
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
	 * @return    ProductsImagesQuery The current query, for fluid interface
	 */
	public function filterById($id = null, $comparison = null)
	{
		if (is_array($id) && null === $comparison) {
			$comparison = Criteria::IN;
		}
		return $this->addUsingAlias(ProductsImagesPeer::ID, $id, $comparison);
	}

	/**
	 * Filter the query on the products_id column
	 *
	 * Example usage:
	 * <code>
	 * $query->filterByProductsId(1234); // WHERE products_id = 1234
	 * $query->filterByProductsId(array(12, 34)); // WHERE products_id IN (12, 34)
	 * $query->filterByProductsId(array('min' => 12)); // WHERE products_id > 12
	 * </code>
	 *
	 * @see       filterByProducts()
	 *
	 * @param     mixed $productsId The value to use as filter.
	 *              Use scalar values for equality.
	 *              Use array values for in_array() equivalent.
	 *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
	 * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 *
	 * @return    ProductsImagesQuery The current query, for fluid interface
	 */
	public function filterByProductsId($productsId = null, $comparison = null)
	{
		if (is_array($productsId)) {
			$useMinMax = false;
			if (isset($productsId['min'])) {
				$this->addUsingAlias(ProductsImagesPeer::PRODUCTS_ID, $productsId['min'], Criteria::GREATER_EQUAL);
				$useMinMax = true;
			}
			if (isset($productsId['max'])) {
				$this->addUsingAlias(ProductsImagesPeer::PRODUCTS_ID, $productsId['max'], Criteria::LESS_EQUAL);
				$useMinMax = true;
			}
			if ($useMinMax) {
				return $this;
			}
			if (null === $comparison) {
				$comparison = Criteria::IN;
			}
		}
		return $this->addUsingAlias(ProductsImagesPeer::PRODUCTS_ID, $productsId, $comparison);
	}

	/**
	 * Filter the query on the image column
	 *
	 * Example usage:
	 * <code>
	 * $query->filterByImage('fooValue');   // WHERE image = 'fooValue'
	 * $query->filterByImage('%fooValue%'); // WHERE image LIKE '%fooValue%'
	 * </code>
	 *
	 * @param     string $image The value to use as filter.
	 *              Accepts wildcards (* and % trigger a LIKE)
	 * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 *
	 * @return    ProductsImagesQuery The current query, for fluid interface
	 */
	public function filterByImage($image = null, $comparison = null)
	{
		if (null === $comparison) {
			if (is_array($image)) {
				$comparison = Criteria::IN;
			} elseif (preg_match('/[\%\*]/', $image)) {
				$image = str_replace('*', '%', $image);
				$comparison = Criteria::LIKE;
			}
		}
		return $this->addUsingAlias(ProductsImagesPeer::IMAGE, $image, $comparison);
	}

	/**
	 * Filter the query by a related Products object
	 *
	 * @param     Products|PropelCollection $products The related object(s) to use as filter
	 * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 *
	 * @return    ProductsImagesQuery The current query, for fluid interface
	 */
	public function filterByProducts($products, $comparison = null)
	{
		if ($products instanceof Products) {
			return $this
				->addUsingAlias(ProductsImagesPeer::PRODUCTS_ID, $products->getId(), $comparison);
		} elseif ($products instanceof PropelCollection) {
			if (null === $comparison) {
				$comparison = Criteria::IN;
			}
			return $this
				->addUsingAlias(ProductsImagesPeer::PRODUCTS_ID, $products->toKeyValue('PrimaryKey', 'Id'), $comparison);
		} else {
			throw new PropelException('filterByProducts() only accepts arguments of type Products or PropelCollection');
		}
	}

	/**
	 * Adds a JOIN clause to the query using the Products relation
	 *
	 * @param     string $relationAlias optional alias for the relation
	 * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
	 *
	 * @return    ProductsImagesQuery The current query, for fluid interface
	 */
	public function joinProducts($relationAlias = null, $joinType = Criteria::INNER_JOIN)
	{
		$tableMap = $this->getTableMap();
		$relationMap = $tableMap->getRelation('Products');

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
			$this->addJoinObject($join, 'Products');
		}

		return $this;
	}

	/**
	 * Use the Products relation Products object
	 *
	 * @see       useQuery()
	 *
	 * @param     string $relationAlias optional alias for the relation,
	 *                                   to be used as main alias in the secondary query
	 * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
	 *
	 * @return    \Hanzo\Model\ProductsQuery A secondary query class using the current class as primary query
	 */
	public function useProductsQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
	{
		return $this
			->joinProducts($relationAlias, $joinType)
			->useQuery($relationAlias ? $relationAlias : 'Products', '\Hanzo\Model\ProductsQuery');
	}

	/**
	 * Filter the query by a related ProductsImagesCategoriesSort object
	 *
	 * @param     ProductsImagesCategoriesSort $productsImagesCategoriesSort  the related object to use as filter
	 * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 *
	 * @return    ProductsImagesQuery The current query, for fluid interface
	 */
	public function filterByProductsImagesCategoriesSort($productsImagesCategoriesSort, $comparison = null)
	{
		if ($productsImagesCategoriesSort instanceof ProductsImagesCategoriesSort) {
			return $this
				->addUsingAlias(ProductsImagesPeer::ID, $productsImagesCategoriesSort->getProductsImagesId(), $comparison);
		} elseif ($productsImagesCategoriesSort instanceof PropelCollection) {
			return $this
				->useProductsImagesCategoriesSortQuery()
				->filterByPrimaryKeys($productsImagesCategoriesSort->getPrimaryKeys())
				->endUse();
		} else {
			throw new PropelException('filterByProductsImagesCategoriesSort() only accepts arguments of type ProductsImagesCategoriesSort or PropelCollection');
		}
	}

	/**
	 * Adds a JOIN clause to the query using the ProductsImagesCategoriesSort relation
	 *
	 * @param     string $relationAlias optional alias for the relation
	 * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
	 *
	 * @return    ProductsImagesQuery The current query, for fluid interface
	 */
	public function joinProductsImagesCategoriesSort($relationAlias = null, $joinType = Criteria::INNER_JOIN)
	{
		$tableMap = $this->getTableMap();
		$relationMap = $tableMap->getRelation('ProductsImagesCategoriesSort');

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
			$this->addJoinObject($join, 'ProductsImagesCategoriesSort');
		}

		return $this;
	}

	/**
	 * Use the ProductsImagesCategoriesSort relation ProductsImagesCategoriesSort object
	 *
	 * @see       useQuery()
	 *
	 * @param     string $relationAlias optional alias for the relation,
	 *                                   to be used as main alias in the secondary query
	 * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
	 *
	 * @return    \Hanzo\Model\ProductsImagesCategoriesSortQuery A secondary query class using the current class as primary query
	 */
	public function useProductsImagesCategoriesSortQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
	{
		return $this
			->joinProductsImagesCategoriesSort($relationAlias, $joinType)
			->useQuery($relationAlias ? $relationAlias : 'ProductsImagesCategoriesSort', '\Hanzo\Model\ProductsImagesCategoriesSortQuery');
	}

	/**
	 * Filter the query by a related ProductsImagesProductReferences object
	 *
	 * @param     ProductsImagesProductReferences $productsImagesProductReferences  the related object to use as filter
	 * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 *
	 * @return    ProductsImagesQuery The current query, for fluid interface
	 */
	public function filterByProductsImagesProductReferences($productsImagesProductReferences, $comparison = null)
	{
		if ($productsImagesProductReferences instanceof ProductsImagesProductReferences) {
			return $this
				->addUsingAlias(ProductsImagesPeer::ID, $productsImagesProductReferences->getProductsImagesId(), $comparison);
		} elseif ($productsImagesProductReferences instanceof PropelCollection) {
			return $this
				->useProductsImagesProductReferencesQuery()
				->filterByPrimaryKeys($productsImagesProductReferences->getPrimaryKeys())
				->endUse();
		} else {
			throw new PropelException('filterByProductsImagesProductReferences() only accepts arguments of type ProductsImagesProductReferences or PropelCollection');
		}
	}

	/**
	 * Adds a JOIN clause to the query using the ProductsImagesProductReferences relation
	 *
	 * @param     string $relationAlias optional alias for the relation
	 * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
	 *
	 * @return    ProductsImagesQuery The current query, for fluid interface
	 */
	public function joinProductsImagesProductReferences($relationAlias = null, $joinType = Criteria::INNER_JOIN)
	{
		$tableMap = $this->getTableMap();
		$relationMap = $tableMap->getRelation('ProductsImagesProductReferences');

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
			$this->addJoinObject($join, 'ProductsImagesProductReferences');
		}

		return $this;
	}

	/**
	 * Use the ProductsImagesProductReferences relation ProductsImagesProductReferences object
	 *
	 * @see       useQuery()
	 *
	 * @param     string $relationAlias optional alias for the relation,
	 *                                   to be used as main alias in the secondary query
	 * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
	 *
	 * @return    \Hanzo\Model\ProductsImagesProductReferencesQuery A secondary query class using the current class as primary query
	 */
	public function useProductsImagesProductReferencesQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
	{
		return $this
			->joinProductsImagesProductReferences($relationAlias, $joinType)
			->useQuery($relationAlias ? $relationAlias : 'ProductsImagesProductReferences', '\Hanzo\Model\ProductsImagesProductReferencesQuery');
	}

	/**
	 * Exclude object from result
	 *
	 * @param     ProductsImages $productsImages Object to remove from the list of results
	 *
	 * @return    ProductsImagesQuery The current query, for fluid interface
	 */
	public function prune($productsImages = null)
	{
		if ($productsImages) {
			$this->addUsingAlias(ProductsImagesPeer::ID, $productsImages->getId(), Criteria::NOT_EQUAL);
		}

		return $this;
	}

} // BaseProductsImagesQuery