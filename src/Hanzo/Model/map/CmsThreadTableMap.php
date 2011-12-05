<?php

namespace Hanzo\Model\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'cms_thread' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    propel.generator.src.Hanzo.Model.map
 */
class CmsThreadTableMap extends TableMap
{

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'src.Hanzo.Model.map.CmsThreadTableMap';

	/**
	 * Initialize the table attributes, columns and validators
	 * Relations are not initialized by this method since they are lazy loaded
	 *
	 * @return     void
	 * @throws     PropelException
	 */
	public function initialize()
	{
		// attributes
		$this->setName('cms_thread');
		$this->setPhpName('CmsThread');
		$this->setClassname('Hanzo\\Model\\CmsThread');
		$this->setPackage('src.Hanzo.Model');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
		$this->addColumn('IS_ACTIVE', 'IsActive', 'BOOLEAN', true, 1, true);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
		$this->addRelation('Cms', 'Hanzo\\Model\\Cms', RelationMap::ONE_TO_MANY, array('id' => 'cms_thread_id', ), 'CASCADE', null, 'Cmss');
		$this->addRelation('CmsThreadI18n', 'Hanzo\\Model\\CmsThreadI18n', RelationMap::ONE_TO_MANY, array('id' => 'id', ), 'CASCADE', null, 'CmsThreadI18ns');
	} // buildRelations()

	/**
	 *
	 * Gets the list of behaviors registered for this table
	 *
	 * @return array Associative array (name => parameters) of behaviors
	 */
	public function getBehaviors()
	{
		return array(
			'i18n' => array('i18n_table' => '%TABLE%_i18n', 'i18n_phpname' => '%PHPNAME%I18n', 'i18n_columns' => 'title', 'locale_column' => 'locale', 'default_locale' => '', 'locale_alias' => '', ),
		);
	} // getBehaviors()

} // CmsThreadTableMap
