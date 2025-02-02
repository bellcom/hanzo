<?php

namespace Hanzo\Model\om;

use \BasePeer;
use \Criteria;
use \PDO;
use \PDOStatement;
use \Propel;
use \PropelException;
use \PropelPDO;
use Glorpen\Propel\PropelBundle\Dispatcher\EventDispatcherProxy;
use Glorpen\Propel\PropelBundle\Events\PeerEvent;
use Hanzo\Model\CategoriesPeer;
use Hanzo\Model\ProductsImagesCategoriesSort;
use Hanzo\Model\ProductsImagesCategoriesSortPeer;
use Hanzo\Model\ProductsImagesPeer;
use Hanzo\Model\ProductsPeer;
use Hanzo\Model\map\ProductsImagesCategoriesSortTableMap;

abstract class BaseProductsImagesCategoriesSortPeer
{

    /** the default database name for this class */
    const DATABASE_NAME = 'default';

    /** the table name for this class */
    const TABLE_NAME = 'products_images_categories_sort';

    /** the related Propel class for this table */
    const OM_CLASS = 'Hanzo\\Model\\ProductsImagesCategoriesSort';

    /** the related TableMap class for this table */
    const TM_CLASS = 'Hanzo\\Model\\map\\ProductsImagesCategoriesSortTableMap';

    /** The total number of columns. */
    const NUM_COLUMNS = 4;

    /** The number of lazy-loaded columns. */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /** The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS) */
    const NUM_HYDRATE_COLUMNS = 4;

    /** the column name for the products_id field */
    const PRODUCTS_ID = 'products_images_categories_sort.products_id';

    /** the column name for the categories_id field */
    const CATEGORIES_ID = 'products_images_categories_sort.categories_id';

    /** the column name for the products_images_id field */
    const PRODUCTS_IMAGES_ID = 'products_images_categories_sort.products_images_id';

    /** the column name for the sort field */
    const SORT = 'products_images_categories_sort.sort';

    /** The default string format for model objects of the related table **/
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * An identity map to hold any loaded instances of ProductsImagesCategoriesSort objects.
     * This must be public so that other peer classes can access this when hydrating from JOIN
     * queries.
     * @var        array ProductsImagesCategoriesSort[]
     */
    public static $instances = array();


    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. ProductsImagesCategoriesSortPeer::$fieldNames[ProductsImagesCategoriesSortPeer::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        BasePeer::TYPE_PHPNAME => array ('ProductsId', 'CategoriesId', 'ProductsImagesId', 'Sort', ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('productsId', 'categoriesId', 'productsImagesId', 'sort', ),
        BasePeer::TYPE_COLNAME => array (ProductsImagesCategoriesSortPeer::PRODUCTS_ID, ProductsImagesCategoriesSortPeer::CATEGORIES_ID, ProductsImagesCategoriesSortPeer::PRODUCTS_IMAGES_ID, ProductsImagesCategoriesSortPeer::SORT, ),
        BasePeer::TYPE_RAW_COLNAME => array ('PRODUCTS_ID', 'CATEGORIES_ID', 'PRODUCTS_IMAGES_ID', 'SORT', ),
        BasePeer::TYPE_FIELDNAME => array ('products_id', 'categories_id', 'products_images_id', 'sort', ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. ProductsImagesCategoriesSortPeer::$fieldNames[BasePeer::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        BasePeer::TYPE_PHPNAME => array ('ProductsId' => 0, 'CategoriesId' => 1, 'ProductsImagesId' => 2, 'Sort' => 3, ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('productsId' => 0, 'categoriesId' => 1, 'productsImagesId' => 2, 'sort' => 3, ),
        BasePeer::TYPE_COLNAME => array (ProductsImagesCategoriesSortPeer::PRODUCTS_ID => 0, ProductsImagesCategoriesSortPeer::CATEGORIES_ID => 1, ProductsImagesCategoriesSortPeer::PRODUCTS_IMAGES_ID => 2, ProductsImagesCategoriesSortPeer::SORT => 3, ),
        BasePeer::TYPE_RAW_COLNAME => array ('PRODUCTS_ID' => 0, 'CATEGORIES_ID' => 1, 'PRODUCTS_IMAGES_ID' => 2, 'SORT' => 3, ),
        BasePeer::TYPE_FIELDNAME => array ('products_id' => 0, 'categories_id' => 1, 'products_images_id' => 2, 'sort' => 3, ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, )
    );

    /**
     * Translates a fieldname to another type
     *
     * @param      string $name field name
     * @param      string $fromType One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *                         BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
     * @param      string $toType   One of the class type constants
     * @return string          translated name of the field.
     * @throws PropelException - if the specified name could not be found in the fieldname mappings.
     */
    public static function translateFieldName($name, $fromType, $toType)
    {
        $toNames = ProductsImagesCategoriesSortPeer::getFieldNames($toType);
        $key = isset(ProductsImagesCategoriesSortPeer::$fieldKeys[$fromType][$name]) ? ProductsImagesCategoriesSortPeer::$fieldKeys[$fromType][$name] : null;
        if ($key === null) {
            throw new PropelException("'$name' could not be found in the field names of type '$fromType'. These are: " . print_r(ProductsImagesCategoriesSortPeer::$fieldKeys[$fromType], true));
        }

        return $toNames[$key];
    }

    /**
     * Returns an array of field names.
     *
     * @param      string $type The type of fieldnames to return:
     *                      One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *                      BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
     * @return array           A list of field names
     * @throws PropelException - if the type is not valid.
     */
    public static function getFieldNames($type = BasePeer::TYPE_PHPNAME)
    {
        if (!array_key_exists($type, ProductsImagesCategoriesSortPeer::$fieldNames)) {
            throw new PropelException('Method getFieldNames() expects the parameter $type to be one of the class constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME, BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM. ' . $type . ' was given.');
        }

        return ProductsImagesCategoriesSortPeer::$fieldNames[$type];
    }

    /**
     * Convenience method which changes table.column to alias.column.
     *
     * Using this method you can maintain SQL abstraction while using column aliases.
     * <code>
     *		$c->addAlias("alias1", TablePeer::TABLE_NAME);
     *		$c->addJoin(TablePeer::alias("alias1", TablePeer::PRIMARY_KEY_COLUMN), TablePeer::PRIMARY_KEY_COLUMN);
     * </code>
     * @param      string $alias The alias for the current table.
     * @param      string $column The column name for current table. (i.e. ProductsImagesCategoriesSortPeer::COLUMN_NAME).
     * @return string
     */
    public static function alias($alias, $column)
    {
        return str_replace(ProductsImagesCategoriesSortPeer::TABLE_NAME.'.', $alias.'.', $column);
    }

    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param      Criteria $criteria object containing the columns to add.
     * @param      string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(ProductsImagesCategoriesSortPeer::PRODUCTS_ID);
            $criteria->addSelectColumn(ProductsImagesCategoriesSortPeer::CATEGORIES_ID);
            $criteria->addSelectColumn(ProductsImagesCategoriesSortPeer::PRODUCTS_IMAGES_ID);
            $criteria->addSelectColumn(ProductsImagesCategoriesSortPeer::SORT);
        } else {
            $criteria->addSelectColumn($alias . '.products_id');
            $criteria->addSelectColumn($alias . '.categories_id');
            $criteria->addSelectColumn($alias . '.products_images_id');
            $criteria->addSelectColumn($alias . '.sort');
        }
    }

    /**
     * Returns the number of rows matching criteria.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @return int Number of matching rows.
     */
    public static function doCount(Criteria $criteria, $distinct = false, PropelPDO $con = null)
    {
        // we may modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(ProductsImagesCategoriesSortPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            ProductsImagesCategoriesSortPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count
        $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME); // Set the correct dbName

        if ($con === null) {
            $con = Propel::getConnection(ProductsImagesCategoriesSortPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }
        // BasePeer returns a PDOStatement
        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }
    /**
     * Selects one object from the DB.
     *
     * @param      Criteria $criteria object used to create the SELECT statement.
     * @param      PropelPDO $con
     * @return ProductsImagesCategoriesSort
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectOne(Criteria $criteria, PropelPDO $con = null)
    {
        $critcopy = clone $criteria;
        $critcopy->setLimit(1);
        $objects = ProductsImagesCategoriesSortPeer::doSelect($critcopy, $con);
        if ($objects) {
            return $objects[0];
        }

        return null;
    }
    /**
     * Selects several row from the DB.
     *
     * @param      Criteria $criteria The Criteria object used to build the SELECT statement.
     * @param      PropelPDO $con
     * @return array           Array of selected Objects
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelect(Criteria $criteria, PropelPDO $con = null)
    {
        return ProductsImagesCategoriesSortPeer::populateObjects(ProductsImagesCategoriesSortPeer::doSelectStmt($criteria, $con));
    }
    /**
     * Prepares the Criteria object and uses the parent doSelect() method to execute a PDOStatement.
     *
     * Use this method directly if you want to work with an executed statement directly (for example
     * to perform your own object hydration).
     *
     * @param      Criteria $criteria The Criteria object used to build the SELECT statement.
     * @param      PropelPDO $con The connection to use
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     * @return PDOStatement The executed PDOStatement object.
     * @see        BasePeer::doSelect()
     */
    public static function doSelectStmt(Criteria $criteria, PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(ProductsImagesCategoriesSortPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        if (!$criteria->hasSelectClause()) {
            $criteria = clone $criteria;
            ProductsImagesCategoriesSortPeer::addSelectColumns($criteria);
        }

        // Set the correct dbName
        $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME);

        // BasePeer returns a PDOStatement
        return BasePeer::doSelect($criteria, $con);
    }
    /**
     * Adds an object to the instance pool.
     *
     * Propel keeps cached copies of objects in an instance pool when they are retrieved
     * from the database.  In some cases -- especially when you override doSelect*()
     * methods in your stub classes -- you may need to explicitly add objects
     * to the cache in order to ensure that the same objects are always returned by doSelect*()
     * and retrieveByPK*() calls.
     *
     * @param ProductsImagesCategoriesSort $obj A ProductsImagesCategoriesSort object.
     * @param      string $key (optional) key to use for instance map (for performance boost if key was already calculated externally).
     */
    public static function addInstanceToPool($obj, $key = null)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if ($key === null) {
                $key = serialize(array((string) $obj->getProductsId(), (string) $obj->getCategoriesId(), (string) $obj->getProductsImagesId()));
            } // if key === null
            ProductsImagesCategoriesSortPeer::$instances[$key] = $obj;
        }
    }

    /**
     * Removes an object from the instance pool.
     *
     * Propel keeps cached copies of objects in an instance pool when they are retrieved
     * from the database.  In some cases -- especially when you override doDelete
     * methods in your stub classes -- you may need to explicitly remove objects
     * from the cache in order to prevent returning objects that no longer exist.
     *
     * @param      mixed $value A ProductsImagesCategoriesSort object or a primary key value.
     *
     * @return void
     * @throws PropelException - if the value is invalid.
     */
    public static function removeInstanceFromPool($value)
    {
        if (Propel::isInstancePoolingEnabled() && $value !== null) {
            if (is_object($value) && $value instanceof ProductsImagesCategoriesSort) {
                $key = serialize(array((string) $value->getProductsId(), (string) $value->getCategoriesId(), (string) $value->getProductsImagesId()));
            } elseif (is_array($value) && count($value) === 3) {
                // assume we've been passed a primary key
                $key = serialize(array((string) $value[0], (string) $value[1], (string) $value[2]));
            } else {
                $e = new PropelException("Invalid value passed to removeInstanceFromPool().  Expected primary key or ProductsImagesCategoriesSort object; got " . (is_object($value) ? get_class($value) . ' object.' : var_export($value,true)));
                throw $e;
            }

            unset(ProductsImagesCategoriesSortPeer::$instances[$key]);
        }
    } // removeInstanceFromPool()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param      string $key The key (@see getPrimaryKeyHash()) for this instance.
     * @return ProductsImagesCategoriesSort Found object or null if 1) no instance exists for specified key or 2) instance pooling has been disabled.
     * @see        getPrimaryKeyHash()
     */
    public static function getInstanceFromPool($key)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if (isset(ProductsImagesCategoriesSortPeer::$instances[$key])) {
                return ProductsImagesCategoriesSortPeer::$instances[$key];
            }
        }

        return null; // just to be explicit
    }

    /**
     * Clear the instance pool.
     *
     * @return void
     */
    public static function clearInstancePool($and_clear_all_references = false)
    {
      if ($and_clear_all_references) {
        foreach (ProductsImagesCategoriesSortPeer::$instances as $instance) {
          $instance->clearAllReferences(true);
        }
      }
        ProductsImagesCategoriesSortPeer::$instances = array();
    }

    /**
     * Method to invalidate the instance pool of all tables related to products_images_categories_sort
     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool()
    {
    }

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param      array $row PropelPDO resultset row.
     * @param      int $startcol The 0-based offset for reading from the resultset row.
     * @return string A string version of PK or null if the components of primary key in result array are all null.
     */
    public static function getPrimaryKeyHashFromRow($row, $startcol = 0)
    {
        // If the PK cannot be derived from the row, return null.
        if ($row[$startcol] === null && $row[$startcol + 1] === null && $row[$startcol + 2] === null) {
            return null;
        }

        return serialize(array((string) $row[$startcol], (string) $row[$startcol + 1], (string) $row[$startcol + 2]));
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param      array $row PropelPDO resultset row.
     * @param      int $startcol The 0-based offset for reading from the resultset row.
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $startcol = 0)
    {

        return array((int) $row[$startcol], (int) $row[$startcol + 1], (int) $row[$startcol + 2]);
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function populateObjects(PDOStatement $stmt)
    {
        $results = array();

        // set the class once to avoid overhead in the loop
        $cls = ProductsImagesCategoriesSortPeer::getOMClass();
        // populate the object(s)
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key = ProductsImagesCategoriesSortPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj = ProductsImagesCategoriesSortPeer::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                ProductsImagesCategoriesSortPeer::addInstanceToPool($obj, $key);
            } // if key exists
        }
        $stmt->closeCursor();

        return $results;
    }
    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param      array $row PropelPDO resultset row.
     * @param      int $startcol The 0-based offset for reading from the resultset row.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     * @return array (ProductsImagesCategoriesSort object, last column rank)
     */
    public static function populateObject($row, $startcol = 0)
    {
        $key = ProductsImagesCategoriesSortPeer::getPrimaryKeyHashFromRow($row, $startcol);
        if (null !== ($obj = ProductsImagesCategoriesSortPeer::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $startcol, true); // rehydrate
            $col = $startcol + ProductsImagesCategoriesSortPeer::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = ProductsImagesCategoriesSortPeer::OM_CLASS;
            $obj = new $cls();
            $col = $obj->hydrate($row, $startcol);
            ProductsImagesCategoriesSortPeer::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }


    /**
     * Returns the number of rows matching criteria, joining the related Products table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinProducts(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(ProductsImagesCategoriesSortPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            ProductsImagesCategoriesSortPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(ProductsImagesCategoriesSortPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::PRODUCTS_ID, ProductsPeer::ID, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related ProductsImages table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinProductsImages(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(ProductsImagesCategoriesSortPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            ProductsImagesCategoriesSortPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(ProductsImagesCategoriesSortPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::PRODUCTS_IMAGES_ID, ProductsImagesPeer::ID, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related Categories table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinCategories(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(ProductsImagesCategoriesSortPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            ProductsImagesCategoriesSortPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(ProductsImagesCategoriesSortPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::CATEGORIES_ID, CategoriesPeer::ID, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Selects a collection of ProductsImagesCategoriesSort objects pre-filled with their Products objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of ProductsImagesCategoriesSort objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinProducts(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME);
        }

        ProductsImagesCategoriesSortPeer::addSelectColumns($criteria);
        $startcol = ProductsImagesCategoriesSortPeer::NUM_HYDRATE_COLUMNS;
        ProductsPeer::addSelectColumns($criteria);

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::PRODUCTS_ID, ProductsPeer::ID, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = ProductsImagesCategoriesSortPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = ProductsImagesCategoriesSortPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = ProductsImagesCategoriesSortPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                ProductsImagesCategoriesSortPeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = ProductsPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = ProductsPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = ProductsPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    ProductsPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (ProductsImagesCategoriesSort) to $obj2 (Products)
                $obj2->addProductsImagesCategoriesSort($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of ProductsImagesCategoriesSort objects pre-filled with their ProductsImages objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of ProductsImagesCategoriesSort objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinProductsImages(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME);
        }

        ProductsImagesCategoriesSortPeer::addSelectColumns($criteria);
        $startcol = ProductsImagesCategoriesSortPeer::NUM_HYDRATE_COLUMNS;
        ProductsImagesPeer::addSelectColumns($criteria);

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::PRODUCTS_IMAGES_ID, ProductsImagesPeer::ID, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = ProductsImagesCategoriesSortPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = ProductsImagesCategoriesSortPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = ProductsImagesCategoriesSortPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                ProductsImagesCategoriesSortPeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = ProductsImagesPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = ProductsImagesPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = ProductsImagesPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    ProductsImagesPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (ProductsImagesCategoriesSort) to $obj2 (ProductsImages)
                $obj2->addProductsImagesCategoriesSort($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of ProductsImagesCategoriesSort objects pre-filled with their Categories objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of ProductsImagesCategoriesSort objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinCategories(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME);
        }

        ProductsImagesCategoriesSortPeer::addSelectColumns($criteria);
        $startcol = ProductsImagesCategoriesSortPeer::NUM_HYDRATE_COLUMNS;
        CategoriesPeer::addSelectColumns($criteria);

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::CATEGORIES_ID, CategoriesPeer::ID, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = ProductsImagesCategoriesSortPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = ProductsImagesCategoriesSortPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = ProductsImagesCategoriesSortPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                ProductsImagesCategoriesSortPeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = CategoriesPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = CategoriesPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = CategoriesPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    CategoriesPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (ProductsImagesCategoriesSort) to $obj2 (Categories)
                $obj2->addProductsImagesCategoriesSort($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Returns the number of rows matching criteria, joining all related tables
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAll(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(ProductsImagesCategoriesSortPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            ProductsImagesCategoriesSortPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(ProductsImagesCategoriesSortPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::PRODUCTS_ID, ProductsPeer::ID, $join_behavior);

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::PRODUCTS_IMAGES_ID, ProductsImagesPeer::ID, $join_behavior);

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::CATEGORIES_ID, CategoriesPeer::ID, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }

    /**
     * Selects a collection of ProductsImagesCategoriesSort objects pre-filled with all related objects.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of ProductsImagesCategoriesSort objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAll(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME);
        }

        ProductsImagesCategoriesSortPeer::addSelectColumns($criteria);
        $startcol2 = ProductsImagesCategoriesSortPeer::NUM_HYDRATE_COLUMNS;

        ProductsPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + ProductsPeer::NUM_HYDRATE_COLUMNS;

        ProductsImagesPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + ProductsImagesPeer::NUM_HYDRATE_COLUMNS;

        CategoriesPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + CategoriesPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::PRODUCTS_ID, ProductsPeer::ID, $join_behavior);

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::PRODUCTS_IMAGES_ID, ProductsImagesPeer::ID, $join_behavior);

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::CATEGORIES_ID, CategoriesPeer::ID, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = ProductsImagesCategoriesSortPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = ProductsImagesCategoriesSortPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = ProductsImagesCategoriesSortPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                ProductsImagesCategoriesSortPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

            // Add objects for joined Products rows

            $key2 = ProductsPeer::getPrimaryKeyHashFromRow($row, $startcol2);
            if ($key2 !== null) {
                $obj2 = ProductsPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = ProductsPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    ProductsPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 loaded

                // Add the $obj1 (ProductsImagesCategoriesSort) to the collection in $obj2 (Products)
                $obj2->addProductsImagesCategoriesSort($obj1);
            } // if joined row not null

            // Add objects for joined ProductsImages rows

            $key3 = ProductsImagesPeer::getPrimaryKeyHashFromRow($row, $startcol3);
            if ($key3 !== null) {
                $obj3 = ProductsImagesPeer::getInstanceFromPool($key3);
                if (!$obj3) {

                    $cls = ProductsImagesPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    ProductsImagesPeer::addInstanceToPool($obj3, $key3);
                } // if obj3 loaded

                // Add the $obj1 (ProductsImagesCategoriesSort) to the collection in $obj3 (ProductsImages)
                $obj3->addProductsImagesCategoriesSort($obj1);
            } // if joined row not null

            // Add objects for joined Categories rows

            $key4 = CategoriesPeer::getPrimaryKeyHashFromRow($row, $startcol4);
            if ($key4 !== null) {
                $obj4 = CategoriesPeer::getInstanceFromPool($key4);
                if (!$obj4) {

                    $cls = CategoriesPeer::getOMClass();

                    $obj4 = new $cls();
                    $obj4->hydrate($row, $startcol4);
                    CategoriesPeer::addInstanceToPool($obj4, $key4);
                } // if obj4 loaded

                // Add the $obj1 (ProductsImagesCategoriesSort) to the collection in $obj4 (Categories)
                $obj4->addProductsImagesCategoriesSort($obj1);
            } // if joined row not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Returns the number of rows matching criteria, joining the related Products table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptProducts(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(ProductsImagesCategoriesSortPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            ProductsImagesCategoriesSortPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(ProductsImagesCategoriesSortPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::PRODUCTS_IMAGES_ID, ProductsImagesPeer::ID, $join_behavior);

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::CATEGORIES_ID, CategoriesPeer::ID, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related ProductsImages table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptProductsImages(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(ProductsImagesCategoriesSortPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            ProductsImagesCategoriesSortPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(ProductsImagesCategoriesSortPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::PRODUCTS_ID, ProductsPeer::ID, $join_behavior);

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::CATEGORIES_ID, CategoriesPeer::ID, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related Categories table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptCategories(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(ProductsImagesCategoriesSortPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            ProductsImagesCategoriesSortPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(ProductsImagesCategoriesSortPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::PRODUCTS_ID, ProductsPeer::ID, $join_behavior);

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::PRODUCTS_IMAGES_ID, ProductsImagesPeer::ID, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Selects a collection of ProductsImagesCategoriesSort objects pre-filled with all related objects except Products.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of ProductsImagesCategoriesSort objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptProducts(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME);
        }

        ProductsImagesCategoriesSortPeer::addSelectColumns($criteria);
        $startcol2 = ProductsImagesCategoriesSortPeer::NUM_HYDRATE_COLUMNS;

        ProductsImagesPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + ProductsImagesPeer::NUM_HYDRATE_COLUMNS;

        CategoriesPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + CategoriesPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::PRODUCTS_IMAGES_ID, ProductsImagesPeer::ID, $join_behavior);

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::CATEGORIES_ID, CategoriesPeer::ID, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = ProductsImagesCategoriesSortPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = ProductsImagesCategoriesSortPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = ProductsImagesCategoriesSortPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                ProductsImagesCategoriesSortPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined ProductsImages rows

                $key2 = ProductsImagesPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = ProductsImagesPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = ProductsImagesPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    ProductsImagesPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (ProductsImagesCategoriesSort) to the collection in $obj2 (ProductsImages)
                $obj2->addProductsImagesCategoriesSort($obj1);

            } // if joined row is not null

                // Add objects for joined Categories rows

                $key3 = CategoriesPeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = CategoriesPeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = CategoriesPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    CategoriesPeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (ProductsImagesCategoriesSort) to the collection in $obj3 (Categories)
                $obj3->addProductsImagesCategoriesSort($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of ProductsImagesCategoriesSort objects pre-filled with all related objects except ProductsImages.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of ProductsImagesCategoriesSort objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptProductsImages(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME);
        }

        ProductsImagesCategoriesSortPeer::addSelectColumns($criteria);
        $startcol2 = ProductsImagesCategoriesSortPeer::NUM_HYDRATE_COLUMNS;

        ProductsPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + ProductsPeer::NUM_HYDRATE_COLUMNS;

        CategoriesPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + CategoriesPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::PRODUCTS_ID, ProductsPeer::ID, $join_behavior);

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::CATEGORIES_ID, CategoriesPeer::ID, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = ProductsImagesCategoriesSortPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = ProductsImagesCategoriesSortPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = ProductsImagesCategoriesSortPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                ProductsImagesCategoriesSortPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined Products rows

                $key2 = ProductsPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = ProductsPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = ProductsPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    ProductsPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (ProductsImagesCategoriesSort) to the collection in $obj2 (Products)
                $obj2->addProductsImagesCategoriesSort($obj1);

            } // if joined row is not null

                // Add objects for joined Categories rows

                $key3 = CategoriesPeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = CategoriesPeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = CategoriesPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    CategoriesPeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (ProductsImagesCategoriesSort) to the collection in $obj3 (Categories)
                $obj3->addProductsImagesCategoriesSort($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of ProductsImagesCategoriesSort objects pre-filled with all related objects except Categories.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of ProductsImagesCategoriesSort objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptCategories(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME);
        }

        ProductsImagesCategoriesSortPeer::addSelectColumns($criteria);
        $startcol2 = ProductsImagesCategoriesSortPeer::NUM_HYDRATE_COLUMNS;

        ProductsPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + ProductsPeer::NUM_HYDRATE_COLUMNS;

        ProductsImagesPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + ProductsImagesPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::PRODUCTS_ID, ProductsPeer::ID, $join_behavior);

        $criteria->addJoin(ProductsImagesCategoriesSortPeer::PRODUCTS_IMAGES_ID, ProductsImagesPeer::ID, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = ProductsImagesCategoriesSortPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = ProductsImagesCategoriesSortPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = ProductsImagesCategoriesSortPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                ProductsImagesCategoriesSortPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined Products rows

                $key2 = ProductsPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = ProductsPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = ProductsPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    ProductsPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (ProductsImagesCategoriesSort) to the collection in $obj2 (Products)
                $obj2->addProductsImagesCategoriesSort($obj1);

            } // if joined row is not null

                // Add objects for joined ProductsImages rows

                $key3 = ProductsImagesPeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = ProductsImagesPeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = ProductsImagesPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    ProductsImagesPeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (ProductsImagesCategoriesSort) to the collection in $obj3 (ProductsImages)
                $obj3->addProductsImagesCategoriesSort($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }

    /**
     * Returns the TableMap related to this peer.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getDatabaseMap(ProductsImagesCategoriesSortPeer::DATABASE_NAME)->getTable(ProductsImagesCategoriesSortPeer::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this peer class.
     */
    public static function buildTableMap()
    {
      $dbMap = Propel::getDatabaseMap(BaseProductsImagesCategoriesSortPeer::DATABASE_NAME);
      if (!$dbMap->hasTable(BaseProductsImagesCategoriesSortPeer::TABLE_NAME)) {
        $dbMap->addTableObject(new \Hanzo\Model\map\ProductsImagesCategoriesSortTableMap());
      }
    }

    /**
     * The class that the Peer will make instances of.
     *
     *
     * @return string ClassName
     */
    public static function getOMClass($row = 0, $colnum = 0)
    {
        return ProductsImagesCategoriesSortPeer::OM_CLASS;
    }

    /**
     * Performs an INSERT on the database, given a ProductsImagesCategoriesSort or Criteria object.
     *
     * @param      mixed $values Criteria or ProductsImagesCategoriesSort object containing data that is used to create the INSERT statement.
     * @param      PropelPDO $con the PropelPDO connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doInsert($values, PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(ProductsImagesCategoriesSortPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity
        } else {
            $criteria = $values->buildCriteria(); // build Criteria from ProductsImagesCategoriesSort object
        }


        // Set the correct dbName
        $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME);

        try {
            // use transaction because $criteria could contain info
            // for more than one table (I guess, conceivably)
            $con->beginTransaction();
            $pk = BasePeer::doInsert($criteria, $con);
            $con->commit();
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }

        return $pk;
    }

    /**
     * Performs an UPDATE on the database, given a ProductsImagesCategoriesSort or Criteria object.
     *
     * @param      mixed $values Criteria or ProductsImagesCategoriesSort object containing data that is used to create the UPDATE statement.
     * @param      PropelPDO $con The connection to use (specify PropelPDO connection object to exert more control over transactions).
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doUpdate($values, PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(ProductsImagesCategoriesSortPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $selectCriteria = new Criteria(ProductsImagesCategoriesSortPeer::DATABASE_NAME);

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity

            $comparison = $criteria->getComparison(ProductsImagesCategoriesSortPeer::PRODUCTS_ID);
            $value = $criteria->remove(ProductsImagesCategoriesSortPeer::PRODUCTS_ID);
            if ($value) {
                $selectCriteria->add(ProductsImagesCategoriesSortPeer::PRODUCTS_ID, $value, $comparison);
            } else {
                $selectCriteria->setPrimaryTableName(ProductsImagesCategoriesSortPeer::TABLE_NAME);
            }

            $comparison = $criteria->getComparison(ProductsImagesCategoriesSortPeer::CATEGORIES_ID);
            $value = $criteria->remove(ProductsImagesCategoriesSortPeer::CATEGORIES_ID);
            if ($value) {
                $selectCriteria->add(ProductsImagesCategoriesSortPeer::CATEGORIES_ID, $value, $comparison);
            } else {
                $selectCriteria->setPrimaryTableName(ProductsImagesCategoriesSortPeer::TABLE_NAME);
            }

            $comparison = $criteria->getComparison(ProductsImagesCategoriesSortPeer::PRODUCTS_IMAGES_ID);
            $value = $criteria->remove(ProductsImagesCategoriesSortPeer::PRODUCTS_IMAGES_ID);
            if ($value) {
                $selectCriteria->add(ProductsImagesCategoriesSortPeer::PRODUCTS_IMAGES_ID, $value, $comparison);
            } else {
                $selectCriteria->setPrimaryTableName(ProductsImagesCategoriesSortPeer::TABLE_NAME);
            }

        } else { // $values is ProductsImagesCategoriesSort object
            $criteria = $values->buildCriteria(); // gets full criteria
            $selectCriteria = $values->buildPkeyCriteria(); // gets criteria w/ primary key(s)
        }

        // set the correct dbName
        $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME);

        return BasePeer::doUpdate($selectCriteria, $criteria, $con);
    }

    /**
     * Deletes all rows from the products_images_categories_sort table.
     *
     * @param      PropelPDO $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException
     */
    public static function doDeleteAll(PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(ProductsImagesCategoriesSortPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += BasePeer::doDeleteAll(ProductsImagesCategoriesSortPeer::TABLE_NAME, $con, ProductsImagesCategoriesSortPeer::DATABASE_NAME);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ProductsImagesCategoriesSortPeer::clearInstancePool();
            ProductsImagesCategoriesSortPeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs a DELETE on the database, given a ProductsImagesCategoriesSort or Criteria object OR a primary key value.
     *
     * @param      mixed $values Criteria or ProductsImagesCategoriesSort object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param      PropelPDO $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *				if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, PropelPDO $con = null)
     {
        if ($con === null) {
            $con = Propel::getConnection(ProductsImagesCategoriesSortPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            // invalidate the cache for all objects of this type, since we have no
            // way of knowing (without running a query) what objects should be invalidated
            // from the cache based on this Criteria.
            ProductsImagesCategoriesSortPeer::clearInstancePool();
            // rename for clarity
            $criteria = clone $values;
        } elseif ($values instanceof ProductsImagesCategoriesSort) { // it's a model object
            // invalidate the cache for this single object
            ProductsImagesCategoriesSortPeer::removeInstanceFromPool($values);
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(ProductsImagesCategoriesSortPeer::DATABASE_NAME);
            // primary key is composite; we therefore, expect
            // the primary key passed to be an array of pkey values
            if (count($values) == count($values, COUNT_RECURSIVE)) {
                // array is not multi-dimensional
                $values = array($values);
            }
            foreach ($values as $value) {
                $criterion = $criteria->getNewCriterion(ProductsImagesCategoriesSortPeer::PRODUCTS_ID, $value[0]);
                $criterion->addAnd($criteria->getNewCriterion(ProductsImagesCategoriesSortPeer::CATEGORIES_ID, $value[1]));
                $criterion->addAnd($criteria->getNewCriterion(ProductsImagesCategoriesSortPeer::PRODUCTS_IMAGES_ID, $value[2]));
                $criteria->addOr($criterion);
                // we can invalidate the cache for this single PK
                ProductsImagesCategoriesSortPeer::removeInstanceFromPool($value);
            }
        }

        // Set the correct dbName
        $criteria->setDbName(ProductsImagesCategoriesSortPeer::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();

            $affectedRows += BasePeer::doDelete($criteria, $con);
            ProductsImagesCategoriesSortPeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Validates all modified columns of given ProductsImagesCategoriesSort object.
     * If parameter $columns is either a single column name or an array of column names
     * than only those columns are validated.
     *
     * NOTICE: This does not apply to primary or foreign keys for now.
     *
     * @param ProductsImagesCategoriesSort $obj The object to validate.
     * @param      mixed $cols Column name or array of column names.
     *
     * @return mixed TRUE if all columns are valid or the error message of the first invalid column.
     */
    public static function doValidate($obj, $cols = null)
    {
        $columns = array();

        if ($cols) {
            $dbMap = Propel::getDatabaseMap(ProductsImagesCategoriesSortPeer::DATABASE_NAME);
            $tableMap = $dbMap->getTable(ProductsImagesCategoriesSortPeer::TABLE_NAME);

            if (! is_array($cols)) {
                $cols = array($cols);
            }

            foreach ($cols as $colName) {
                if ($tableMap->hasColumn($colName)) {
                    $get = 'get' . $tableMap->getColumn($colName)->getPhpName();
                    $columns[$colName] = $obj->$get();
                }
            }
        } else {

        }

        return BasePeer::doValidate(ProductsImagesCategoriesSortPeer::DATABASE_NAME, ProductsImagesCategoriesSortPeer::TABLE_NAME, $columns);
    }

    /**
     * Retrieve object using using composite pkey values.
     * @param   int $products_id
     * @param   int $categories_id
     * @param   int $products_images_id
     * @param      PropelPDO $con
     * @return ProductsImagesCategoriesSort
     */
    public static function retrieveByPK($products_id, $categories_id, $products_images_id, PropelPDO $con = null) {
        $_instancePoolKey = serialize(array((string) $products_id, (string) $categories_id, (string) $products_images_id));
         if (null !== ($obj = ProductsImagesCategoriesSortPeer::getInstanceFromPool($_instancePoolKey))) {
             return $obj;
        }

        if ($con === null) {
            $con = Propel::getConnection(ProductsImagesCategoriesSortPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }
        $criteria = new Criteria(ProductsImagesCategoriesSortPeer::DATABASE_NAME);
        $criteria->add(ProductsImagesCategoriesSortPeer::PRODUCTS_ID, $products_id);
        $criteria->add(ProductsImagesCategoriesSortPeer::CATEGORIES_ID, $categories_id);
        $criteria->add(ProductsImagesCategoriesSortPeer::PRODUCTS_IMAGES_ID, $products_images_id);
        $v = ProductsImagesCategoriesSortPeer::doSelect($criteria, $con);

        return !empty($v) ? $v[0] : null;
    }
} // BaseProductsImagesCategoriesSortPeer

// This is the static code needed to register the TableMap for this table with the main Propel class.
//
BaseProductsImagesCategoriesSortPeer::buildTableMap();

EventDispatcherProxy::trigger(array('construct','peer.construct'), new PeerEvent('Hanzo\Model\om\BaseProductsImagesCategoriesSortPeer'));
