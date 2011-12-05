<?php

namespace Hanzo\Model\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'products_washing_instructions' table.
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
class ProductsWashingInstructionsTableMap extends TableMap
{

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'src.Hanzo.Model.map.ProductsWashingInstructionsTableMap';

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
		$this->setName('products_washing_instructions');
		$this->setPhpName('ProductsWashingInstructions');
		$this->setClassname('Hanzo\\Model\\ProductsWashingInstructions');
		$this->setPackage('src.Hanzo.Model');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
		$this->addColumn('CODE', 'Code', 'INTEGER', true, null, null);
		$this->addForeignKey('LANGUAGES_ID', 'LanguagesId', 'INTEGER', 'languages', 'ID', true, null, null);
		$this->addColumn('DESCRIPTION', 'Description', 'LONGVARCHAR', true, null, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
		$this->addRelation('Languages', 'Hanzo\\Model\\Languages', RelationMap::MANY_TO_ONE, array('languages_id' => 'id', ), 'CASCADE', null);
	} // buildRelations()

} // ProductsWashingInstructionsTableMap
