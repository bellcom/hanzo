<?php

namespace Hanzo\Model\om;

use \BaseObject;
use \BasePeer;
use \Criteria;
use \DateTime;
use \DateTimeZone;
use \Exception;
use \PDO;
use \Persistent;
use \Propel;
use \PropelCollection;
use \PropelDateTime;
use \PropelException;
use \PropelObjectCollection;
use \PropelPDO;
use Hanzo\Model\Customers;
use Hanzo\Model\CustomersQuery;
use Hanzo\Model\EventsParticipants;
use Hanzo\Model\EventsParticipantsQuery;
use Hanzo\Model\EventsPeer;
use Hanzo\Model\EventsQuery;
use Hanzo\Model\Orders;
use Hanzo\Model\OrdersQuery;

/**
 * Base class that represents a row from the 'events' table.
 *
 * 
 *
 * @package    propel.generator.src.Hanzo.Model.om
 */
abstract class BaseEvents extends BaseObject  implements Persistent
{

	/**
	 * Peer class name
	 */
	const PEER = 'Hanzo\\Model\\EventsPeer';

	/**
	 * The Peer class.
	 * Instance provides a convenient way of calling static methods on a class
	 * that calling code may not be able to identify.
	 * @var        EventsPeer
	 */
	protected static $peer;

	/**
	 * The flag var to prevent infinit loop in deep copy
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
	 * The value for the key field.
	 * @var        string
	 */
	protected $key;

	/**
	 * The value for the consultants_id field.
	 * @var        int
	 */
	protected $consultants_id;

	/**
	 * The value for the customers_id field.
	 * @var        int
	 */
	protected $customers_id;

	/**
	 * The value for the event_date field.
	 * @var        string
	 */
	protected $event_date;

	/**
	 * The value for the host field.
	 * @var        string
	 */
	protected $host;

	/**
	 * The value for the address_line_1 field.
	 * @var        string
	 */
	protected $address_line_1;

	/**
	 * The value for the address_line_2 field.
	 * @var        string
	 */
	protected $address_line_2;

	/**
	 * The value for the postal_code field.
	 * @var        string
	 */
	protected $postal_code;

	/**
	 * The value for the city field.
	 * @var        string
	 */
	protected $city;

	/**
	 * The value for the phone field.
	 * @var        string
	 */
	protected $phone;

	/**
	 * The value for the email field.
	 * @var        string
	 */
	protected $email;

	/**
	 * The value for the description field.
	 * @var        string
	 */
	protected $description;

	/**
	 * The value for the type field.
	 * Note: this column has a database default value of: 'AR'
	 * @var        string
	 */
	protected $type;

	/**
	 * The value for the is_open field.
	 * Note: this column has a database default value of: false
	 * @var        boolean
	 */
	protected $is_open;

	/**
	 * The value for the notify_hostess field.
	 * Note: this column has a database default value of: true
	 * @var        boolean
	 */
	protected $notify_hostess;

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
	 * @var        Customers
	 */
	protected $aCustomersRelatedByConsultantsId;

	/**
	 * @var        Customers
	 */
	protected $aCustomersRelatedByCustomersId;

	/**
	 * @var        array EventsParticipants[] Collection to store aggregation of EventsParticipants objects.
	 */
	protected $collEventsParticipantss;

	/**
	 * @var        array Orders[] Collection to store aggregation of Orders objects.
	 */
	protected $collOrderss;

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
	 * An array of objects scheduled for deletion.
	 * @var		array
	 */
	protected $eventsParticipantssScheduledForDeletion = null;

	/**
	 * An array of objects scheduled for deletion.
	 * @var		array
	 */
	protected $orderssScheduledForDeletion = null;

	/**
	 * Applies default values to this object.
	 * This method should be called from the object's constructor (or
	 * equivalent initialization method).
	 * @see        __construct()
	 */
	public function applyDefaultValues()
	{
		$this->type = 'AR';
		$this->is_open = false;
		$this->notify_hostess = true;
	}

	/**
	 * Initializes internal state of BaseEvents object.
	 * @see        applyDefaults()
	 */
	public function __construct()
	{
		parent::__construct();
		$this->applyDefaultValues();
	}

	/**
	 * Get the [id] column value.
	 * 
	 * @return     int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get the [code] column value.
	 * 
	 * @return     string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * Get the [key] column value.
	 * 
	 * @return     string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * Get the [consultants_id] column value.
	 * 
	 * @return     int
	 */
	public function getConsultantsId()
	{
		return $this->consultants_id;
	}

	/**
	 * Get the [customers_id] column value.
	 * 
	 * @return     int
	 */
	public function getCustomersId()
	{
		return $this->customers_id;
	}

	/**
	 * Get the [optionally formatted] temporal [event_date] column value.
	 * 
	 *
	 * @param      string $format The date/time format string (either date()-style or strftime()-style).
	 *							If format is NULL, then the raw DateTime object will be returned.
	 * @return     mixed Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
	 * @throws     PropelException - if unable to parse/validate the date/time value.
	 */
	public function getEventDate($format = 'Y-m-d H:i:s')
	{
		if ($this->event_date === null) {
			return null;
		}


		if ($this->event_date === '0000-00-00 00:00:00') {
			// while technically this is not a default value of NULL,
			// this seems to be closest in meaning.
			return null;
		} else {
			try {
				$dt = new DateTime($this->event_date);
			} catch (Exception $x) {
				throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->event_date, true), $x);
			}
		}

		if ($format === null) {
			// Because propel.useDateTimeClass is TRUE, we return a DateTime object.
			return $dt;
		} elseif (strpos($format, '%') !== false) {
			return strftime($format, $dt->format('U'));
		} else {
			return $dt->format($format);
		}
	}

	/**
	 * Get the [host] column value.
	 * 
	 * @return     string
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * Get the [address_line_1] column value.
	 * 
	 * @return     string
	 */
	public function getAddressLine1()
	{
		return $this->address_line_1;
	}

	/**
	 * Get the [address_line_2] column value.
	 * 
	 * @return     string
	 */
	public function getAddressLine2()
	{
		return $this->address_line_2;
	}

	/**
	 * Get the [postal_code] column value.
	 * 
	 * @return     string
	 */
	public function getPostalCode()
	{
		return $this->postal_code;
	}

	/**
	 * Get the [city] column value.
	 * 
	 * @return     string
	 */
	public function getCity()
	{
		return $this->city;
	}

	/**
	 * Get the [phone] column value.
	 * 
	 * @return     string
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * Get the [email] column value.
	 * 
	 * @return     string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Get the [description] column value.
	 * 
	 * @return     string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Get the [type] column value.
	 * 
	 * @return     string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Get the [is_open] column value.
	 * 
	 * @return     boolean
	 */
	public function getIsOpen()
	{
		return $this->is_open;
	}

	/**
	 * Get the [notify_hostess] column value.
	 * 
	 * @return     boolean
	 */
	public function getNotifyHostess()
	{
		return $this->notify_hostess;
	}

	/**
	 * Get the [optionally formatted] temporal [created_at] column value.
	 * 
	 *
	 * @param      string $format The date/time format string (either date()-style or strftime()-style).
	 *							If format is NULL, then the raw DateTime object will be returned.
	 * @return     mixed Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
	 * @throws     PropelException - if unable to parse/validate the date/time value.
	 */
	public function getCreatedAt($format = 'Y-m-d H:i:s')
	{
		if ($this->created_at === null) {
			return null;
		}


		if ($this->created_at === '0000-00-00 00:00:00') {
			// while technically this is not a default value of NULL,
			// this seems to be closest in meaning.
			return null;
		} else {
			try {
				$dt = new DateTime($this->created_at);
			} catch (Exception $x) {
				throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->created_at, true), $x);
			}
		}

		if ($format === null) {
			// Because propel.useDateTimeClass is TRUE, we return a DateTime object.
			return $dt;
		} elseif (strpos($format, '%') !== false) {
			return strftime($format, $dt->format('U'));
		} else {
			return $dt->format($format);
		}
	}

	/**
	 * Get the [optionally formatted] temporal [updated_at] column value.
	 * 
	 *
	 * @param      string $format The date/time format string (either date()-style or strftime()-style).
	 *							If format is NULL, then the raw DateTime object will be returned.
	 * @return     mixed Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
	 * @throws     PropelException - if unable to parse/validate the date/time value.
	 */
	public function getUpdatedAt($format = 'Y-m-d H:i:s')
	{
		if ($this->updated_at === null) {
			return null;
		}


		if ($this->updated_at === '0000-00-00 00:00:00') {
			// while technically this is not a default value of NULL,
			// this seems to be closest in meaning.
			return null;
		} else {
			try {
				$dt = new DateTime($this->updated_at);
			} catch (Exception $x) {
				throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->updated_at, true), $x);
			}
		}

		if ($format === null) {
			// Because propel.useDateTimeClass is TRUE, we return a DateTime object.
			return $dt;
		} elseif (strpos($format, '%') !== false) {
			return strftime($format, $dt->format('U'));
		} else {
			return $dt->format($format);
		}
	}

	/**
	 * Set the value of [id] column.
	 * 
	 * @param      int $v new value
	 * @return     Events The current object (for fluent API support)
	 */
	public function setId($v)
	{
		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->id !== $v) {
			$this->id = $v;
			$this->modifiedColumns[] = EventsPeer::ID;
		}

		return $this;
	} // setId()

	/**
	 * Set the value of [code] column.
	 * 
	 * @param      string $v new value
	 * @return     Events The current object (for fluent API support)
	 */
	public function setCode($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->code !== $v) {
			$this->code = $v;
			$this->modifiedColumns[] = EventsPeer::CODE;
		}

		return $this;
	} // setCode()

	/**
	 * Set the value of [key] column.
	 * 
	 * @param      string $v new value
	 * @return     Events The current object (for fluent API support)
	 */
	public function setKey($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->key !== $v) {
			$this->key = $v;
			$this->modifiedColumns[] = EventsPeer::KEY;
		}

		return $this;
	} // setKey()

	/**
	 * Set the value of [consultants_id] column.
	 * 
	 * @param      int $v new value
	 * @return     Events The current object (for fluent API support)
	 */
	public function setConsultantsId($v)
	{
		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->consultants_id !== $v) {
			$this->consultants_id = $v;
			$this->modifiedColumns[] = EventsPeer::CONSULTANTS_ID;
		}

		if ($this->aCustomersRelatedByConsultantsId !== null && $this->aCustomersRelatedByConsultantsId->getId() !== $v) {
			$this->aCustomersRelatedByConsultantsId = null;
		}

		return $this;
	} // setConsultantsId()

	/**
	 * Set the value of [customers_id] column.
	 * 
	 * @param      int $v new value
	 * @return     Events The current object (for fluent API support)
	 */
	public function setCustomersId($v)
	{
		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->customers_id !== $v) {
			$this->customers_id = $v;
			$this->modifiedColumns[] = EventsPeer::CUSTOMERS_ID;
		}

		if ($this->aCustomersRelatedByCustomersId !== null && $this->aCustomersRelatedByCustomersId->getId() !== $v) {
			$this->aCustomersRelatedByCustomersId = null;
		}

		return $this;
	} // setCustomersId()

	/**
	 * Sets the value of [event_date] column to a normalized version of the date/time value specified.
	 * 
	 * @param      mixed $v string, integer (timestamp), or DateTime value.
	 *               Empty strings are treated as NULL.
	 * @return     Events The current object (for fluent API support)
	 */
	public function setEventDate($v)
	{
		$dt = PropelDateTime::newInstance($v, null, 'DateTime');
		if ($this->event_date !== null || $dt !== null) {
			$currentDateAsString = ($this->event_date !== null && $tmpDt = new DateTime($this->event_date)) ? $tmpDt->format('Y-m-d H:i:s') : null;
			$newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
			if ($currentDateAsString !== $newDateAsString) {
				$this->event_date = $newDateAsString;
				$this->modifiedColumns[] = EventsPeer::EVENT_DATE;
			}
		} // if either are not null

		return $this;
	} // setEventDate()

	/**
	 * Set the value of [host] column.
	 * 
	 * @param      string $v new value
	 * @return     Events The current object (for fluent API support)
	 */
	public function setHost($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->host !== $v) {
			$this->host = $v;
			$this->modifiedColumns[] = EventsPeer::HOST;
		}

		return $this;
	} // setHost()

	/**
	 * Set the value of [address_line_1] column.
	 * 
	 * @param      string $v new value
	 * @return     Events The current object (for fluent API support)
	 */
	public function setAddressLine1($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->address_line_1 !== $v) {
			$this->address_line_1 = $v;
			$this->modifiedColumns[] = EventsPeer::ADDRESS_LINE_1;
		}

		return $this;
	} // setAddressLine1()

	/**
	 * Set the value of [address_line_2] column.
	 * 
	 * @param      string $v new value
	 * @return     Events The current object (for fluent API support)
	 */
	public function setAddressLine2($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->address_line_2 !== $v) {
			$this->address_line_2 = $v;
			$this->modifiedColumns[] = EventsPeer::ADDRESS_LINE_2;
		}

		return $this;
	} // setAddressLine2()

	/**
	 * Set the value of [postal_code] column.
	 * 
	 * @param      string $v new value
	 * @return     Events The current object (for fluent API support)
	 */
	public function setPostalCode($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->postal_code !== $v) {
			$this->postal_code = $v;
			$this->modifiedColumns[] = EventsPeer::POSTAL_CODE;
		}

		return $this;
	} // setPostalCode()

	/**
	 * Set the value of [city] column.
	 * 
	 * @param      string $v new value
	 * @return     Events The current object (for fluent API support)
	 */
	public function setCity($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->city !== $v) {
			$this->city = $v;
			$this->modifiedColumns[] = EventsPeer::CITY;
		}

		return $this;
	} // setCity()

	/**
	 * Set the value of [phone] column.
	 * 
	 * @param      string $v new value
	 * @return     Events The current object (for fluent API support)
	 */
	public function setPhone($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->phone !== $v) {
			$this->phone = $v;
			$this->modifiedColumns[] = EventsPeer::PHONE;
		}

		return $this;
	} // setPhone()

	/**
	 * Set the value of [email] column.
	 * 
	 * @param      string $v new value
	 * @return     Events The current object (for fluent API support)
	 */
	public function setEmail($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->email !== $v) {
			$this->email = $v;
			$this->modifiedColumns[] = EventsPeer::EMAIL;
		}

		return $this;
	} // setEmail()

	/**
	 * Set the value of [description] column.
	 * 
	 * @param      string $v new value
	 * @return     Events The current object (for fluent API support)
	 */
	public function setDescription($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->description !== $v) {
			$this->description = $v;
			$this->modifiedColumns[] = EventsPeer::DESCRIPTION;
		}

		return $this;
	} // setDescription()

	/**
	 * Set the value of [type] column.
	 * 
	 * @param      string $v new value
	 * @return     Events The current object (for fluent API support)
	 */
	public function setType($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->type !== $v) {
			$this->type = $v;
			$this->modifiedColumns[] = EventsPeer::TYPE;
		}

		return $this;
	} // setType()

	/**
	 * Sets the value of the [is_open] column.
	 * Non-boolean arguments are converted using the following rules:
	 *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
	 *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
	 * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
	 * 
	 * @param      boolean|integer|string $v The new value
	 * @return     Events The current object (for fluent API support)
	 */
	public function setIsOpen($v)
	{
		if ($v !== null) {
			if (is_string($v)) {
				$v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
			} else {
				$v = (boolean) $v;
			}
		}

		if ($this->is_open !== $v) {
			$this->is_open = $v;
			$this->modifiedColumns[] = EventsPeer::IS_OPEN;
		}

		return $this;
	} // setIsOpen()

	/**
	 * Sets the value of the [notify_hostess] column.
	 * Non-boolean arguments are converted using the following rules:
	 *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
	 *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
	 * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
	 * 
	 * @param      boolean|integer|string $v The new value
	 * @return     Events The current object (for fluent API support)
	 */
	public function setNotifyHostess($v)
	{
		if ($v !== null) {
			if (is_string($v)) {
				$v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
			} else {
				$v = (boolean) $v;
			}
		}

		if ($this->notify_hostess !== $v) {
			$this->notify_hostess = $v;
			$this->modifiedColumns[] = EventsPeer::NOTIFY_HOSTESS;
		}

		return $this;
	} // setNotifyHostess()

	/**
	 * Sets the value of [created_at] column to a normalized version of the date/time value specified.
	 * 
	 * @param      mixed $v string, integer (timestamp), or DateTime value.
	 *               Empty strings are treated as NULL.
	 * @return     Events The current object (for fluent API support)
	 */
	public function setCreatedAt($v)
	{
		$dt = PropelDateTime::newInstance($v, null, 'DateTime');
		if ($this->created_at !== null || $dt !== null) {
			$currentDateAsString = ($this->created_at !== null && $tmpDt = new DateTime($this->created_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
			$newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
			if ($currentDateAsString !== $newDateAsString) {
				$this->created_at = $newDateAsString;
				$this->modifiedColumns[] = EventsPeer::CREATED_AT;
			}
		} // if either are not null

		return $this;
	} // setCreatedAt()

	/**
	 * Sets the value of [updated_at] column to a normalized version of the date/time value specified.
	 * 
	 * @param      mixed $v string, integer (timestamp), or DateTime value.
	 *               Empty strings are treated as NULL.
	 * @return     Events The current object (for fluent API support)
	 */
	public function setUpdatedAt($v)
	{
		$dt = PropelDateTime::newInstance($v, null, 'DateTime');
		if ($this->updated_at !== null || $dt !== null) {
			$currentDateAsString = ($this->updated_at !== null && $tmpDt = new DateTime($this->updated_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
			$newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
			if ($currentDateAsString !== $newDateAsString) {
				$this->updated_at = $newDateAsString;
				$this->modifiedColumns[] = EventsPeer::UPDATED_AT;
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
	 * @return     boolean Whether the columns in this object are only been set with default values.
	 */
	public function hasOnlyDefaultValues()
	{
			if ($this->type !== 'AR') {
				return false;
			}

			if ($this->is_open !== false) {
				return false;
			}

			if ($this->notify_hostess !== true) {
				return false;
			}

		// otherwise, everything was equal, so return TRUE
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
	 * @param      array $row The row returned by PDOStatement->fetch(PDO::FETCH_NUM)
	 * @param      int $startcol 0-based offset column which indicates which restultset column to start with.
	 * @param      boolean $rehydrate Whether this object is being re-hydrated from the database.
	 * @return     int next starting column
	 * @throws     PropelException  - Any caught Exception will be rewrapped as a PropelException.
	 */
	public function hydrate($row, $startcol = 0, $rehydrate = false)
	{
		try {

			$this->id = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
			$this->code = ($row[$startcol + 1] !== null) ? (string) $row[$startcol + 1] : null;
			$this->key = ($row[$startcol + 2] !== null) ? (string) $row[$startcol + 2] : null;
			$this->consultants_id = ($row[$startcol + 3] !== null) ? (int) $row[$startcol + 3] : null;
			$this->customers_id = ($row[$startcol + 4] !== null) ? (int) $row[$startcol + 4] : null;
			$this->event_date = ($row[$startcol + 5] !== null) ? (string) $row[$startcol + 5] : null;
			$this->host = ($row[$startcol + 6] !== null) ? (string) $row[$startcol + 6] : null;
			$this->address_line_1 = ($row[$startcol + 7] !== null) ? (string) $row[$startcol + 7] : null;
			$this->address_line_2 = ($row[$startcol + 8] !== null) ? (string) $row[$startcol + 8] : null;
			$this->postal_code = ($row[$startcol + 9] !== null) ? (string) $row[$startcol + 9] : null;
			$this->city = ($row[$startcol + 10] !== null) ? (string) $row[$startcol + 10] : null;
			$this->phone = ($row[$startcol + 11] !== null) ? (string) $row[$startcol + 11] : null;
			$this->email = ($row[$startcol + 12] !== null) ? (string) $row[$startcol + 12] : null;
			$this->description = ($row[$startcol + 13] !== null) ? (string) $row[$startcol + 13] : null;
			$this->type = ($row[$startcol + 14] !== null) ? (string) $row[$startcol + 14] : null;
			$this->is_open = ($row[$startcol + 15] !== null) ? (boolean) $row[$startcol + 15] : null;
			$this->notify_hostess = ($row[$startcol + 16] !== null) ? (boolean) $row[$startcol + 16] : null;
			$this->created_at = ($row[$startcol + 17] !== null) ? (string) $row[$startcol + 17] : null;
			$this->updated_at = ($row[$startcol + 18] !== null) ? (string) $row[$startcol + 18] : null;
			$this->resetModified();

			$this->setNew(false);

			if ($rehydrate) {
				$this->ensureConsistency();
			}

			return $startcol + 19; // 19 = EventsPeer::NUM_HYDRATE_COLUMNS.

		} catch (Exception $e) {
			throw new PropelException("Error populating Events object", $e);
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
	 * @throws     PropelException
	 */
	public function ensureConsistency()
	{

		if ($this->aCustomersRelatedByConsultantsId !== null && $this->consultants_id !== $this->aCustomersRelatedByConsultantsId->getId()) {
			$this->aCustomersRelatedByConsultantsId = null;
		}
		if ($this->aCustomersRelatedByCustomersId !== null && $this->customers_id !== $this->aCustomersRelatedByCustomersId->getId()) {
			$this->aCustomersRelatedByCustomersId = null;
		}
	} // ensureConsistency

	/**
	 * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
	 *
	 * This will only work if the object has been saved and has a valid primary key set.
	 *
	 * @param      boolean $deep (optional) Whether to also de-associated any related objects.
	 * @param      PropelPDO $con (optional) The PropelPDO connection to use.
	 * @return     void
	 * @throws     PropelException - if this object is deleted, unsaved or doesn't have pk match in db
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
			$con = Propel::getConnection(EventsPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		// We don't need to alter the object instance pool; we're just modifying this instance
		// already in the pool.

		$stmt = EventsPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
		$row = $stmt->fetch(PDO::FETCH_NUM);
		$stmt->closeCursor();
		if (!$row) {
			throw new PropelException('Cannot find matching row in the database to reload object values.');
		}
		$this->hydrate($row, 0, true); // rehydrate

		if ($deep) {  // also de-associate any related objects?

			$this->aCustomersRelatedByConsultantsId = null;
			$this->aCustomersRelatedByCustomersId = null;
			$this->collEventsParticipantss = null;

			$this->collOrderss = null;

		} // if (deep)
	}

	/**
	 * Removes this object from datastore and sets delete attribute.
	 *
	 * @param      PropelPDO $con
	 * @return     void
	 * @throws     PropelException
	 * @see        BaseObject::setDeleted()
	 * @see        BaseObject::isDeleted()
	 */
	public function delete(PropelPDO $con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("This object has already been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(EventsPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		$con->beginTransaction();
		try {
			$deleteQuery = EventsQuery::create()
				->filterByPrimaryKey($this->getPrimaryKey());
			$ret = $this->preDelete($con);
			if ($ret) {
				$deleteQuery->delete($con);
				$this->postDelete($con);
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
	 * @param      PropelPDO $con
	 * @return     int The number of rows affected by this insert/update and any referring fk objects' save() operations.
	 * @throws     PropelException
	 * @see        doSave()
	 */
	public function save(PropelPDO $con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("You cannot save an object that has been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(EventsPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		$con->beginTransaction();
		$isInsert = $this->isNew();
		try {
			$ret = $this->preSave($con);
			if ($isInsert) {
				$ret = $ret && $this->preInsert($con);
				// timestampable behavior
				if (!$this->isColumnModified(EventsPeer::CREATED_AT)) {
					$this->setCreatedAt(time());
				}
				if (!$this->isColumnModified(EventsPeer::UPDATED_AT)) {
					$this->setUpdatedAt(time());
				}
			} else {
				$ret = $ret && $this->preUpdate($con);
				// timestampable behavior
				if ($this->isModified() && !$this->isColumnModified(EventsPeer::UPDATED_AT)) {
					$this->setUpdatedAt(time());
				}
			}
			if ($ret) {
				$affectedRows = $this->doSave($con);
				if ($isInsert) {
					$this->postInsert($con);
				} else {
					$this->postUpdate($con);
				}
				$this->postSave($con);
				EventsPeer::addInstanceToPool($this);
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
	 * @param      PropelPDO $con
	 * @return     int The number of rows affected by this insert/update and any referring fk objects' save() operations.
	 * @throws     PropelException
	 * @see        save()
	 */
	protected function doSave(PropelPDO $con)
	{
		$affectedRows = 0; // initialize var to track total num of affected rows
		if (!$this->alreadyInSave) {
			$this->alreadyInSave = true;

			// We call the save method on the following object(s) if they
			// were passed to this object by their coresponding set
			// method.  This object relates to these object(s) by a
			// foreign key reference.

			if ($this->aCustomersRelatedByConsultantsId !== null) {
				if ($this->aCustomersRelatedByConsultantsId->isModified() || $this->aCustomersRelatedByConsultantsId->isNew()) {
					$affectedRows += $this->aCustomersRelatedByConsultantsId->save($con);
				}
				$this->setCustomersRelatedByConsultantsId($this->aCustomersRelatedByConsultantsId);
			}

			if ($this->aCustomersRelatedByCustomersId !== null) {
				if ($this->aCustomersRelatedByCustomersId->isModified() || $this->aCustomersRelatedByCustomersId->isNew()) {
					$affectedRows += $this->aCustomersRelatedByCustomersId->save($con);
				}
				$this->setCustomersRelatedByCustomersId($this->aCustomersRelatedByCustomersId);
			}

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

			if ($this->eventsParticipantssScheduledForDeletion !== null) {
				if (!$this->eventsParticipantssScheduledForDeletion->isEmpty()) {
					EventsParticipantsQuery::create()
						->filterByPrimaryKeys($this->eventsParticipantssScheduledForDeletion->getPrimaryKeys(false))
						->delete($con);
					$this->eventsParticipantssScheduledForDeletion = null;
				}
			}

			if ($this->collEventsParticipantss !== null) {
				foreach ($this->collEventsParticipantss as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->orderssScheduledForDeletion !== null) {
				if (!$this->orderssScheduledForDeletion->isEmpty()) {
					OrdersQuery::create()
						->filterByPrimaryKeys($this->orderssScheduledForDeletion->getPrimaryKeys(false))
						->delete($con);
					$this->orderssScheduledForDeletion = null;
				}
			}

			if ($this->collOrderss !== null) {
				foreach ($this->collOrderss as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
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
	 * @param      PropelPDO $con
	 *
	 * @throws     PropelException
	 * @see        doSave()
	 */
	protected function doInsert(PropelPDO $con)
	{
		$modifiedColumns = array();
		$index = 0;

		$this->modifiedColumns[] = EventsPeer::ID;

		 // check the columns in natural order for more readable SQL queries
		if ($this->isColumnModified(EventsPeer::ID)) {
			$modifiedColumns[':p' . $index++]  = '`ID`';
		}
		if ($this->isColumnModified(EventsPeer::CODE)) {
			$modifiedColumns[':p' . $index++]  = '`CODE`';
		}
		if ($this->isColumnModified(EventsPeer::KEY)) {
			$modifiedColumns[':p' . $index++]  = '`KEY`';
		}
		if ($this->isColumnModified(EventsPeer::CONSULTANTS_ID)) {
			$modifiedColumns[':p' . $index++]  = '`CONSULTANTS_ID`';
		}
		if ($this->isColumnModified(EventsPeer::CUSTOMERS_ID)) {
			$modifiedColumns[':p' . $index++]  = '`CUSTOMERS_ID`';
		}
		if ($this->isColumnModified(EventsPeer::EVENT_DATE)) {
			$modifiedColumns[':p' . $index++]  = '`EVENT_DATE`';
		}
		if ($this->isColumnModified(EventsPeer::HOST)) {
			$modifiedColumns[':p' . $index++]  = '`HOST`';
		}
		if ($this->isColumnModified(EventsPeer::ADDRESS_LINE_1)) {
			$modifiedColumns[':p' . $index++]  = '`ADDRESS_LINE_1`';
		}
		if ($this->isColumnModified(EventsPeer::ADDRESS_LINE_2)) {
			$modifiedColumns[':p' . $index++]  = '`ADDRESS_LINE_2`';
		}
		if ($this->isColumnModified(EventsPeer::POSTAL_CODE)) {
			$modifiedColumns[':p' . $index++]  = '`POSTAL_CODE`';
		}
		if ($this->isColumnModified(EventsPeer::CITY)) {
			$modifiedColumns[':p' . $index++]  = '`CITY`';
		}
		if ($this->isColumnModified(EventsPeer::PHONE)) {
			$modifiedColumns[':p' . $index++]  = '`PHONE`';
		}
		if ($this->isColumnModified(EventsPeer::EMAIL)) {
			$modifiedColumns[':p' . $index++]  = '`EMAIL`';
		}
		if ($this->isColumnModified(EventsPeer::DESCRIPTION)) {
			$modifiedColumns[':p' . $index++]  = '`DESCRIPTION`';
		}
		if ($this->isColumnModified(EventsPeer::TYPE)) {
			$modifiedColumns[':p' . $index++]  = '`TYPE`';
		}
		if ($this->isColumnModified(EventsPeer::IS_OPEN)) {
			$modifiedColumns[':p' . $index++]  = '`IS_OPEN`';
		}
		if ($this->isColumnModified(EventsPeer::NOTIFY_HOSTESS)) {
			$modifiedColumns[':p' . $index++]  = '`NOTIFY_HOSTESS`';
		}
		if ($this->isColumnModified(EventsPeer::CREATED_AT)) {
			$modifiedColumns[':p' . $index++]  = '`CREATED_AT`';
		}
		if ($this->isColumnModified(EventsPeer::UPDATED_AT)) {
			$modifiedColumns[':p' . $index++]  = '`UPDATED_AT`';
		}

		$sql = sprintf(
			'INSERT INTO `events` (%s) VALUES (%s)',
			implode(', ', $modifiedColumns),
			implode(', ', array_keys($modifiedColumns))
		);

		try {
			$stmt = $con->prepare($sql);
			foreach ($modifiedColumns as $identifier => $columnName) {
				switch ($columnName) {
					case '`ID`':
						$stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);
						break;
					case '`CODE`':
						$stmt->bindValue($identifier, $this->code, PDO::PARAM_STR);
						break;
					case '`KEY`':
						$stmt->bindValue($identifier, $this->key, PDO::PARAM_STR);
						break;
					case '`CONSULTANTS_ID`':
						$stmt->bindValue($identifier, $this->consultants_id, PDO::PARAM_INT);
						break;
					case '`CUSTOMERS_ID`':
						$stmt->bindValue($identifier, $this->customers_id, PDO::PARAM_INT);
						break;
					case '`EVENT_DATE`':
						$stmt->bindValue($identifier, $this->event_date, PDO::PARAM_STR);
						break;
					case '`HOST`':
						$stmt->bindValue($identifier, $this->host, PDO::PARAM_STR);
						break;
					case '`ADDRESS_LINE_1`':
						$stmt->bindValue($identifier, $this->address_line_1, PDO::PARAM_STR);
						break;
					case '`ADDRESS_LINE_2`':
						$stmt->bindValue($identifier, $this->address_line_2, PDO::PARAM_STR);
						break;
					case '`POSTAL_CODE`':
						$stmt->bindValue($identifier, $this->postal_code, PDO::PARAM_STR);
						break;
					case '`CITY`':
						$stmt->bindValue($identifier, $this->city, PDO::PARAM_STR);
						break;
					case '`PHONE`':
						$stmt->bindValue($identifier, $this->phone, PDO::PARAM_STR);
						break;
					case '`EMAIL`':
						$stmt->bindValue($identifier, $this->email, PDO::PARAM_STR);
						break;
					case '`DESCRIPTION`':
						$stmt->bindValue($identifier, $this->description, PDO::PARAM_STR);
						break;
					case '`TYPE`':
						$stmt->bindValue($identifier, $this->type, PDO::PARAM_STR);
						break;
					case '`IS_OPEN`':
						$stmt->bindValue($identifier, (int) $this->is_open, PDO::PARAM_INT);
						break;
					case '`NOTIFY_HOSTESS`':
						$stmt->bindValue($identifier, (int) $this->notify_hostess, PDO::PARAM_INT);
						break;
					case '`CREATED_AT`':
						$stmt->bindValue($identifier, $this->created_at, PDO::PARAM_STR);
						break;
					case '`UPDATED_AT`':
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
		if ($pk !== null) {
			$this->setId($pk);
		}

		$this->setNew(false);
	}

	/**
	 * Update the row in the database.
	 *
	 * @param      PropelPDO $con
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
	 * @return     array ValidationFailed[]
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
	 * @param      mixed $columns Column name or an array of column names.
	 * @return     boolean Whether all columns pass validation.
	 * @see        doValidate()
	 * @see        getValidationFailures()
	 */
	public function validate($columns = null)
	{
		$res = $this->doValidate($columns);
		if ($res === true) {
			$this->validationFailures = array();
			return true;
		} else {
			$this->validationFailures = $res;
			return false;
		}
	}

	/**
	 * This function performs the validation work for complex object models.
	 *
	 * In addition to checking the current object, all related objects will
	 * also be validated.  If all pass then <code>true</code> is returned; otherwise
	 * an aggreagated array of ValidationFailed objects will be returned.
	 *
	 * @param      array $columns Array of column names to validate.
	 * @return     mixed <code>true</code> if all validations pass; array of <code>ValidationFailed</code> objets otherwise.
	 */
	protected function doValidate($columns = null)
	{
		if (!$this->alreadyInValidation) {
			$this->alreadyInValidation = true;
			$retval = null;

			$failureMap = array();


			// We call the validate method on the following object(s) if they
			// were passed to this object by their coresponding set
			// method.  This object relates to these object(s) by a
			// foreign key reference.

			if ($this->aCustomersRelatedByConsultantsId !== null) {
				if (!$this->aCustomersRelatedByConsultantsId->validate($columns)) {
					$failureMap = array_merge($failureMap, $this->aCustomersRelatedByConsultantsId->getValidationFailures());
				}
			}

			if ($this->aCustomersRelatedByCustomersId !== null) {
				if (!$this->aCustomersRelatedByCustomersId->validate($columns)) {
					$failureMap = array_merge($failureMap, $this->aCustomersRelatedByCustomersId->getValidationFailures());
				}
			}


			if (($retval = EventsPeer::doValidate($this, $columns)) !== true) {
				$failureMap = array_merge($failureMap, $retval);
			}


				if ($this->collEventsParticipantss !== null) {
					foreach ($this->collEventsParticipantss as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collOrderss !== null) {
					foreach ($this->collOrderss as $referrerFK) {
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
	 * @param      string $name name
	 * @param      string $type The type of fieldname the $name is of:
	 *                     one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
	 *                     BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
	 * @return     mixed Value of field.
	 */
	public function getByName($name, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = EventsPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		$field = $this->getByPosition($pos);
		return $field;
	}

	/**
	 * Retrieves a field from the object by Position as specified in the xml schema.
	 * Zero-based.
	 *
	 * @param      int $pos position in xml schema
	 * @return     mixed Value of field at $pos
	 */
	public function getByPosition($pos)
	{
		switch($pos) {
			case 0:
				return $this->getId();
				break;
			case 1:
				return $this->getCode();
				break;
			case 2:
				return $this->getKey();
				break;
			case 3:
				return $this->getConsultantsId();
				break;
			case 4:
				return $this->getCustomersId();
				break;
			case 5:
				return $this->getEventDate();
				break;
			case 6:
				return $this->getHost();
				break;
			case 7:
				return $this->getAddressLine1();
				break;
			case 8:
				return $this->getAddressLine2();
				break;
			case 9:
				return $this->getPostalCode();
				break;
			case 10:
				return $this->getCity();
				break;
			case 11:
				return $this->getPhone();
				break;
			case 12:
				return $this->getEmail();
				break;
			case 13:
				return $this->getDescription();
				break;
			case 14:
				return $this->getType();
				break;
			case 15:
				return $this->getIsOpen();
				break;
			case 16:
				return $this->getNotifyHostess();
				break;
			case 17:
				return $this->getCreatedAt();
				break;
			case 18:
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
	 * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
	 * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
	 * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
	 *
	 * @return    array an associative array containing the field names (as keys) and field values
	 */
	public function toArray($keyType = BasePeer::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
	{
		if (isset($alreadyDumpedObjects['Events'][$this->getPrimaryKey()])) {
			return '*RECURSION*';
		}
		$alreadyDumpedObjects['Events'][$this->getPrimaryKey()] = true;
		$keys = EventsPeer::getFieldNames($keyType);
		$result = array(
			$keys[0] => $this->getId(),
			$keys[1] => $this->getCode(),
			$keys[2] => $this->getKey(),
			$keys[3] => $this->getConsultantsId(),
			$keys[4] => $this->getCustomersId(),
			$keys[5] => $this->getEventDate(),
			$keys[6] => $this->getHost(),
			$keys[7] => $this->getAddressLine1(),
			$keys[8] => $this->getAddressLine2(),
			$keys[9] => $this->getPostalCode(),
			$keys[10] => $this->getCity(),
			$keys[11] => $this->getPhone(),
			$keys[12] => $this->getEmail(),
			$keys[13] => $this->getDescription(),
			$keys[14] => $this->getType(),
			$keys[15] => $this->getIsOpen(),
			$keys[16] => $this->getNotifyHostess(),
			$keys[17] => $this->getCreatedAt(),
			$keys[18] => $this->getUpdatedAt(),
		);
		if ($includeForeignObjects) {
			if (null !== $this->aCustomersRelatedByConsultantsId) {
				$result['CustomersRelatedByConsultantsId'] = $this->aCustomersRelatedByConsultantsId->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
			}
			if (null !== $this->aCustomersRelatedByCustomersId) {
				$result['CustomersRelatedByCustomersId'] = $this->aCustomersRelatedByCustomersId->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
			}
			if (null !== $this->collEventsParticipantss) {
				$result['EventsParticipantss'] = $this->collEventsParticipantss->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
			}
			if (null !== $this->collOrderss) {
				$result['Orderss'] = $this->collOrderss->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
			}
		}
		return $result;
	}

	/**
	 * Sets a field from the object by name passed in as a string.
	 *
	 * @param      string $name peer name
	 * @param      mixed $value field value
	 * @param      string $type The type of fieldname the $name is of:
	 *                     one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
	 *                     BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
	 * @return     void
	 */
	public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = EventsPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		return $this->setByPosition($pos, $value);
	}

	/**
	 * Sets a field from the object by Position as specified in the xml schema.
	 * Zero-based.
	 *
	 * @param      int $pos position in xml schema
	 * @param      mixed $value field value
	 * @return     void
	 */
	public function setByPosition($pos, $value)
	{
		switch($pos) {
			case 0:
				$this->setId($value);
				break;
			case 1:
				$this->setCode($value);
				break;
			case 2:
				$this->setKey($value);
				break;
			case 3:
				$this->setConsultantsId($value);
				break;
			case 4:
				$this->setCustomersId($value);
				break;
			case 5:
				$this->setEventDate($value);
				break;
			case 6:
				$this->setHost($value);
				break;
			case 7:
				$this->setAddressLine1($value);
				break;
			case 8:
				$this->setAddressLine2($value);
				break;
			case 9:
				$this->setPostalCode($value);
				break;
			case 10:
				$this->setCity($value);
				break;
			case 11:
				$this->setPhone($value);
				break;
			case 12:
				$this->setEmail($value);
				break;
			case 13:
				$this->setDescription($value);
				break;
			case 14:
				$this->setType($value);
				break;
			case 15:
				$this->setIsOpen($value);
				break;
			case 16:
				$this->setNotifyHostess($value);
				break;
			case 17:
				$this->setCreatedAt($value);
				break;
			case 18:
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
	 * The default key type is the column's phpname (e.g. 'AuthorId')
	 *
	 * @param      array  $arr     An array to populate the object from.
	 * @param      string $keyType The type of keys the array uses.
	 * @return     void
	 */
	public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
	{
		$keys = EventsPeer::getFieldNames($keyType);

		if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
		if (array_key_exists($keys[1], $arr)) $this->setCode($arr[$keys[1]]);
		if (array_key_exists($keys[2], $arr)) $this->setKey($arr[$keys[2]]);
		if (array_key_exists($keys[3], $arr)) $this->setConsultantsId($arr[$keys[3]]);
		if (array_key_exists($keys[4], $arr)) $this->setCustomersId($arr[$keys[4]]);
		if (array_key_exists($keys[5], $arr)) $this->setEventDate($arr[$keys[5]]);
		if (array_key_exists($keys[6], $arr)) $this->setHost($arr[$keys[6]]);
		if (array_key_exists($keys[7], $arr)) $this->setAddressLine1($arr[$keys[7]]);
		if (array_key_exists($keys[8], $arr)) $this->setAddressLine2($arr[$keys[8]]);
		if (array_key_exists($keys[9], $arr)) $this->setPostalCode($arr[$keys[9]]);
		if (array_key_exists($keys[10], $arr)) $this->setCity($arr[$keys[10]]);
		if (array_key_exists($keys[11], $arr)) $this->setPhone($arr[$keys[11]]);
		if (array_key_exists($keys[12], $arr)) $this->setEmail($arr[$keys[12]]);
		if (array_key_exists($keys[13], $arr)) $this->setDescription($arr[$keys[13]]);
		if (array_key_exists($keys[14], $arr)) $this->setType($arr[$keys[14]]);
		if (array_key_exists($keys[15], $arr)) $this->setIsOpen($arr[$keys[15]]);
		if (array_key_exists($keys[16], $arr)) $this->setNotifyHostess($arr[$keys[16]]);
		if (array_key_exists($keys[17], $arr)) $this->setCreatedAt($arr[$keys[17]]);
		if (array_key_exists($keys[18], $arr)) $this->setUpdatedAt($arr[$keys[18]]);
	}

	/**
	 * Build a Criteria object containing the values of all modified columns in this object.
	 *
	 * @return     Criteria The Criteria object containing all modified values.
	 */
	public function buildCriteria()
	{
		$criteria = new Criteria(EventsPeer::DATABASE_NAME);

		if ($this->isColumnModified(EventsPeer::ID)) $criteria->add(EventsPeer::ID, $this->id);
		if ($this->isColumnModified(EventsPeer::CODE)) $criteria->add(EventsPeer::CODE, $this->code);
		if ($this->isColumnModified(EventsPeer::KEY)) $criteria->add(EventsPeer::KEY, $this->key);
		if ($this->isColumnModified(EventsPeer::CONSULTANTS_ID)) $criteria->add(EventsPeer::CONSULTANTS_ID, $this->consultants_id);
		if ($this->isColumnModified(EventsPeer::CUSTOMERS_ID)) $criteria->add(EventsPeer::CUSTOMERS_ID, $this->customers_id);
		if ($this->isColumnModified(EventsPeer::EVENT_DATE)) $criteria->add(EventsPeer::EVENT_DATE, $this->event_date);
		if ($this->isColumnModified(EventsPeer::HOST)) $criteria->add(EventsPeer::HOST, $this->host);
		if ($this->isColumnModified(EventsPeer::ADDRESS_LINE_1)) $criteria->add(EventsPeer::ADDRESS_LINE_1, $this->address_line_1);
		if ($this->isColumnModified(EventsPeer::ADDRESS_LINE_2)) $criteria->add(EventsPeer::ADDRESS_LINE_2, $this->address_line_2);
		if ($this->isColumnModified(EventsPeer::POSTAL_CODE)) $criteria->add(EventsPeer::POSTAL_CODE, $this->postal_code);
		if ($this->isColumnModified(EventsPeer::CITY)) $criteria->add(EventsPeer::CITY, $this->city);
		if ($this->isColumnModified(EventsPeer::PHONE)) $criteria->add(EventsPeer::PHONE, $this->phone);
		if ($this->isColumnModified(EventsPeer::EMAIL)) $criteria->add(EventsPeer::EMAIL, $this->email);
		if ($this->isColumnModified(EventsPeer::DESCRIPTION)) $criteria->add(EventsPeer::DESCRIPTION, $this->description);
		if ($this->isColumnModified(EventsPeer::TYPE)) $criteria->add(EventsPeer::TYPE, $this->type);
		if ($this->isColumnModified(EventsPeer::IS_OPEN)) $criteria->add(EventsPeer::IS_OPEN, $this->is_open);
		if ($this->isColumnModified(EventsPeer::NOTIFY_HOSTESS)) $criteria->add(EventsPeer::NOTIFY_HOSTESS, $this->notify_hostess);
		if ($this->isColumnModified(EventsPeer::CREATED_AT)) $criteria->add(EventsPeer::CREATED_AT, $this->created_at);
		if ($this->isColumnModified(EventsPeer::UPDATED_AT)) $criteria->add(EventsPeer::UPDATED_AT, $this->updated_at);

		return $criteria;
	}

	/**
	 * Builds a Criteria object containing the primary key for this object.
	 *
	 * Unlike buildCriteria() this method includes the primary key values regardless
	 * of whether or not they have been modified.
	 *
	 * @return     Criteria The Criteria object containing value(s) for primary key(s).
	 */
	public function buildPkeyCriteria()
	{
		$criteria = new Criteria(EventsPeer::DATABASE_NAME);
		$criteria->add(EventsPeer::ID, $this->id);

		return $criteria;
	}

	/**
	 * Returns the primary key for this object (row).
	 * @return     int
	 */
	public function getPrimaryKey()
	{
		return $this->getId();
	}

	/**
	 * Generic method to set the primary key (id column).
	 *
	 * @param      int $key Primary key.
	 * @return     void
	 */
	public function setPrimaryKey($key)
	{
		$this->setId($key);
	}

	/**
	 * Returns true if the primary key for this object is null.
	 * @return     boolean
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
	 * @param      object $copyObj An object of Events (or compatible) type.
	 * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
	 * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
	 * @throws     PropelException
	 */
	public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
	{
		$copyObj->setCode($this->getCode());
		$copyObj->setKey($this->getKey());
		$copyObj->setConsultantsId($this->getConsultantsId());
		$copyObj->setCustomersId($this->getCustomersId());
		$copyObj->setEventDate($this->getEventDate());
		$copyObj->setHost($this->getHost());
		$copyObj->setAddressLine1($this->getAddressLine1());
		$copyObj->setAddressLine2($this->getAddressLine2());
		$copyObj->setPostalCode($this->getPostalCode());
		$copyObj->setCity($this->getCity());
		$copyObj->setPhone($this->getPhone());
		$copyObj->setEmail($this->getEmail());
		$copyObj->setDescription($this->getDescription());
		$copyObj->setType($this->getType());
		$copyObj->setIsOpen($this->getIsOpen());
		$copyObj->setNotifyHostess($this->getNotifyHostess());
		$copyObj->setCreatedAt($this->getCreatedAt());
		$copyObj->setUpdatedAt($this->getUpdatedAt());

		if ($deepCopy && !$this->startCopy) {
			// important: temporarily setNew(false) because this affects the behavior of
			// the getter/setter methods for fkey referrer objects.
			$copyObj->setNew(false);
			// store object hash to prevent cycle
			$this->startCopy = true;

			foreach ($this->getEventsParticipantss() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addEventsParticipants($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getOrderss() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addOrders($relObj->copy($deepCopy));
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
	 * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
	 * @return     Events Clone of current object.
	 * @throws     PropelException
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
	 * @return     EventsPeer
	 */
	public function getPeer()
	{
		if (self::$peer === null) {
			self::$peer = new EventsPeer();
		}
		return self::$peer;
	}

	/**
	 * Declares an association between this object and a Customers object.
	 *
	 * @param      Customers $v
	 * @return     Events The current object (for fluent API support)
	 * @throws     PropelException
	 */
	public function setCustomersRelatedByConsultantsId(Customers $v = null)
	{
		if ($v === null) {
			$this->setConsultantsId(NULL);
		} else {
			$this->setConsultantsId($v->getId());
		}

		$this->aCustomersRelatedByConsultantsId = $v;

		// Add binding for other direction of this n:n relationship.
		// If this object has already been added to the Customers object, it will not be re-added.
		if ($v !== null) {
			$v->addEventsRelatedByConsultantsId($this);
		}

		return $this;
	}


	/**
	 * Get the associated Customers object
	 *
	 * @param      PropelPDO Optional Connection object.
	 * @return     Customers The associated Customers object.
	 * @throws     PropelException
	 */
	public function getCustomersRelatedByConsultantsId(PropelPDO $con = null)
	{
		if ($this->aCustomersRelatedByConsultantsId === null && ($this->consultants_id !== null)) {
			$this->aCustomersRelatedByConsultantsId = CustomersQuery::create()->findPk($this->consultants_id, $con);
			/* The following can be used additionally to
				guarantee the related object contains a reference
				to this object.  This level of coupling may, however, be
				undesirable since it could result in an only partially populated collection
				in the referenced object.
				$this->aCustomersRelatedByConsultantsId->addEventssRelatedByConsultantsId($this);
			 */
		}
		return $this->aCustomersRelatedByConsultantsId;
	}

	/**
	 * Declares an association between this object and a Customers object.
	 *
	 * @param      Customers $v
	 * @return     Events The current object (for fluent API support)
	 * @throws     PropelException
	 */
	public function setCustomersRelatedByCustomersId(Customers $v = null)
	{
		if ($v === null) {
			$this->setCustomersId(NULL);
		} else {
			$this->setCustomersId($v->getId());
		}

		$this->aCustomersRelatedByCustomersId = $v;

		// Add binding for other direction of this n:n relationship.
		// If this object has already been added to the Customers object, it will not be re-added.
		if ($v !== null) {
			$v->addEventsRelatedByCustomersId($this);
		}

		return $this;
	}


	/**
	 * Get the associated Customers object
	 *
	 * @param      PropelPDO Optional Connection object.
	 * @return     Customers The associated Customers object.
	 * @throws     PropelException
	 */
	public function getCustomersRelatedByCustomersId(PropelPDO $con = null)
	{
		if ($this->aCustomersRelatedByCustomersId === null && ($this->customers_id !== null)) {
			$this->aCustomersRelatedByCustomersId = CustomersQuery::create()->findPk($this->customers_id, $con);
			/* The following can be used additionally to
				guarantee the related object contains a reference
				to this object.  This level of coupling may, however, be
				undesirable since it could result in an only partially populated collection
				in the referenced object.
				$this->aCustomersRelatedByCustomersId->addEventssRelatedByCustomersId($this);
			 */
		}
		return $this->aCustomersRelatedByCustomersId;
	}


	/**
	 * Initializes a collection based on the name of a relation.
	 * Avoids crafting an 'init[$relationName]s' method name
	 * that wouldn't work when StandardEnglishPluralizer is used.
	 *
	 * @param      string $relationName The name of the relation to initialize
	 * @return     void
	 */
	public function initRelation($relationName)
	{
		if ('EventsParticipants' == $relationName) {
			return $this->initEventsParticipantss();
		}
		if ('Orders' == $relationName) {
			return $this->initOrderss();
		}
	}

	/**
	 * Clears out the collEventsParticipantss collection
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addEventsParticipantss()
	 */
	public function clearEventsParticipantss()
	{
		$this->collEventsParticipantss = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collEventsParticipantss collection.
	 *
	 * By default this just sets the collEventsParticipantss collection to an empty array (like clearcollEventsParticipantss());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @param      boolean $overrideExisting If set to true, the method call initializes
	 *                                        the collection even if it is not empty
	 *
	 * @return     void
	 */
	public function initEventsParticipantss($overrideExisting = true)
	{
		if (null !== $this->collEventsParticipantss && !$overrideExisting) {
			return;
		}
		$this->collEventsParticipantss = new PropelObjectCollection();
		$this->collEventsParticipantss->setModel('EventsParticipants');
	}

	/**
	 * Gets an array of EventsParticipants objects which contain a foreign key that references this object.
	 *
	 * If the $criteria is not null, it is used to always fetch the results from the database.
	 * Otherwise the results are fetched from the database the first time, then cached.
	 * Next time the same method is called without $criteria, the cached collection is returned.
	 * If this Events is new, it will return
	 * an empty collection or the current collection; the criteria is ignored on a new object.
	 *
	 * @param      Criteria $criteria optional Criteria object to narrow the query
	 * @param      PropelPDO $con optional connection object
	 * @return     PropelCollection|array EventsParticipants[] List of EventsParticipants objects
	 * @throws     PropelException
	 */
	public function getEventsParticipantss($criteria = null, PropelPDO $con = null)
	{
		if(null === $this->collEventsParticipantss || null !== $criteria) {
			if ($this->isNew() && null === $this->collEventsParticipantss) {
				// return empty collection
				$this->initEventsParticipantss();
			} else {
				$collEventsParticipantss = EventsParticipantsQuery::create(null, $criteria)
					->filterByEvents($this)
					->find($con);
				if (null !== $criteria) {
					return $collEventsParticipantss;
				}
				$this->collEventsParticipantss = $collEventsParticipantss;
			}
		}
		return $this->collEventsParticipantss;
	}

	/**
	 * Sets a collection of EventsParticipants objects related by a one-to-many relationship
	 * to the current object.
	 * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
	 * and new objects from the given Propel collection.
	 *
	 * @param      PropelCollection $eventsParticipantss A Propel collection.
	 * @param      PropelPDO $con Optional connection object
	 */
	public function setEventsParticipantss(PropelCollection $eventsParticipantss, PropelPDO $con = null)
	{
		$this->eventsParticipantssScheduledForDeletion = $this->getEventsParticipantss(new Criteria(), $con)->diff($eventsParticipantss);

		foreach ($eventsParticipantss as $eventsParticipants) {
			// Fix issue with collection modified by reference
			if ($eventsParticipants->isNew()) {
				$eventsParticipants->setEvents($this);
			}
			$this->addEventsParticipants($eventsParticipants);
		}

		$this->collEventsParticipantss = $eventsParticipantss;
	}

	/**
	 * Returns the number of related EventsParticipants objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related EventsParticipants objects.
	 * @throws     PropelException
	 */
	public function countEventsParticipantss(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if(null === $this->collEventsParticipantss || null !== $criteria) {
			if ($this->isNew() && null === $this->collEventsParticipantss) {
				return 0;
			} else {
				$query = EventsParticipantsQuery::create(null, $criteria);
				if($distinct) {
					$query->distinct();
				}
				return $query
					->filterByEvents($this)
					->count($con);
			}
		} else {
			return count($this->collEventsParticipantss);
		}
	}

	/**
	 * Method called to associate a EventsParticipants object to this object
	 * through the EventsParticipants foreign key attribute.
	 *
	 * @param      EventsParticipants $l EventsParticipants
	 * @return     Events The current object (for fluent API support)
	 */
	public function addEventsParticipants(EventsParticipants $l)
	{
		if ($this->collEventsParticipantss === null) {
			$this->initEventsParticipantss();
		}
		if (!$this->collEventsParticipantss->contains($l)) { // only add it if the **same** object is not already associated
			$this->doAddEventsParticipants($l);
		}

		return $this;
	}

	/**
	 * @param	EventsParticipants $eventsParticipants The eventsParticipants object to add.
	 */
	protected function doAddEventsParticipants($eventsParticipants)
	{
		$this->collEventsParticipantss[]= $eventsParticipants;
		$eventsParticipants->setEvents($this);
	}

	/**
	 * Clears out the collOrderss collection
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addOrderss()
	 */
	public function clearOrderss()
	{
		$this->collOrderss = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collOrderss collection.
	 *
	 * By default this just sets the collOrderss collection to an empty array (like clearcollOrderss());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @param      boolean $overrideExisting If set to true, the method call initializes
	 *                                        the collection even if it is not empty
	 *
	 * @return     void
	 */
	public function initOrderss($overrideExisting = true)
	{
		if (null !== $this->collOrderss && !$overrideExisting) {
			return;
		}
		$this->collOrderss = new PropelObjectCollection();
		$this->collOrderss->setModel('Orders');
	}

	/**
	 * Gets an array of Orders objects which contain a foreign key that references this object.
	 *
	 * If the $criteria is not null, it is used to always fetch the results from the database.
	 * Otherwise the results are fetched from the database the first time, then cached.
	 * Next time the same method is called without $criteria, the cached collection is returned.
	 * If this Events is new, it will return
	 * an empty collection or the current collection; the criteria is ignored on a new object.
	 *
	 * @param      Criteria $criteria optional Criteria object to narrow the query
	 * @param      PropelPDO $con optional connection object
	 * @return     PropelCollection|array Orders[] List of Orders objects
	 * @throws     PropelException
	 */
	public function getOrderss($criteria = null, PropelPDO $con = null)
	{
		if(null === $this->collOrderss || null !== $criteria) {
			if ($this->isNew() && null === $this->collOrderss) {
				// return empty collection
				$this->initOrderss();
			} else {
				$collOrderss = OrdersQuery::create(null, $criteria)
					->filterByEvents($this)
					->find($con);
				if (null !== $criteria) {
					return $collOrderss;
				}
				$this->collOrderss = $collOrderss;
			}
		}
		return $this->collOrderss;
	}

	/**
	 * Sets a collection of Orders objects related by a one-to-many relationship
	 * to the current object.
	 * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
	 * and new objects from the given Propel collection.
	 *
	 * @param      PropelCollection $orderss A Propel collection.
	 * @param      PropelPDO $con Optional connection object
	 */
	public function setOrderss(PropelCollection $orderss, PropelPDO $con = null)
	{
		$this->orderssScheduledForDeletion = $this->getOrderss(new Criteria(), $con)->diff($orderss);

		foreach ($orderss as $orders) {
			// Fix issue with collection modified by reference
			if ($orders->isNew()) {
				$orders->setEvents($this);
			}
			$this->addOrders($orders);
		}

		$this->collOrderss = $orderss;
	}

	/**
	 * Returns the number of related Orders objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related Orders objects.
	 * @throws     PropelException
	 */
	public function countOrderss(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if(null === $this->collOrderss || null !== $criteria) {
			if ($this->isNew() && null === $this->collOrderss) {
				return 0;
			} else {
				$query = OrdersQuery::create(null, $criteria);
				if($distinct) {
					$query->distinct();
				}
				return $query
					->filterByEvents($this)
					->count($con);
			}
		} else {
			return count($this->collOrderss);
		}
	}

	/**
	 * Method called to associate a Orders object to this object
	 * through the Orders foreign key attribute.
	 *
	 * @param      Orders $l Orders
	 * @return     Events The current object (for fluent API support)
	 */
	public function addOrders(Orders $l)
	{
		if ($this->collOrderss === null) {
			$this->initOrderss();
		}
		if (!$this->collOrderss->contains($l)) { // only add it if the **same** object is not already associated
			$this->doAddOrders($l);
		}

		return $this;
	}

	/**
	 * @param	Orders $orders The orders object to add.
	 */
	protected function doAddOrders($orders)
	{
		$this->collOrderss[]= $orders;
		$orders->setEvents($this);
	}


	/**
	 * If this collection has already been initialized with
	 * an identical criteria, it returns the collection.
	 * Otherwise if this Events is new, it will return
	 * an empty collection; or if this Events has previously
	 * been saved, it will retrieve related Orderss from storage.
	 *
	 * This method is protected by default in order to keep the public
	 * api reasonable.  You can provide public methods for those you
	 * actually need in Events.
	 *
	 * @param      Criteria $criteria optional Criteria object to narrow the query
	 * @param      PropelPDO $con optional connection object
	 * @param      string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
	 * @return     PropelCollection|array Orders[] List of Orders objects
	 */
	public function getOrderssJoinCustomers($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
		$query = OrdersQuery::create(null, $criteria);
		$query->joinWith('Customers', $join_behavior);

		return $this->getOrderss($query, $con);
	}


	/**
	 * If this collection has already been initialized with
	 * an identical criteria, it returns the collection.
	 * Otherwise if this Events is new, it will return
	 * an empty collection; or if this Events has previously
	 * been saved, it will retrieve related Orderss from storage.
	 *
	 * This method is protected by default in order to keep the public
	 * api reasonable.  You can provide public methods for those you
	 * actually need in Events.
	 *
	 * @param      Criteria $criteria optional Criteria object to narrow the query
	 * @param      PropelPDO $con optional connection object
	 * @param      string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
	 * @return     PropelCollection|array Orders[] List of Orders objects
	 */
	public function getOrderssJoinCountriesRelatedByBillingCountriesId($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
		$query = OrdersQuery::create(null, $criteria);
		$query->joinWith('CountriesRelatedByBillingCountriesId', $join_behavior);

		return $this->getOrderss($query, $con);
	}


	/**
	 * If this collection has already been initialized with
	 * an identical criteria, it returns the collection.
	 * Otherwise if this Events is new, it will return
	 * an empty collection; or if this Events has previously
	 * been saved, it will retrieve related Orderss from storage.
	 *
	 * This method is protected by default in order to keep the public
	 * api reasonable.  You can provide public methods for those you
	 * actually need in Events.
	 *
	 * @param      Criteria $criteria optional Criteria object to narrow the query
	 * @param      PropelPDO $con optional connection object
	 * @param      string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
	 * @return     PropelCollection|array Orders[] List of Orders objects
	 */
	public function getOrderssJoinCountriesRelatedByDeliveryCountriesId($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
		$query = OrdersQuery::create(null, $criteria);
		$query->joinWith('CountriesRelatedByDeliveryCountriesId', $join_behavior);

		return $this->getOrderss($query, $con);
	}

	/**
	 * Clears the current object and sets all attributes to their default values
	 */
	public function clear()
	{
		$this->id = null;
		$this->code = null;
		$this->key = null;
		$this->consultants_id = null;
		$this->customers_id = null;
		$this->event_date = null;
		$this->host = null;
		$this->address_line_1 = null;
		$this->address_line_2 = null;
		$this->postal_code = null;
		$this->city = null;
		$this->phone = null;
		$this->email = null;
		$this->description = null;
		$this->type = null;
		$this->is_open = null;
		$this->notify_hostess = null;
		$this->created_at = null;
		$this->updated_at = null;
		$this->alreadyInSave = false;
		$this->alreadyInValidation = false;
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
	 * when using Propel in certain daemon or large-volumne/high-memory operations.
	 *
	 * @param      boolean $deep Whether to also clear the references on all referrer objects.
	 */
	public function clearAllReferences($deep = false)
	{
		if ($deep) {
			if ($this->collEventsParticipantss) {
				foreach ($this->collEventsParticipantss as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collOrderss) {
				foreach ($this->collOrderss as $o) {
					$o->clearAllReferences($deep);
				}
			}
		} // if ($deep)

		if ($this->collEventsParticipantss instanceof PropelCollection) {
			$this->collEventsParticipantss->clearIterator();
		}
		$this->collEventsParticipantss = null;
		if ($this->collOrderss instanceof PropelCollection) {
			$this->collOrderss->clearIterator();
		}
		$this->collOrderss = null;
		$this->aCustomersRelatedByConsultantsId = null;
		$this->aCustomersRelatedByCustomersId = null;
	}

	/**
	 * Return the string representation of this object
	 *
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->exportTo(EventsPeer::DEFAULT_STRING_FORMAT);
	}

	// timestampable behavior
	
	/**
	 * Mark the current object so that the update date doesn't get updated during next save
	 *
	 * @return     Events The current object (for fluent API support)
	 */
	public function keepUpdateDateUnchanged()
	{
		$this->modifiedColumns[] = EventsPeer::UPDATED_AT;
		return $this;
	}

} // BaseEvents
