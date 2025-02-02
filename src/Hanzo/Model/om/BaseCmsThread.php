<?php

namespace Hanzo\Model\om;

use \BaseObject;
use \BasePeer;
use \Criteria;
use \Exception;
use \PDO;
use \Persistent;
use \Propel;
use \PropelCollection;
use \PropelException;
use \PropelObjectCollection;
use \PropelPDO;
use Glorpen\Propel\PropelBundle\Dispatcher\EventDispatcherProxy;
use Glorpen\Propel\PropelBundle\Events\ModelEvent;
use Hanzo\Model\Cms;
use Hanzo\Model\CmsQuery;
use Hanzo\Model\CmsThread;
use Hanzo\Model\CmsThreadI18n;
use Hanzo\Model\CmsThreadI18nQuery;
use Hanzo\Model\CmsThreadPeer;
use Hanzo\Model\CmsThreadQuery;

abstract class BaseCmsThread extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'Hanzo\\Model\\CmsThreadPeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        CmsThreadPeer
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
     * The value for the is_active field.
     * Note: this column has a database default value of: true
     * @var        boolean
     */
    protected $is_active;

    /**
     * @var        PropelObjectCollection|Cms[] Collection to store aggregation of Cms objects.
     */
    protected $collCmss;
    protected $collCmssPartial;

    /**
     * @var        PropelObjectCollection|CmsThreadI18n[] Collection to store aggregation of CmsThreadI18n objects.
     */
    protected $collCmsThreadI18ns;
    protected $collCmsThreadI18nsPartial;

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

    // i18n behavior

    /**
     * Current locale
     * @var        string
     */
    protected $currentLocale = 'da_DK';

    /**
     * Current translation objects
     * @var        array[CmsThreadI18n]
     */
    protected $currentTranslations;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $cmssScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $cmsThreadI18nsScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see        __construct()
     */
    public function applyDefaultValues()
    {
        $this->is_active = true;
    }

    /**
     * Initializes internal state of BaseCmsThread object.
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
     * @return CmsThread The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[] = CmsThreadPeer::ID;
        }


        return $this;
    } // setId()

    /**
     * Sets the value of the [is_active] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param boolean|integer|string $v The new value
     * @return CmsThread The current object (for fluent API support)
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
            $this->modifiedColumns[] = CmsThreadPeer::IS_ACTIVE;
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
            $this->is_active = ($row[$startcol + 1] !== null) ? (boolean) $row[$startcol + 1] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);

            return $startcol + 2; // 2 = CmsThreadPeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating CmsThread object", $e);
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
            $con = Propel::getConnection(CmsThreadPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = CmsThreadPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->collCmss = null;

            $this->collCmsThreadI18ns = null;

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
            $con = Propel::getConnection(CmsThreadPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            EventDispatcherProxy::trigger(array('delete.pre','model.delete.pre'), new ModelEvent($this));
            $deleteQuery = CmsThreadQuery::create()
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
            $con = Propel::getConnection(CmsThreadPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
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
                CmsThreadPeer::addInstanceToPool($this);
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

            if ($this->cmssScheduledForDeletion !== null) {
                if (!$this->cmssScheduledForDeletion->isEmpty()) {
                    CmsQuery::create()
                        ->filterByPrimaryKeys($this->cmssScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->cmssScheduledForDeletion = null;
                }
            }

            if ($this->collCmss !== null) {
                foreach ($this->collCmss as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->cmsThreadI18nsScheduledForDeletion !== null) {
                if (!$this->cmsThreadI18nsScheduledForDeletion->isEmpty()) {
                    CmsThreadI18nQuery::create()
                        ->filterByPrimaryKeys($this->cmsThreadI18nsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->cmsThreadI18nsScheduledForDeletion = null;
                }
            }

            if ($this->collCmsThreadI18ns !== null) {
                foreach ($this->collCmsThreadI18ns as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
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

        $this->modifiedColumns[] = CmsThreadPeer::ID;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . CmsThreadPeer::ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(CmsThreadPeer::ID)) {
            $modifiedColumns[':p' . $index++]  = '`id`';
        }
        if ($this->isColumnModified(CmsThreadPeer::IS_ACTIVE)) {
            $modifiedColumns[':p' . $index++]  = '`is_active`';
        }

        $sql = sprintf(
            'INSERT INTO `cms_thread` (%s) VALUES (%s)',
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


            if (($retval = CmsThreadPeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
            }


                if ($this->collCmss !== null) {
                    foreach ($this->collCmss as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collCmsThreadI18ns !== null) {
                    foreach ($this->collCmsThreadI18ns as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
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
        $pos = CmsThreadPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = BasePeer::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {
        if (isset($alreadyDumpedObjects['CmsThread'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['CmsThread'][$this->getPrimaryKey()] = true;
        $keys = CmsThreadPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getIsActive(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->collCmss) {
                $result['Cmss'] = $this->collCmss->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collCmsThreadI18ns) {
                $result['CmsThreadI18ns'] = $this->collCmsThreadI18ns->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
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
        $pos = CmsThreadPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

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
        $keys = CmsThreadPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setIsActive($arr[$keys[1]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(CmsThreadPeer::DATABASE_NAME);

        if ($this->isColumnModified(CmsThreadPeer::ID)) $criteria->add(CmsThreadPeer::ID, $this->id);
        if ($this->isColumnModified(CmsThreadPeer::IS_ACTIVE)) $criteria->add(CmsThreadPeer::IS_ACTIVE, $this->is_active);

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
        $criteria = new Criteria(CmsThreadPeer::DATABASE_NAME);
        $criteria->add(CmsThreadPeer::ID, $this->id);

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
     * @param object $copyObj An object of CmsThread (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setIsActive($this->getIsActive());

        if ($deepCopy && !$this->startCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);
            // store object hash to prevent cycle
            $this->startCopy = true;

            foreach ($this->getCmss() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addCms($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getCmsThreadI18ns() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addCmsThreadI18n($relObj->copy($deepCopy));
                }
            }

            //unflag object copy
            $this->startCopy = false;
        } // if ($deepCopy)

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
     * @return CmsThread Clone of current object.
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
     * @return CmsThreadPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new CmsThreadPeer();
        }

        return self::$peer;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('Cms' == $relationName) {
            $this->initCmss();
        }
        if ('CmsThreadI18n' == $relationName) {
            $this->initCmsThreadI18ns();
        }
    }

    /**
     * Clears out the collCmss collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return CmsThread The current object (for fluent API support)
     * @see        addCmss()
     */
    public function clearCmss()
    {
        $this->collCmss = null; // important to set this to null since that means it is uninitialized
        $this->collCmssPartial = null;

        return $this;
    }

    /**
     * reset is the collCmss collection loaded partially
     *
     * @return void
     */
    public function resetPartialCmss($v = true)
    {
        $this->collCmssPartial = $v;
    }

    /**
     * Initializes the collCmss collection.
     *
     * By default this just sets the collCmss collection to an empty array (like clearcollCmss());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initCmss($overrideExisting = true)
    {
        if (null !== $this->collCmss && !$overrideExisting) {
            return;
        }
        $this->collCmss = new PropelObjectCollection();
        $this->collCmss->setModel('Cms');
    }

    /**
     * Gets an array of Cms objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this CmsThread is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|Cms[] List of Cms objects
     * @throws PropelException
     */
    public function getCmss($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collCmssPartial && !$this->isNew();
        if (null === $this->collCmss || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collCmss) {
                // return empty collection
                $this->initCmss();
            } else {
                $collCmss = CmsQuery::create(null, $criteria)
                    ->filterByCmsThread($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collCmssPartial && count($collCmss)) {
                      $this->initCmss(false);

                      foreach ($collCmss as $obj) {
                        if (false == $this->collCmss->contains($obj)) {
                          $this->collCmss->append($obj);
                        }
                      }

                      $this->collCmssPartial = true;
                    }

                    $collCmss->getInternalIterator()->rewind();

                    return $collCmss;
                }

                if ($partial && $this->collCmss) {
                    foreach ($this->collCmss as $obj) {
                        if ($obj->isNew()) {
                            $collCmss[] = $obj;
                        }
                    }
                }

                $this->collCmss = $collCmss;
                $this->collCmssPartial = false;
            }
        }

        return $this->collCmss;
    }

    /**
     * Sets a collection of Cms objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $cmss A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return CmsThread The current object (for fluent API support)
     */
    public function setCmss(PropelCollection $cmss, PropelPDO $con = null)
    {
        $cmssToDelete = $this->getCmss(new Criteria(), $con)->diff($cmss);


        $this->cmssScheduledForDeletion = $cmssToDelete;

        foreach ($cmssToDelete as $cmsRemoved) {
            $cmsRemoved->setCmsThread(null);
        }

        $this->collCmss = null;
        foreach ($cmss as $cms) {
            $this->addCms($cms);
        }

        $this->collCmss = $cmss;
        $this->collCmssPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Cms objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related Cms objects.
     * @throws PropelException
     */
    public function countCmss(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collCmssPartial && !$this->isNew();
        if (null === $this->collCmss || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collCmss) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getCmss());
            }
            $query = CmsQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByCmsThread($this)
                ->count($con);
        }

        return count($this->collCmss);
    }

    /**
     * Method called to associate a Cms object to this object
     * through the Cms foreign key attribute.
     *
     * @param    Cms $l Cms
     * @return CmsThread The current object (for fluent API support)
     */
    public function addCms(Cms $l)
    {
        if ($this->collCmss === null) {
            $this->initCmss();
            $this->collCmssPartial = true;
        }

        if (!in_array($l, $this->collCmss->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddCms($l);

            if ($this->cmssScheduledForDeletion and $this->cmssScheduledForDeletion->contains($l)) {
                $this->cmssScheduledForDeletion->remove($this->cmssScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	Cms $cms The cms object to add.
     */
    protected function doAddCms($cms)
    {
        $this->collCmss[]= $cms;
        $cms->setCmsThread($this);
    }

    /**
     * @param	Cms $cms The cms object to remove.
     * @return CmsThread The current object (for fluent API support)
     */
    public function removeCms($cms)
    {
        if ($this->getCmss()->contains($cms)) {
            $this->collCmss->remove($this->collCmss->search($cms));
            if (null === $this->cmssScheduledForDeletion) {
                $this->cmssScheduledForDeletion = clone $this->collCmss;
                $this->cmssScheduledForDeletion->clear();
            }
            $this->cmssScheduledForDeletion[]= clone $cms;
            $cms->setCmsThread(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this CmsThread is new, it will return
     * an empty collection; or if this CmsThread has previously
     * been saved, it will retrieve related Cmss from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in CmsThread.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|Cms[] List of Cms objects
     */
    public function getCmssJoinCmsRelatedByParentId($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = CmsQuery::create(null, $criteria);
        $query->joinWith('CmsRelatedByParentId', $join_behavior);

        return $this->getCmss($query, $con);
    }

    /**
     * Clears out the collCmsThreadI18ns collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return CmsThread The current object (for fluent API support)
     * @see        addCmsThreadI18ns()
     */
    public function clearCmsThreadI18ns()
    {
        $this->collCmsThreadI18ns = null; // important to set this to null since that means it is uninitialized
        $this->collCmsThreadI18nsPartial = null;

        return $this;
    }

    /**
     * reset is the collCmsThreadI18ns collection loaded partially
     *
     * @return void
     */
    public function resetPartialCmsThreadI18ns($v = true)
    {
        $this->collCmsThreadI18nsPartial = $v;
    }

    /**
     * Initializes the collCmsThreadI18ns collection.
     *
     * By default this just sets the collCmsThreadI18ns collection to an empty array (like clearcollCmsThreadI18ns());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initCmsThreadI18ns($overrideExisting = true)
    {
        if (null !== $this->collCmsThreadI18ns && !$overrideExisting) {
            return;
        }
        $this->collCmsThreadI18ns = new PropelObjectCollection();
        $this->collCmsThreadI18ns->setModel('CmsThreadI18n');
    }

    /**
     * Gets an array of CmsThreadI18n objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this CmsThread is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|CmsThreadI18n[] List of CmsThreadI18n objects
     * @throws PropelException
     */
    public function getCmsThreadI18ns($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collCmsThreadI18nsPartial && !$this->isNew();
        if (null === $this->collCmsThreadI18ns || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collCmsThreadI18ns) {
                // return empty collection
                $this->initCmsThreadI18ns();
            } else {
                $collCmsThreadI18ns = CmsThreadI18nQuery::create(null, $criteria)
                    ->filterByCmsThread($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collCmsThreadI18nsPartial && count($collCmsThreadI18ns)) {
                      $this->initCmsThreadI18ns(false);

                      foreach ($collCmsThreadI18ns as $obj) {
                        if (false == $this->collCmsThreadI18ns->contains($obj)) {
                          $this->collCmsThreadI18ns->append($obj);
                        }
                      }

                      $this->collCmsThreadI18nsPartial = true;
                    }

                    $collCmsThreadI18ns->getInternalIterator()->rewind();

                    return $collCmsThreadI18ns;
                }

                if ($partial && $this->collCmsThreadI18ns) {
                    foreach ($this->collCmsThreadI18ns as $obj) {
                        if ($obj->isNew()) {
                            $collCmsThreadI18ns[] = $obj;
                        }
                    }
                }

                $this->collCmsThreadI18ns = $collCmsThreadI18ns;
                $this->collCmsThreadI18nsPartial = false;
            }
        }

        return $this->collCmsThreadI18ns;
    }

    /**
     * Sets a collection of CmsThreadI18n objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $cmsThreadI18ns A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return CmsThread The current object (for fluent API support)
     */
    public function setCmsThreadI18ns(PropelCollection $cmsThreadI18ns, PropelPDO $con = null)
    {
        $cmsThreadI18nsToDelete = $this->getCmsThreadI18ns(new Criteria(), $con)->diff($cmsThreadI18ns);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->cmsThreadI18nsScheduledForDeletion = clone $cmsThreadI18nsToDelete;

        foreach ($cmsThreadI18nsToDelete as $cmsThreadI18nRemoved) {
            $cmsThreadI18nRemoved->setCmsThread(null);
        }

        $this->collCmsThreadI18ns = null;
        foreach ($cmsThreadI18ns as $cmsThreadI18n) {
            $this->addCmsThreadI18n($cmsThreadI18n);
        }

        $this->collCmsThreadI18ns = $cmsThreadI18ns;
        $this->collCmsThreadI18nsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related CmsThreadI18n objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related CmsThreadI18n objects.
     * @throws PropelException
     */
    public function countCmsThreadI18ns(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collCmsThreadI18nsPartial && !$this->isNew();
        if (null === $this->collCmsThreadI18ns || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collCmsThreadI18ns) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getCmsThreadI18ns());
            }
            $query = CmsThreadI18nQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByCmsThread($this)
                ->count($con);
        }

        return count($this->collCmsThreadI18ns);
    }

    /**
     * Method called to associate a CmsThreadI18n object to this object
     * through the CmsThreadI18n foreign key attribute.
     *
     * @param    CmsThreadI18n $l CmsThreadI18n
     * @return CmsThread The current object (for fluent API support)
     */
    public function addCmsThreadI18n(CmsThreadI18n $l)
    {
        if ($l && $locale = $l->getLocale()) {
            $this->setLocale($locale);
            $this->currentTranslations[$locale] = $l;
        }
        if ($this->collCmsThreadI18ns === null) {
            $this->initCmsThreadI18ns();
            $this->collCmsThreadI18nsPartial = true;
        }

        if (!in_array($l, $this->collCmsThreadI18ns->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddCmsThreadI18n($l);

            if ($this->cmsThreadI18nsScheduledForDeletion and $this->cmsThreadI18nsScheduledForDeletion->contains($l)) {
                $this->cmsThreadI18nsScheduledForDeletion->remove($this->cmsThreadI18nsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	CmsThreadI18n $cmsThreadI18n The cmsThreadI18n object to add.
     */
    protected function doAddCmsThreadI18n($cmsThreadI18n)
    {
        $this->collCmsThreadI18ns[]= $cmsThreadI18n;
        $cmsThreadI18n->setCmsThread($this);
    }

    /**
     * @param	CmsThreadI18n $cmsThreadI18n The cmsThreadI18n object to remove.
     * @return CmsThread The current object (for fluent API support)
     */
    public function removeCmsThreadI18n($cmsThreadI18n)
    {
        if ($this->getCmsThreadI18ns()->contains($cmsThreadI18n)) {
            $this->collCmsThreadI18ns->remove($this->collCmsThreadI18ns->search($cmsThreadI18n));
            if (null === $this->cmsThreadI18nsScheduledForDeletion) {
                $this->cmsThreadI18nsScheduledForDeletion = clone $this->collCmsThreadI18ns;
                $this->cmsThreadI18nsScheduledForDeletion->clear();
            }
            $this->cmsThreadI18nsScheduledForDeletion[]= clone $cmsThreadI18n;
            $cmsThreadI18n->setCmsThread(null);
        }

        return $this;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
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
            if ($this->collCmss) {
                foreach ($this->collCmss as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collCmsThreadI18ns) {
                foreach ($this->collCmsThreadI18ns as $o) {
                    $o->clearAllReferences($deep);
                }
            }

            $this->alreadyInClearAllReferencesDeep = false;
        } // if ($deep)

        // i18n behavior
        $this->currentLocale = 'da_DK';
        $this->currentTranslations = null;

        if ($this->collCmss instanceof PropelCollection) {
            $this->collCmss->clearIterator();
        }
        $this->collCmss = null;
        if ($this->collCmsThreadI18ns instanceof PropelCollection) {
            $this->collCmsThreadI18ns->clearIterator();
        }
        $this->collCmsThreadI18ns = null;
    }

    /**
     * return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(CmsThreadPeer::DEFAULT_STRING_FORMAT);
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

    // i18n behavior

    /**
     * Sets the locale for translations
     *
     * @param     string $locale Locale to use for the translation, e.g. 'fr_FR'
     *
     * @return    CmsThread The current object (for fluent API support)
     */
    public function setLocale($locale = 'da_DK')
    {
        $this->currentLocale = $locale;

        return $this;
    }

    /**
     * Gets the locale for translations
     *
     * @return    string $locale Locale to use for the translation, e.g. 'fr_FR'
     */
    public function getLocale()
    {
        return $this->currentLocale;
    }

    /**
     * Returns the current translation for a given locale
     *
     * @param     string $locale Locale to use for the translation, e.g. 'fr_FR'
     * @param     PropelPDO $con an optional connection object
     *
     * @return CmsThreadI18n */
    public function getTranslation($locale = 'da_DK', PropelPDO $con = null)
    {
        if (!isset($this->currentTranslations[$locale])) {
            if (null !== $this->collCmsThreadI18ns) {
                foreach ($this->collCmsThreadI18ns as $translation) {
                    if ($translation->getLocale() == $locale) {
                        $this->currentTranslations[$locale] = $translation;

                        return $translation;
                    }
                }
            }
            if ($this->isNew()) {
                $translation = new CmsThreadI18n();
                $translation->setLocale($locale);
            } else {
                $translation = CmsThreadI18nQuery::create()
                    ->filterByPrimaryKey(array($this->getPrimaryKey(), $locale))
                    ->findOneOrCreate($con);
                $this->currentTranslations[$locale] = $translation;
            }
            $this->addCmsThreadI18n($translation);
        }

        return $this->currentTranslations[$locale];
    }

    /**
     * Remove the translation for a given locale
     *
     * @param     string $locale Locale to use for the translation, e.g. 'fr_FR'
     * @param     PropelPDO $con an optional connection object
     *
     * @return    CmsThread The current object (for fluent API support)
     */
    public function removeTranslation($locale = 'da_DK', PropelPDO $con = null)
    {
        if (!$this->isNew()) {
            CmsThreadI18nQuery::create()
                ->filterByPrimaryKey(array($this->getPrimaryKey(), $locale))
                ->delete($con);
        }
        if (isset($this->currentTranslations[$locale])) {
            unset($this->currentTranslations[$locale]);
        }
        foreach ($this->collCmsThreadI18ns as $key => $translation) {
            if ($translation->getLocale() == $locale) {
                unset($this->collCmsThreadI18ns[$key]);
                break;
            }
        }

        return $this;
    }

    /**
     * Returns the current translation
     *
     * @param     PropelPDO $con an optional connection object
     *
     * @return CmsThreadI18n */
    public function getCurrentTranslation(PropelPDO $con = null)
    {
        return $this->getTranslation($this->getLocale(), $con);
    }


        /**
         * Get the [title] column value.
         *
         * @return string
         */
        public function getTitle()
        {
        return $this->getCurrentTranslation()->getTitle();
    }


        /**
         * Set the value of [title] column.
         *
         * @param  string $v new value
         * @return CmsThreadI18n The current object (for fluent API support)
         */
        public function setTitle($v)
        {    $this->getCurrentTranslation()->setTitle($v);

        return $this;
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
