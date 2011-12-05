<?php

namespace Hanzo\Model\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'domains_settings' table.
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
class DomainsSettingsTableMap extends TableMap
{

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'src.Hanzo.Model.map.DomainsSettingsTableMap';

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
		$this->setName('domains_settings');
		$this->setPhpName('DomainsSettings');
		$this->setClassname('Hanzo\\Model\\DomainsSettings');
		$this->setPackage('src.Hanzo.Model');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
		$this->addForeignKey('DOMAIN_KEY', 'DomainKey', 'VARCHAR', 'domains', 'DOMAIN_KEY', true, 12, null);
		$this->addColumn('C_KEY', 'CKey', 'VARCHAR', true, 128, null);
		$this->addColumn('NS', 'Ns', 'VARCHAR', true, 64, null);
		$this->addColumn('C_VALUE', 'CValue', 'LONGVARCHAR', true, null, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
		$this->addRelation('Domains', 'Hanzo\\Model\\Domains', RelationMap::MANY_TO_ONE, array('domain_key' => 'domain_key', ), 'CASCADE', null);
	} // buildRelations()

} // DomainsSettingsTableMap
