<?php

namespace Hanzo\Model\om;

use \BaseObject;
use \BasePeer;
use \Criteria;
use \Exception;
use \PDO;
use \Persistent;
use \Propel;
use \PropelException;
use \PropelPDO;
use Glorpen\Propel\PropelBundle\Dispatcher\EventDispatcherProxy;
use Glorpen\Propel\PropelBundle\Events\ModelEvent;
use Hanzo\Model\ShippingMethods;
use Hanzo\Model\ShippingMethodsPeer;
use Hanzo\Model\ShippingMethodsQuery;

abstract class BaseShippingMethods extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'Hanzo\\Model\\ShippingMethodsPeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        ShippingMethodsPeer
     */
    protected static $peer;

    /**
     * The flag var to prevent infinite loop in deep copy
     * @var       boolean
     */
    protected $startCopy = false;

    /**
     * The value for the id field.
     * @var        int
     */
    protected $id;

    /**
     * The value for the carrier field.
     * @var        string
     */
    protected $carrier;

    /**
     * The value for the method field.
     * @var        string
     */
    protected $method;

    /**
     * The value for the external_id field.
     * @var        string
     */
    protected $external_id;

    /**
     * The value for the calc_engine field.
     * Note: this column has a database default value of: 'flat'
     * @var        string
     */
    protected $calc_engine;

    /**
     * The value for the price field.
     * @var        string
     */
    protected $price;

    /**
     * The value for the fee field.
     * Note: this column has a database default value of: '0.00'
     * @var        string
     */
    protected $fee;

    /**
     * The value for the fee_external_id field.
     * @var        string
     */
    protected $fee_external_id;

    /**
     * The value for the is_active field.
     * Note: this column has a database default value of: true
     * @var        boolean
     */
    protected $is_active;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInSave = false;

    /**
     * Flag to prevent endless validation loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInValidation = false;

    /**
     * Flag to prevent endless clearAllReferences($deep=true) loop, if this object is referenced
     * @var        boolean
     */
    protected $alreadyInClearAllReferencesDeep = false;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see        __construct()
     */
    public function applyDefaultValues()
    {
        $this->calc_engine = 'flat';
        $this->fee = '0.00';
        $this->is_active = true;
    }

    /**
     * Initializes internal state of BaseShippingMethods object.
     * @see        applyDefaults()
     */
    public function __construct()
    {
        parent::__construct();
        $this->applyDefaultValues();
        EventDispatcherProxy::trigger(array('construct','model.construct'), new ModelEvent($this));
    }

    /**
     * Get the [id] column value.
     *
     * @return int
     */
    public function getId()
    {

        return $this->id;
    }

    /**
     * Get the [carrier] column value.
     *
     * @return string
     */
    public function getCarrier()
    {

        return $this->carrier;
    }

    /**
     * Get the [method] column value.
     *
     * @return string
     */
    public function getMethod()
    {

        return $this->method;
    }

    /**
     * Get the [external_id] column value.
     *
     * @return string
     */
    public function getExternalId()
    {

        return $this->external_id;
    }

    /**
     * Get the [calc_engine] column value.
     *
     * @return string
     */
    public function getCalcEngine()
    {

        return $this->calc_engine;
    }

    /**
     * Get the [price] column value.
     *
     * @return string
     */
    public function getPrice()
    {

        return $this->price;
    }

    /**
     * Get the [fee] column value.
     *
     * @return string
     */
    public function getFee()
    {

        return $this->fee;
    }

    /**
     * Get the [fee_external_id] column value.
     *
     * @return string
     */
    public function getFeeExternalId()
    {

        return $this->fee_external_id;
    }

    /**
     * Get the [is_active] column value.
     *
     * @return boolean
     */
    public function getIsActive()
    {

        return $this->is_active;
    }

    /**
     * Set the value of [id] column.
     *
     * @param  int $v new value
     * @return ShippingMethods The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[] = ShippingMethodsPeer::ID;
        }


        return $this;
    } // setId()

    /**
     * Set the value of [carrier] column.
     *
     * @param  string $v new value
     * @return ShippingMethods The current object (for fluent API support)
     */
    public function setCarrier($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->carrier !== $v) {
            $this->carrier = $v;
            $this->modifiedColumns[] = ShippingMethodsPeer::CARRIER;
        }


        return $this;
    } // setCarrier()

    /**
     * Set the value of [method] column.
     *
     * @param  string $v new value
     * @return ShippingMethods The current object (for fluent API support)
     */
    public function setMethod($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->method !== $v) {
            $this->method = $v;
            $this->modifiedColumns[] = ShippingMethodsPeer::METHOD;
        }


        return $this;
    } // setMethod()

    /**
     * Set the value of [external_id] column.
     *
     * @param  string $v new value
     * @return ShippingMethods The current object (for fluent API support)
     */
    public function setExternalId($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->external_id !== $v) {
            $this->external_id = $v;
            $this->modifiedColumns[] = ShippingMethodsPeer::EXTERNAL_ID;
        }


        return $this;
    } // setExternalId()

    /**
     * Set the value of [calc_engine] column.
     *
     * @param  string $v new value
     * @return ShippingMethods The current object (for fluent API support)
     */
    public function setCalcEngine($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->calc_engine !== $v) {
            $this->calc_engine = $v;
            $this->modifiedColumns[] = ShippingMethodsPeer::CALC_ENGINE;
        }


        return $this;
    } // setCalcEngine()

    /**
     * Set the value of [price] column.
     *
     * @param  string $v new value
     * @return ShippingMethods The current object (for fluent API support)
     */
    public function setPrice($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->price !== $v) {
            $this->price = $v;
            $this->modifiedColumns[] = ShippingMethodsPeer::PRICE;
        }


        return $this;
    } // setPrice()

    /**
     * Set the value of [fee] column.
     *
     * @param  string $v new value
     * @return ShippingMethods The current object (for fluent API support)
     */
    public function setFee($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->fee !== $v) {
            $this->fee = $v;
            $this->modifiedColumns[] = ShippingMethodsPeer::FEE;
        }


        return $this;
    } // setFee()

    /**
     * Set the value of [fee_external_id] column.
     *
     * @param  string $v new value
     * @return ShippingMethods The current object (for fluent API support)
     */
    public function setFeeExternalId($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->fee_external_id !== $v) {
            $this->fee_external_id = $v;
            $this->modifiedColumns[] = ShippingMethodsPeer::FEE_EXTERNAL_ID;
        }


        return $this;
    } // setFeeExternalId()

    /**
     * Sets the value of the [is_active] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param boolean|integer|string $v The new value
     * @return ShippingMethods The current object (for fluent API support)
     */
    public function setIsActive($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->is_active !== $v) {
            $this->is_active = $v;
            $this->modifiedColumns[] = ShippingMethodsPeer::IS_ACTIVE;
        }


        return $this;
    } // setIsActive()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
            if ($this->calc_engine !== 'flat') {
                return false;
            }

            if ($this->fee !== '0.00') {
                return false;
            }

            if ($this->is_active !== true) {
                return false;
            }

        // otherwise, everything was equal, so return true
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array $row The row returned by PDOStatement->fetch(PDO::FETCH_NUM)
     * @param int $startcol 0-based offset column which indicates which resultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false)
    {
        try {

            $this->id = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
            $this->carrier = ($row[$startcol + 1] !== null) ? (string) $row[$startcol + 1] : null;
            $this->method = ($row[$startcol + 2] !== null) ? (string) $row[$startcol + 2] : null;
            $this->external_id = ($row[$startcol + 3] !== null) ? (string) $row[$startcol + 3] : null;
            $this->calc_engine = ($row[$startcol + 4] !== null) ? (string) $row[$startcol + 4] : null;
            $this->price = ($row[$startcol + 5] !== null) ? (string) $row[$startcol + 5] : null;
            $this->fee = ($row[$startcol + 6] !== null) ? (string) $row[$startcol + 6] : null;
            $this->fee_external_id = ($row[$startcol + 7] !== null) ? (string) $row[$startcol + 7] : null;
            $this->is_active = ($row[$startcol + 8] !== null) ? (boolean) $row[$startcol + 8] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);

            return $startcol + 9; // 9 = ShippingMethodsPeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating ShippingMethods object", $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {

    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param boolean $deep (optional) Whether to also de-associated any related objects.
     * @param PropelPDO $con (optional) The PropelPDO connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, PropelPDO $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getConnection(ShippingMethodsPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = ShippingMethodsPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true); // rehydrate

        if ($deep) {  // also de-associate any related objects?

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param PropelPDO $con
     * @return void
     * @throws PropelException
     * @throws Exception
     * @see        BaseObject::setDeleted()
     * @see        BaseObject::isDeleted()
     */
    public function delete(PropelPDO $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getConnection(ShippingMethodsPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            EventDispatcherProxy::trigger(array('delete.pre','model.delete.pre'), new ModelEvent($this));
            $deleteQuery = ShippingMethodsQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                // event behavior
                EventDispatcherProxy::trigger(array('delete.post', 'model.delete.post'), new ModelEvent($this));
                $con->commit();
                $this->setDeleted(true);
            } else {
                $con->commit();
            }
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param PropelPDO $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @throws Exception
     * @see        doSave()
     */
    public function save(PropelPDO $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($con === null) {
            $con = Propel::getConnection(ShippingMethodsPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        $isInsert = $this->isNew();
        try {
            $ret = $this->preSave($con);
            // event behavior
            EventDispatcherProxy::trigger('model.save.pre', new ModelEvent($this));
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
                // event behavior
                EventDispatcherProxy::trigger('model.insert.pre', new ModelEvent($this));
            } else {
                $ret = $ret && $this->preUpdate($con);
                // event behavior
                EventDispatcherProxy::trigger(array('update.pre', 'model.update.pre'), new ModelEvent($this));
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                    // event behavior
                    EventDispatcherProxy::trigger('model.insert.post', new ModelEvent($this));
                } else {
                    $this->postUpdate($con);
                    // event behavior
                    EventDispatcherProxy::trigger(array('update.post', 'model.update.post'), new ModelEvent($this));
                }
                $this->postSave($con);
                // event behavior
                EventDispatcherProxy::trigger('model.save.post', new ModelEvent($this));
                ShippingMethodsPeer::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param PropelPDO $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see        save()
     */
    protected function doSave(PropelPDO $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                } else {
                    $this->doUpdate($con);
                }
                $affectedRows += 1;
                $this->resetModified();
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param PropelPDO $con
     *
     * @throws PropelException
     * @see        doSave()
     */
    protected function doInsert(PropelPDO $con)
    {
        $modifiedColumns = array();
        $index = 0;

        $this->modifiedColumns[] = ShippingMethodsPeer::ID;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . ShippingMethodsPeer::ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(ShippingMethodsPeer::ID)) {
            $modifiedColumns[':p' . $index++]  = '`id`';
        }
        if ($this->isColumnModified(ShippingMethodsPeer::CARRIER)) {
            $modifiedColumns[':p' . $index++]  = '`carrier`';
        }
        if ($this->isColumnModified(ShippingMethodsPeer::METHOD)) {
            $modifiedColumns[':p' . $index++]  = '`method`';
        }
        if ($this->isColumnModified(ShippingMethodsPeer::EXTERNAL_ID)) {
            $modifiedColumns[':p' . $index++]  = '`external_id`';
        }
        if ($this->isColumnModified(ShippingMethodsPeer::CALC_ENGINE)) {
            $modifiedColumns[':p' . $index++]  = '`calc_engine`';
        }
        if ($this->isColumnModified(ShippingMethodsPeer::PRICE)) {
            $modifiedColumns[':p' . $index++]  = '`price`';
        }
        if ($this->isColumnModified(ShippingMethodsPeer::FEE)) {
            $modifiedColumns[':p' . $index++]  = '`fee`';
        }
        if ($this->isColumnModified(ShippingMethodsPeer::FEE_EXTERNAL_ID)) {
            $modifiedColumns[':p' . $index++]  = '`fee_external_id`';
        }
        if ($this->isColumnModified(ShippingMethodsPeer::IS_ACTIVE)) {
            $modifiedColumns[':p' . $index++]  = '`is_active`';
        }

        $sql = sprintf(
            'INSERT INTO `shipping_methods` (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case '`id`':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);
                        break;
                    case '`carrier`':
                        $stmt->bindValue($identifier, $this->carrier, PDO::PARAM_STR);
                        break;
                    case '`method`':
                        $stmt->bindValue($identifier, $this->method, PDO::PARAM_STR);
                        break;
                    case '`external_id`':
                        $stmt->bindValue($identifier, $this->external_id, PDO::PARAM_STR);
                        break;
                    case '`calc_engine`':
                        $stmt->bindValue($identifier, $this->calc_engine, PDO::PARAM_STR);
                        break;
                    case '`price`':
                        $stmt->bindValue($identifier, $this->price, PDO::PARAM_STR);
                        break;
                    case '`fee`':
                        $stmt->bindValue($identifier, $this->fee, PDO::PARAM_STR);
                        break;
                    case '`fee_external_id`':
                        $stmt->bindValue($identifier, $this->fee_external_id, PDO::PARAM_STR);
                        break;
                    case '`is_active`':
                        $stmt->bindValue($identifier, (int) $this->is_active, PDO::PARAM_INT);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', $e);
        }
        $this->setId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param PropelPDO $con
     *
     * @see        doSave()
     */
    protected function doUpdate(PropelPDO $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();
        BasePeer::doUpdate($selectCriteria, $valuesCriteria, $con);
    }

    /**
     * Array of ValidationFailed objects.
     * @var        array ValidationFailed[]
     */
    protected $validationFailures = array();

    /**
     * Gets any ValidationFailed objects that resulted from last call to validate().
     *
     *
     * @return array ValidationFailed[]
     * @see        validate()
     */
    public function getValidationFailures()
    {
        return $this->validationFailures;
    }

    /**
     * Validates the objects modified field values and all objects related to this table.
     *
     * If $columns is either a column name or an array of column names
     * only those columns are validated.
     *
     * @param mixed $columns Column name or an array of column names.
     * @return boolean Whether all columns pass validation.
     * @see        doValidate()
     * @see        getValidationFailures()
     */
    public function validate($columns = null)
    {
        $res = $this->doValidate($columns);
        if ($res === true) {
            $this->validationFailures = array();

            return true;
        }

        $this->validationFailures = $res;

        return false;
    }

    /**
     * This function performs the validation work for complex object models.
     *
     * In addition to checking the current object, all related objects will
     * also be validated.  If all pass then <code>true</code> is returned; otherwise
     * an aggregated array of ValidationFailed objects will be returned.
     *
     * @param array $columns Array of column names to validate.
     * @return mixed <code>true</code> if all validations pass; array of <code>ValidationFailed</code> objects otherwise.
     */
    protected function doValidate($columns = null)
    {
        if (!$this->alreadyInValidation) {
            $this->alreadyInValidation = true;
            $retval = null;

            $failureMap = array();


            if (($retval = ShippingMethodsPeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
            }



            $this->alreadyInValidation = false;
        }

        return (!empty($failureMap) ? $failureMap : true);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param string $name name
     * @param string $type The type of fieldname the $name is of:
     *               one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *               BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     *               Defaults to BasePeer::TYPE_PHPNAME
     * @return mixed Value of field.
     */
    public function getByName($name, $type = BasePeer::TYPE_PHPNAME)
    {
        $pos = ShippingMethodsPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getCarrier();
                break;
            case 2:
                return $this->getMethod();
                break;
            case 3:
                return $this->getExternalId();
                break;
            case 4:
                return $this->getCalcEngine();
                break;
            case 5:
                return $this->getPrice();
                break;
            case 6:
                return $this->getFee();
                break;
            case 7:
                return $this->getFeeExternalId();
                break;
            case 8:
                return $this->getIsActive();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME,
     *                    BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     *                    Defaults to BasePeer::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to true.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = BasePeer::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array())
    {
        if (isset($alreadyDumpedObjects['ShippingMethods'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['ShippingMethods'][$this->getPrimaryKey()] = true;
        $keys = ShippingMethodsPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getCarrier(),
            $keys[2] => $this->getMethod(),
            $keys[3] => $this->getExternalId(),
            $keys[4] => $this->getCalcEngine(),
            $keys[5] => $this->getPrice(),
            $keys[6] => $this->getFee(),
            $keys[7] => $this->getFeeExternalId(),
            $keys[8] => $this->getIsActive(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }


        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param string $name peer name
     * @param mixed $value field value
     * @param string $type The type of fieldname the $name is of:
     *                     one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *                     BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     *                     Defaults to BasePeer::TYPE_PHPNAME
     * @return void
     */
    public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
    {
        $pos = ShippingMethodsPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

        $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param int $pos position in xml schema
     * @param mixed $value field value
     * @return void
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setCarrier($value);
                break;
            case 2:
                $this->setMethod($value);
                break;
            case 3:
                $this->setExternalId($value);
                break;
            case 4:
                $this->setCalcEngine($value);
                break;
            case 5:
                $this->setPrice($value);
                break;
            case 6:
                $this->setFee($value);
                break;
            case 7:
                $this->setFeeExternalId($value);
                break;
            case 8:
                $this->setIsActive($value);
                break;
        } // switch()
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME,
     * BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     * The default key type is the column's BasePeer::TYPE_PHPNAME
     *
     * @param array  $arr     An array to populate the object from.
     * @param string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
    {
        $keys = ShippingMethodsPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setCarrier($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setMethod($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setExternalId($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setCalcEngine($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setPrice($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setFee($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setFeeExternalId($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setIsActive($arr[$keys[8]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(ShippingMethodsPeer::DATABASE_NAME);

        if ($this->isColumnModified(ShippingMethodsPeer::ID)) $criteria->add(ShippingMethodsPeer::ID, $this->id);
        if ($this->isColumnModified(ShippingMethodsPeer::CARRIER)) $criteria->add(ShippingMethodsPeer::CARRIER, $this->carrier);
        if ($this->isColumnModified(ShippingMethodsPeer::METHOD)) $criteria->add(ShippingMethodsPeer::METHOD, $this->method);
        if ($this->isColumnModified(ShippingMethodsPeer::EXTERNAL_ID)) $criteria->add(ShippingMethodsPeer::EXTERNAL_ID, $this->external_id);
        if ($this->isColumnModified(ShippingMethodsPeer::CALC_ENGINE)) $criteria->add(ShippingMethodsPeer::CALC_ENGINE, $this->calc_engine);
        if ($this->isColumnModified(ShippingMethodsPeer::PRICE)) $criteria->add(ShippingMethodsPeer::PRICE, $this->price);
        if ($this->isColumnModified(ShippingMethodsPeer::FEE)) $criteria->add(ShippingMethodsPeer::FEE, $this->fee);
        if ($this->isColumnModified(ShippingMethodsPeer::FEE_EXTERNAL_ID)) $criteria->add(ShippingMethodsPeer::FEE_EXTERNAL_ID, $this->fee_external_id);
        if ($this->isColumnModified(ShippingMethodsPeer::IS_ACTIVE)) $criteria->add(ShippingMethodsPeer::IS_ACTIVE, $this->is_active);

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = new Criteria(ShippingMethodsPeer::DATABASE_NAME);
        $criteria->add(ShippingMethodsPeer::ID, $this->id);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param  int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of ShippingMethods (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setCarrier($this->getCarrier());
        $copyObj->setMethod($this->getMethod());
        $copyObj->setExternalId($this->getExternalId());
        $copyObj->setCalcEngine($this->getCalcEngine());
        $copyObj->setPrice($this->getPrice());
        $copyObj->setFee($this->getFee());
        $copyObj->setFeeExternalId($this->getFeeExternalId());
        $copyObj->setIsActive($this->getIsActive());
        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return ShippingMethods Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }

    /**
     * Returns a peer instance associated with this om.
     *
     * Since Peer classes are not to have any instance attributes, this method returns the
     * same instance for all member of this class. The method could therefore
     * be static, but this would prevent one from overriding the behavior.
     *
     * @return ShippingMethodsPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new ShippingMethodsPeer();
        }

        return self::$peer;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
        $this->carrier = null;
        $this->method = null;
        $this->external_id = null;
        $this->calc_engine = null;
        $this->price = null;
        $this->fee = null;
        $this->fee_external_id = null;
        $this->is_active = null;
        $this->alreadyInSave = false;
        $this->alreadyInValidation = false;
        $this->alreadyInClearAllReferencesDeep = false;
        $this->clearAllReferences();
        $this->applyDefaultValues();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references to other model objects or collections of model objects.
     *
     * This method is a user-space workaround for PHP's inability to garbage collect
     * objects with circular references (even in PHP 5.3). This is currently necessary
     * when using Propel in certain daemon or large-volume/high-memory operations.
     *
     * @param boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep && !$this->alreadyInClearAllReferencesDeep) {
            $this->alreadyInClearAllReferencesDeep = true;

            $this->alreadyInClearAllReferencesDeep = false;
        } // if ($deep)

    }

    /**
     * return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(ShippingMethodsPeer::DEFAULT_STRING_FORMAT);
    }

    /**
     * return true is the object is in saving state
     *
     * @return boolean
     */
    public function isAlreadyInSave()
    {
        return $this->alreadyInSave;
    }

    // event behavior
    public function preCommit(\PropelPDO $con = null){}
    public function preCommitSave(\PropelPDO $con = null){}
    public function preCommitDelete(\PropelPDO $con = null){}
    public function preCommitUpdate(\PropelPDO $con = null){}
    public function preCommitInsert(\PropelPDO $con = null){}
    public function preRollback(\PropelPDO $con = null){}
    public function preRollbackSave(\PropelPDO $con = null){}
    public function preRollbackDelete(\PropelPDO $con = null){}
    public function preRollbackUpdate(\PropelPDO $con = null){}
    public function preRollbackInsert(\PropelPDO $con = null){}

}
