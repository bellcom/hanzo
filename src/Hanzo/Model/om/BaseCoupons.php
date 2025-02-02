<?php

namespace Hanzo\Model\om;

use \BaseObject;
use \BasePeer;
use \Criteria;
use \DateTime;
use \Exception;
use \PDO;
use \Persistent;
use \Propel;
use \PropelCollection;
use \PropelDateTime;
use \PropelException;
use \PropelObjectCollection;
use \PropelPDO;
use Glorpen\Propel\PropelBundle\Dispatcher\EventDispatcherProxy;
use Glorpen\Propel\PropelBundle\Events\ModelEvent;
use Hanzo\Model\Coupons;
use Hanzo\Model\CouponsPeer;
use Hanzo\Model\CouponsQuery;
use Hanzo\Model\OrdersToCoupons;
use Hanzo\Model\OrdersToCouponsQuery;

abstract class BaseCoupons extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'Hanzo\\Model\\CouponsPeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        CouponsPeer
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
     * The value for the code field.
     * @var        string
     */
    protected $code;

    /**
     * The value for the amount field.
     * @var        string
     */
    protected $amount;

    /**
     * The value for the amount_type field.
     * Note: this column has a database default value of: 'amount'
     * @var        string
     */
    protected $amount_type;

    /**
     * The value for the min_purchase_amount field.
     * @var        string
     */
    protected $min_purchase_amount;

    /**
     * The value for the currency_code field.
     * @var        string
     */
    protected $currency_code;

    /**
     * The value for the active_from field.
     * @var        string
     */
    protected $active_from;

    /**
     * The value for the active_to field.
     * @var        string
     */
    protected $active_to;

    /**
     * The value for the is_active field.
     * Note: this column has a database default value of: true
     * @var        boolean
     */
    protected $is_active;

    /**
     * The value for the is_used field.
     * Note: this column has a database default value of: false
     * @var        boolean
     */
    protected $is_used;

    /**
     * The value for the is_reusable field.
     * Note: this column has a database default value of: false
     * @var        boolean
     */
    protected $is_reusable;

    /**
     * The value for the created_at field.
     * @var        string
     */
    protected $created_at;

    /**
     * The value for the updated_at field.
     * @var        string
     */
    protected $updated_at;

    /**
     * @var        PropelObjectCollection|OrdersToCoupons[] Collection to store aggregation of OrdersToCoupons objects.
     */
    protected $collOrdersToCouponss;
    protected $collOrdersToCouponssPartial;

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
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $ordersToCouponssScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see        __construct()
     */
    public function applyDefaultValues()
    {
        $this->amount_type = 'amount';
        $this->is_active = true;
        $this->is_used = false;
        $this->is_reusable = false;
    }

    /**
     * Initializes internal state of BaseCoupons object.
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
     * Get the [code] column value.
     *
     * @return string
     */
    public function getCode()
    {

        return $this->code;
    }

    /**
     * Get the [amount] column value.
     *
     * @return string
     */
    public function getAmount()
    {

        return $this->amount;
    }

    /**
     * Get the [amount_type] column value.
     *
     * @return string
     */
    public function getAmountType()
    {

        return $this->amount_type;
    }

    /**
     * Get the [min_purchase_amount] column value.
     *
     * @return string
     */
    public function getMinPurchaseAmount()
    {

        return $this->min_purchase_amount;
    }

    /**
     * Get the [currency_code] column value.
     *
     * @return string
     */
    public function getCurrencyCode()
    {

        return $this->currency_code;
    }

    /**
     * Get the [optionally formatted] temporal [active_from] column value.
     *
     * This accessor only only work with unix epoch dates.  Consider enabling the propel.useDateTimeClass
     * option in order to avoid conversions to integers (which are limited in the dates they can express).
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw unix timestamp integer will be returned.
     * @return mixed Formatted date/time value as string or (integer) unix timestamp (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getActiveFrom($format = 'Y-m-d H:i:s')
    {
        if ($this->active_from === null) {
            return null;
        }

        if ($this->active_from === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->active_from);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->active_from, true), $x);
        }

        if ($format === null) {
            // We cast here to maintain BC in API; obviously we will lose data if we're dealing with pre-/post-epoch dates.
            return (int) $dt->format('U');
        }

        if (strpos($format, '%') !== false) {
            return strftime($format, $dt->format('U'));
        }

        return $dt->format($format);

    }

    /**
     * Get the [optionally formatted] temporal [active_to] column value.
     *
     * This accessor only only work with unix epoch dates.  Consider enabling the propel.useDateTimeClass
     * option in order to avoid conversions to integers (which are limited in the dates they can express).
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw unix timestamp integer will be returned.
     * @return mixed Formatted date/time value as string or (integer) unix timestamp (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getActiveTo($format = 'Y-m-d H:i:s')
    {
        if ($this->active_to === null) {
            return null;
        }

        if ($this->active_to === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->active_to);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->active_to, true), $x);
        }

        if ($format === null) {
            // We cast here to maintain BC in API; obviously we will lose data if we're dealing with pre-/post-epoch dates.
            return (int) $dt->format('U');
        }

        if (strpos($format, '%') !== false) {
            return strftime($format, $dt->format('U'));
        }

        return $dt->format($format);

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
     * Get the [is_used] column value.
     *
     * @return boolean
     */
    public function getIsUsed()
    {

        return $this->is_used;
    }

    /**
     * Get the [is_reusable] column value.
     *
     * @return boolean
     */
    public function getIsReusable()
    {

        return $this->is_reusable;
    }

    /**
     * Get the [optionally formatted] temporal [created_at] column value.
     *
     * This accessor only only work with unix epoch dates.  Consider enabling the propel.useDateTimeClass
     * option in order to avoid conversions to integers (which are limited in the dates they can express).
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw unix timestamp integer will be returned.
     * @return mixed Formatted date/time value as string or (integer) unix timestamp (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getCreatedAt($format = 'Y-m-d H:i:s')
    {
        if ($this->created_at === null) {
            return null;
        }

        if ($this->created_at === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->created_at);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->created_at, true), $x);
        }

        if ($format === null) {
            // We cast here to maintain BC in API; obviously we will lose data if we're dealing with pre-/post-epoch dates.
            return (int) $dt->format('U');
        }

        if (strpos($format, '%') !== false) {
            return strftime($format, $dt->format('U'));
        }

        return $dt->format($format);

    }

    /**
     * Get the [optionally formatted] temporal [updated_at] column value.
     *
     * This accessor only only work with unix epoch dates.  Consider enabling the propel.useDateTimeClass
     * option in order to avoid conversions to integers (which are limited in the dates they can express).
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw unix timestamp integer will be returned.
     * @return mixed Formatted date/time value as string or (integer) unix timestamp (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getUpdatedAt($format = 'Y-m-d H:i:s')
    {
        if ($this->updated_at === null) {
            return null;
        }

        if ($this->updated_at === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->updated_at);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->updated_at, true), $x);
        }

        if ($format === null) {
            // We cast here to maintain BC in API; obviously we will lose data if we're dealing with pre-/post-epoch dates.
            return (int) $dt->format('U');
        }

        if (strpos($format, '%') !== false) {
            return strftime($format, $dt->format('U'));
        }

        return $dt->format($format);

    }

    /**
     * Set the value of [id] column.
     *
     * @param  int $v new value
     * @return Coupons The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[] = CouponsPeer::ID;
        }


        return $this;
    } // setId()

    /**
     * Set the value of [code] column.
     *
     * @param  string $v new value
     * @return Coupons The current object (for fluent API support)
     */
    public function setCode($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->code !== $v) {
            $this->code = $v;
            $this->modifiedColumns[] = CouponsPeer::CODE;
        }


        return $this;
    } // setCode()

    /**
     * Set the value of [amount] column.
     *
     * @param  string $v new value
     * @return Coupons The current object (for fluent API support)
     */
    public function setAmount($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->amount !== $v) {
            $this->amount = $v;
            $this->modifiedColumns[] = CouponsPeer::AMOUNT;
        }


        return $this;
    } // setAmount()

    /**
     * Set the value of [amount_type] column.
     *
     * @param  string $v new value
     * @return Coupons The current object (for fluent API support)
     */
    public function setAmountType($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->amount_type !== $v) {
            $this->amount_type = $v;
            $this->modifiedColumns[] = CouponsPeer::AMOUNT_TYPE;
        }


        return $this;
    } // setAmountType()

    /**
     * Set the value of [min_purchase_amount] column.
     *
     * @param  string $v new value
     * @return Coupons The current object (for fluent API support)
     */
    public function setMinPurchaseAmount($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->min_purchase_amount !== $v) {
            $this->min_purchase_amount = $v;
            $this->modifiedColumns[] = CouponsPeer::MIN_PURCHASE_AMOUNT;
        }


        return $this;
    } // setMinPurchaseAmount()

    /**
     * Set the value of [currency_code] column.
     *
     * @param  string $v new value
     * @return Coupons The current object (for fluent API support)
     */
    public function setCurrencyCode($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->currency_code !== $v) {
            $this->currency_code = $v;
            $this->modifiedColumns[] = CouponsPeer::CURRENCY_CODE;
        }


        return $this;
    } // setCurrencyCode()

    /**
     * Sets the value of [active_from] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return Coupons The current object (for fluent API support)
     */
    public function setActiveFrom($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->active_from !== null || $dt !== null) {
            $currentDateAsString = ($this->active_from !== null && $tmpDt = new DateTime($this->active_from)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->active_from = $newDateAsString;
                $this->modifiedColumns[] = CouponsPeer::ACTIVE_FROM;
            }
        } // if either are not null


        return $this;
    } // setActiveFrom()

    /**
     * Sets the value of [active_to] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return Coupons The current object (for fluent API support)
     */
    public function setActiveTo($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->active_to !== null || $dt !== null) {
            $currentDateAsString = ($this->active_to !== null && $tmpDt = new DateTime($this->active_to)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->active_to = $newDateAsString;
                $this->modifiedColumns[] = CouponsPeer::ACTIVE_TO;
            }
        } // if either are not null


        return $this;
    } // setActiveTo()

    /**
     * Sets the value of the [is_active] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param boolean|integer|string $v The new value
     * @return Coupons The current object (for fluent API support)
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
            $this->modifiedColumns[] = CouponsPeer::IS_ACTIVE;
        }


        return $this;
    } // setIsActive()

    /**
     * Sets the value of the [is_used] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param boolean|integer|string $v The new value
     * @return Coupons The current object (for fluent API support)
     */
    public function setIsUsed($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->is_used !== $v) {
            $this->is_used = $v;
            $this->modifiedColumns[] = CouponsPeer::IS_USED;
        }


        return $this;
    } // setIsUsed()

    /**
     * Sets the value of the [is_reusable] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param boolean|integer|string $v The new value
     * @return Coupons The current object (for fluent API support)
     */
    public function setIsReusable($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->is_reusable !== $v) {
            $this->is_reusable = $v;
            $this->modifiedColumns[] = CouponsPeer::IS_REUSABLE;
        }


        return $this;
    } // setIsReusable()

    /**
     * Sets the value of [created_at] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return Coupons The current object (for fluent API support)
     */
    public function setCreatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->created_at !== null || $dt !== null) {
            $currentDateAsString = ($this->created_at !== null && $tmpDt = new DateTime($this->created_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->created_at = $newDateAsString;
                $this->modifiedColumns[] = CouponsPeer::CREATED_AT;
            }
        } // if either are not null


        return $this;
    } // setCreatedAt()

    /**
     * Sets the value of [updated_at] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return Coupons The current object (for fluent API support)
     */
    public function setUpdatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->updated_at !== null || $dt !== null) {
            $currentDateAsString = ($this->updated_at !== null && $tmpDt = new DateTime($this->updated_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->updated_at = $newDateAsString;
                $this->modifiedColumns[] = CouponsPeer::UPDATED_AT;
            }
        } // if either are not null


        return $this;
    } // setUpdatedAt()

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
            if ($this->amount_type !== 'amount') {
                return false;
            }

            if ($this->is_active !== true) {
                return false;
            }

            if ($this->is_used !== false) {
                return false;
            }

            if ($this->is_reusable !== false) {
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
            $this->code = ($row[$startcol + 1] !== null) ? (string) $row[$startcol + 1] : null;
            $this->amount = ($row[$startcol + 2] !== null) ? (string) $row[$startcol + 2] : null;
            $this->amount_type = ($row[$startcol + 3] !== null) ? (string) $row[$startcol + 3] : null;
            $this->min_purchase_amount = ($row[$startcol + 4] !== null) ? (string) $row[$startcol + 4] : null;
            $this->currency_code = ($row[$startcol + 5] !== null) ? (string) $row[$startcol + 5] : null;
            $this->active_from = ($row[$startcol + 6] !== null) ? (string) $row[$startcol + 6] : null;
            $this->active_to = ($row[$startcol + 7] !== null) ? (string) $row[$startcol + 7] : null;
            $this->is_active = ($row[$startcol + 8] !== null) ? (boolean) $row[$startcol + 8] : null;
            $this->is_used = ($row[$startcol + 9] !== null) ? (boolean) $row[$startcol + 9] : null;
            $this->is_reusable = ($row[$startcol + 10] !== null) ? (boolean) $row[$startcol + 10] : null;
            $this->created_at = ($row[$startcol + 11] !== null) ? (string) $row[$startcol + 11] : null;
            $this->updated_at = ($row[$startcol + 12] !== null) ? (string) $row[$startcol + 12] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);

            return $startcol + 13; // 13 = CouponsPeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating Coupons object", $e);
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
            $con = Propel::getConnection(CouponsPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = CouponsPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->collOrdersToCouponss = null;

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
            $con = Propel::getConnection(CouponsPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            EventDispatcherProxy::trigger(array('delete.pre','model.delete.pre'), new ModelEvent($this));
            $deleteQuery = CouponsQuery::create()
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
            $con = Propel::getConnection(CouponsPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        $isInsert = $this->isNew();
        try {
            $ret = $this->preSave($con);
            // event behavior
            EventDispatcherProxy::trigger('model.save.pre', new ModelEvent($this));
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
                // timestampable behavior
                if (!$this->isColumnModified(CouponsPeer::CREATED_AT)) {
                    $this->setCreatedAt(time());
                }
                if (!$this->isColumnModified(CouponsPeer::UPDATED_AT)) {
                    $this->setUpdatedAt(time());
                }
                // event behavior
                EventDispatcherProxy::trigger('model.insert.pre', new ModelEvent($this));
            } else {
                $ret = $ret && $this->preUpdate($con);
                // timestampable behavior
                if ($this->isModified() && !$this->isColumnModified(CouponsPeer::UPDATED_AT)) {
                    $this->setUpdatedAt(time());
                }
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
                CouponsPeer::addInstanceToPool($this);
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

            if ($this->ordersToCouponssScheduledForDeletion !== null) {
                if (!$this->ordersToCouponssScheduledForDeletion->isEmpty()) {
                    OrdersToCouponsQuery::create()
                        ->filterByPrimaryKeys($this->ordersToCouponssScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->ordersToCouponssScheduledForDeletion = null;
                }
            }

            if ($this->collOrdersToCouponss !== null) {
                foreach ($this->collOrdersToCouponss as $referrerFK) {
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

        $this->modifiedColumns[] = CouponsPeer::ID;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . CouponsPeer::ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(CouponsPeer::ID)) {
            $modifiedColumns[':p' . $index++]  = '`id`';
        }
        if ($this->isColumnModified(CouponsPeer::CODE)) {
            $modifiedColumns[':p' . $index++]  = '`code`';
        }
        if ($this->isColumnModified(CouponsPeer::AMOUNT)) {
            $modifiedColumns[':p' . $index++]  = '`amount`';
        }
        if ($this->isColumnModified(CouponsPeer::AMOUNT_TYPE)) {
            $modifiedColumns[':p' . $index++]  = '`amount_type`';
        }
        if ($this->isColumnModified(CouponsPeer::MIN_PURCHASE_AMOUNT)) {
            $modifiedColumns[':p' . $index++]  = '`min_purchase_amount`';
        }
        if ($this->isColumnModified(CouponsPeer::CURRENCY_CODE)) {
            $modifiedColumns[':p' . $index++]  = '`currency_code`';
        }
        if ($this->isColumnModified(CouponsPeer::ACTIVE_FROM)) {
            $modifiedColumns[':p' . $index++]  = '`active_from`';
        }
        if ($this->isColumnModified(CouponsPeer::ACTIVE_TO)) {
            $modifiedColumns[':p' . $index++]  = '`active_to`';
        }
        if ($this->isColumnModified(CouponsPeer::IS_ACTIVE)) {
            $modifiedColumns[':p' . $index++]  = '`is_active`';
        }
        if ($this->isColumnModified(CouponsPeer::IS_USED)) {
            $modifiedColumns[':p' . $index++]  = '`is_used`';
        }
        if ($this->isColumnModified(CouponsPeer::IS_REUSABLE)) {
            $modifiedColumns[':p' . $index++]  = '`is_reusable`';
        }
        if ($this->isColumnModified(CouponsPeer::CREATED_AT)) {
            $modifiedColumns[':p' . $index++]  = '`created_at`';
        }
        if ($this->isColumnModified(CouponsPeer::UPDATED_AT)) {
            $modifiedColumns[':p' . $index++]  = '`updated_at`';
        }

        $sql = sprintf(
            'INSERT INTO `coupons` (%s) VALUES (%s)',
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
                    case '`code`':
                        $stmt->bindValue($identifier, $this->code, PDO::PARAM_STR);
                        break;
                    case '`amount`':
                        $stmt->bindValue($identifier, $this->amount, PDO::PARAM_STR);
                        break;
                    case '`amount_type`':
                        $stmt->bindValue($identifier, $this->amount_type, PDO::PARAM_STR);
                        break;
                    case '`min_purchase_amount`':
                        $stmt->bindValue($identifier, $this->min_purchase_amount, PDO::PARAM_STR);
                        break;
                    case '`currency_code`':
                        $stmt->bindValue($identifier, $this->currency_code, PDO::PARAM_STR);
                        break;
                    case '`active_from`':
                        $stmt->bindValue($identifier, $this->active_from, PDO::PARAM_STR);
                        break;
                    case '`active_to`':
                        $stmt->bindValue($identifier, $this->active_to, PDO::PARAM_STR);
                        break;
                    case '`is_active`':
                        $stmt->bindValue($identifier, (int) $this->is_active, PDO::PARAM_INT);
                        break;
                    case '`is_used`':
                        $stmt->bindValue($identifier, (int) $this->is_used, PDO::PARAM_INT);
                        break;
                    case '`is_reusable`':
                        $stmt->bindValue($identifier, (int) $this->is_reusable, PDO::PARAM_INT);
                        break;
                    case '`created_at`':
                        $stmt->bindValue($identifier, $this->created_at, PDO::PARAM_STR);
                        break;
                    case '`updated_at`':
                        $stmt->bindValue($identifier, $this->updated_at, PDO::PARAM_STR);
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


            if (($retval = CouponsPeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
            }


                if ($this->collOrdersToCouponss !== null) {
                    foreach ($this->collOrdersToCouponss as $referrerFK) {
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
        $pos = CouponsPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
                return $this->getCode();
                break;
            case 2:
                return $this->getAmount();
                break;
            case 3:
                return $this->getAmountType();
                break;
            case 4:
                return $this->getMinPurchaseAmount();
                break;
            case 5:
                return $this->getCurrencyCode();
                break;
            case 6:
                return $this->getActiveFrom();
                break;
            case 7:
                return $this->getActiveTo();
                break;
            case 8:
                return $this->getIsActive();
                break;
            case 9:
                return $this->getIsUsed();
                break;
            case 10:
                return $this->getIsReusable();
                break;
            case 11:
                return $this->getCreatedAt();
                break;
            case 12:
                return $this->getUpdatedAt();
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
        if (isset($alreadyDumpedObjects['Coupons'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['Coupons'][$this->getPrimaryKey()] = true;
        $keys = CouponsPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getCode(),
            $keys[2] => $this->getAmount(),
            $keys[3] => $this->getAmountType(),
            $keys[4] => $this->getMinPurchaseAmount(),
            $keys[5] => $this->getCurrencyCode(),
            $keys[6] => $this->getActiveFrom(),
            $keys[7] => $this->getActiveTo(),
            $keys[8] => $this->getIsActive(),
            $keys[9] => $this->getIsUsed(),
            $keys[10] => $this->getIsReusable(),
            $keys[11] => $this->getCreatedAt(),
            $keys[12] => $this->getUpdatedAt(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->collOrdersToCouponss) {
                $result['OrdersToCouponss'] = $this->collOrdersToCouponss->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = CouponsPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

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
                $this->setCode($value);
                break;
            case 2:
                $this->setAmount($value);
                break;
            case 3:
                $this->setAmountType($value);
                break;
            case 4:
                $this->setMinPurchaseAmount($value);
                break;
            case 5:
                $this->setCurrencyCode($value);
                break;
            case 6:
                $this->setActiveFrom($value);
                break;
            case 7:
                $this->setActiveTo($value);
                break;
            case 8:
                $this->setIsActive($value);
                break;
            case 9:
                $this->setIsUsed($value);
                break;
            case 10:
                $this->setIsReusable($value);
                break;
            case 11:
                $this->setCreatedAt($value);
                break;
            case 12:
                $this->setUpdatedAt($value);
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
        $keys = CouponsPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setCode($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setAmount($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setAmountType($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setMinPurchaseAmount($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setCurrencyCode($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setActiveFrom($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setActiveTo($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setIsActive($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setIsUsed($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setIsReusable($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setCreatedAt($arr[$keys[11]]);
        if (array_key_exists($keys[12], $arr)) $this->setUpdatedAt($arr[$keys[12]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(CouponsPeer::DATABASE_NAME);

        if ($this->isColumnModified(CouponsPeer::ID)) $criteria->add(CouponsPeer::ID, $this->id);
        if ($this->isColumnModified(CouponsPeer::CODE)) $criteria->add(CouponsPeer::CODE, $this->code);
        if ($this->isColumnModified(CouponsPeer::AMOUNT)) $criteria->add(CouponsPeer::AMOUNT, $this->amount);
        if ($this->isColumnModified(CouponsPeer::AMOUNT_TYPE)) $criteria->add(CouponsPeer::AMOUNT_TYPE, $this->amount_type);
        if ($this->isColumnModified(CouponsPeer::MIN_PURCHASE_AMOUNT)) $criteria->add(CouponsPeer::MIN_PURCHASE_AMOUNT, $this->min_purchase_amount);
        if ($this->isColumnModified(CouponsPeer::CURRENCY_CODE)) $criteria->add(CouponsPeer::CURRENCY_CODE, $this->currency_code);
        if ($this->isColumnModified(CouponsPeer::ACTIVE_FROM)) $criteria->add(CouponsPeer::ACTIVE_FROM, $this->active_from);
        if ($this->isColumnModified(CouponsPeer::ACTIVE_TO)) $criteria->add(CouponsPeer::ACTIVE_TO, $this->active_to);
        if ($this->isColumnModified(CouponsPeer::IS_ACTIVE)) $criteria->add(CouponsPeer::IS_ACTIVE, $this->is_active);
        if ($this->isColumnModified(CouponsPeer::IS_USED)) $criteria->add(CouponsPeer::IS_USED, $this->is_used);
        if ($this->isColumnModified(CouponsPeer::IS_REUSABLE)) $criteria->add(CouponsPeer::IS_REUSABLE, $this->is_reusable);
        if ($this->isColumnModified(CouponsPeer::CREATED_AT)) $criteria->add(CouponsPeer::CREATED_AT, $this->created_at);
        if ($this->isColumnModified(CouponsPeer::UPDATED_AT)) $criteria->add(CouponsPeer::UPDATED_AT, $this->updated_at);

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
        $criteria = new Criteria(CouponsPeer::DATABASE_NAME);
        $criteria->add(CouponsPeer::ID, $this->id);

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
     * @param object $copyObj An object of Coupons (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setCode($this->getCode());
        $copyObj->setAmount($this->getAmount());
        $copyObj->setAmountType($this->getAmountType());
        $copyObj->setMinPurchaseAmount($this->getMinPurchaseAmount());
        $copyObj->setCurrencyCode($this->getCurrencyCode());
        $copyObj->setActiveFrom($this->getActiveFrom());
        $copyObj->setActiveTo($this->getActiveTo());
        $copyObj->setIsActive($this->getIsActive());
        $copyObj->setIsUsed($this->getIsUsed());
        $copyObj->setIsReusable($this->getIsReusable());
        $copyObj->setCreatedAt($this->getCreatedAt());
        $copyObj->setUpdatedAt($this->getUpdatedAt());

        if ($deepCopy && !$this->startCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);
            // store object hash to prevent cycle
            $this->startCopy = true;

            foreach ($this->getOrdersToCouponss() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addOrdersToCoupons($relObj->copy($deepCopy));
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
     * @return Coupons Clone of current object.
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
     * @return CouponsPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new CouponsPeer();
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
        if ('OrdersToCoupons' == $relationName) {
            $this->initOrdersToCouponss();
        }
    }

    /**
     * Clears out the collOrdersToCouponss collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return Coupons The current object (for fluent API support)
     * @see        addOrdersToCouponss()
     */
    public function clearOrdersToCouponss()
    {
        $this->collOrdersToCouponss = null; // important to set this to null since that means it is uninitialized
        $this->collOrdersToCouponssPartial = null;

        return $this;
    }

    /**
     * reset is the collOrdersToCouponss collection loaded partially
     *
     * @return void
     */
    public function resetPartialOrdersToCouponss($v = true)
    {
        $this->collOrdersToCouponssPartial = $v;
    }

    /**
     * Initializes the collOrdersToCouponss collection.
     *
     * By default this just sets the collOrdersToCouponss collection to an empty array (like clearcollOrdersToCouponss());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initOrdersToCouponss($overrideExisting = true)
    {
        if (null !== $this->collOrdersToCouponss && !$overrideExisting) {
            return;
        }
        $this->collOrdersToCouponss = new PropelObjectCollection();
        $this->collOrdersToCouponss->setModel('OrdersToCoupons');
    }

    /**
     * Gets an array of OrdersToCoupons objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this Coupons is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|OrdersToCoupons[] List of OrdersToCoupons objects
     * @throws PropelException
     */
    public function getOrdersToCouponss($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collOrdersToCouponssPartial && !$this->isNew();
        if (null === $this->collOrdersToCouponss || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collOrdersToCouponss) {
                // return empty collection
                $this->initOrdersToCouponss();
            } else {
                $collOrdersToCouponss = OrdersToCouponsQuery::create(null, $criteria)
                    ->filterByCoupons($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collOrdersToCouponssPartial && count($collOrdersToCouponss)) {
                      $this->initOrdersToCouponss(false);

                      foreach ($collOrdersToCouponss as $obj) {
                        if (false == $this->collOrdersToCouponss->contains($obj)) {
                          $this->collOrdersToCouponss->append($obj);
                        }
                      }

                      $this->collOrdersToCouponssPartial = true;
                    }

                    $collOrdersToCouponss->getInternalIterator()->rewind();

                    return $collOrdersToCouponss;
                }

                if ($partial && $this->collOrdersToCouponss) {
                    foreach ($this->collOrdersToCouponss as $obj) {
                        if ($obj->isNew()) {
                            $collOrdersToCouponss[] = $obj;
                        }
                    }
                }

                $this->collOrdersToCouponss = $collOrdersToCouponss;
                $this->collOrdersToCouponssPartial = false;
            }
        }

        return $this->collOrdersToCouponss;
    }

    /**
     * Sets a collection of OrdersToCoupons objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $ordersToCouponss A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return Coupons The current object (for fluent API support)
     */
    public function setOrdersToCouponss(PropelCollection $ordersToCouponss, PropelPDO $con = null)
    {
        $ordersToCouponssToDelete = $this->getOrdersToCouponss(new Criteria(), $con)->diff($ordersToCouponss);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->ordersToCouponssScheduledForDeletion = clone $ordersToCouponssToDelete;

        foreach ($ordersToCouponssToDelete as $ordersToCouponsRemoved) {
            $ordersToCouponsRemoved->setCoupons(null);
        }

        $this->collOrdersToCouponss = null;
        foreach ($ordersToCouponss as $ordersToCoupons) {
            $this->addOrdersToCoupons($ordersToCoupons);
        }

        $this->collOrdersToCouponss = $ordersToCouponss;
        $this->collOrdersToCouponssPartial = false;

        return $this;
    }

    /**
     * Returns the number of related OrdersToCoupons objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related OrdersToCoupons objects.
     * @throws PropelException
     */
    public function countOrdersToCouponss(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collOrdersToCouponssPartial && !$this->isNew();
        if (null === $this->collOrdersToCouponss || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collOrdersToCouponss) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getOrdersToCouponss());
            }
            $query = OrdersToCouponsQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByCoupons($this)
                ->count($con);
        }

        return count($this->collOrdersToCouponss);
    }

    /**
     * Method called to associate a OrdersToCoupons object to this object
     * through the OrdersToCoupons foreign key attribute.
     *
     * @param    OrdersToCoupons $l OrdersToCoupons
     * @return Coupons The current object (for fluent API support)
     */
    public function addOrdersToCoupons(OrdersToCoupons $l)
    {
        if ($this->collOrdersToCouponss === null) {
            $this->initOrdersToCouponss();
            $this->collOrdersToCouponssPartial = true;
        }

        if (!in_array($l, $this->collOrdersToCouponss->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddOrdersToCoupons($l);

            if ($this->ordersToCouponssScheduledForDeletion and $this->ordersToCouponssScheduledForDeletion->contains($l)) {
                $this->ordersToCouponssScheduledForDeletion->remove($this->ordersToCouponssScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	OrdersToCoupons $ordersToCoupons The ordersToCoupons object to add.
     */
    protected function doAddOrdersToCoupons($ordersToCoupons)
    {
        $this->collOrdersToCouponss[]= $ordersToCoupons;
        $ordersToCoupons->setCoupons($this);
    }

    /**
     * @param	OrdersToCoupons $ordersToCoupons The ordersToCoupons object to remove.
     * @return Coupons The current object (for fluent API support)
     */
    public function removeOrdersToCoupons($ordersToCoupons)
    {
        if ($this->getOrdersToCouponss()->contains($ordersToCoupons)) {
            $this->collOrdersToCouponss->remove($this->collOrdersToCouponss->search($ordersToCoupons));
            if (null === $this->ordersToCouponssScheduledForDeletion) {
                $this->ordersToCouponssScheduledForDeletion = clone $this->collOrdersToCouponss;
                $this->ordersToCouponssScheduledForDeletion->clear();
            }
            $this->ordersToCouponssScheduledForDeletion[]= clone $ordersToCoupons;
            $ordersToCoupons->setCoupons(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Coupons is new, it will return
     * an empty collection; or if this Coupons has previously
     * been saved, it will retrieve related OrdersToCouponss from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Coupons.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|OrdersToCoupons[] List of OrdersToCoupons objects
     */
    public function getOrdersToCouponssJoinOrders($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = OrdersToCouponsQuery::create(null, $criteria);
        $query->joinWith('Orders', $join_behavior);

        return $this->getOrdersToCouponss($query, $con);
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
        $this->code = null;
        $this->amount = null;
        $this->amount_type = null;
        $this->min_purchase_amount = null;
        $this->currency_code = null;
        $this->active_from = null;
        $this->active_to = null;
        $this->is_active = null;
        $this->is_used = null;
        $this->is_reusable = null;
        $this->created_at = null;
        $this->updated_at = null;
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
            if ($this->collOrdersToCouponss) {
                foreach ($this->collOrdersToCouponss as $o) {
                    $o->clearAllReferences($deep);
                }
            }

            $this->alreadyInClearAllReferencesDeep = false;
        } // if ($deep)

        if ($this->collOrdersToCouponss instanceof PropelCollection) {
            $this->collOrdersToCouponss->clearIterator();
        }
        $this->collOrdersToCouponss = null;
    }

    /**
     * return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(CouponsPeer::DEFAULT_STRING_FORMAT);
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

    // timestampable behavior

    /**
     * Mark the current object so that the update date doesn't get updated during next save
     *
     * @return     Coupons The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged()
    {
        $this->modifiedColumns[] = CouponsPeer::UPDATED_AT;

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
