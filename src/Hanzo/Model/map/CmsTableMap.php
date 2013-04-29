<?php

namespace Hanzo\Model\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'cms' table.
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
class CmsTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'src.Hanzo.Model.map.CmsTableMap';

    /**
     * Initialize the table attributes, columns and validators
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('cms');
        $this->setPhpName('Cms');
        $this->setClassname('Hanzo\\Model\\Cms');
        $this->setPackage('src.Hanzo.Model');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('PARENT_ID', 'ParentId', 'INTEGER', 'cms', 'ID', false, null, null);
        $this->addForeignKey('CMS_THREAD_ID', 'CmsThreadId', 'INTEGER', 'cms_thread', 'ID', true, null, null);
        $this->addColumn('SORT', 'Sort', 'INTEGER', true, null, 1);
        $this->addColumn('TYPE', 'Type', 'VARCHAR', true, 255, 'cms');
        $this->addColumn('CREATED_AT', 'CreatedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('UPDATED_AT', 'UpdatedAt', 'TIMESTAMP', false, null, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('CmsThread', 'Hanzo\\Model\\CmsThread', RelationMap::MANY_TO_ONE, array('cms_thread_id' => 'id', ), 'CASCADE', 'CASCADE');
        $this->addRelation('CmsRelatedByParentId', 'Hanzo\\Model\\Cms', RelationMap::MANY_TO_ONE, array('parent_id' => 'id', ), 'CASCADE', null);
        $this->addRelation('CmsRelatedById', 'Hanzo\\Model\\Cms', RelationMap::ONE_TO_MANY, array('id' => 'parent_id', ), 'CASCADE', null, 'CmssRelatedById');
        $this->addRelation('CmsI18n', 'Hanzo\\Model\\CmsI18n', RelationMap::ONE_TO_MANY, array('id' => 'id', ), 'CASCADE', null, 'CmsI18ns');
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
            'i18n' => array('i18n_table' => '%TABLE%_i18n', 'i18n_phpname' => '%PHPNAME%I18n', 'i18n_columns' => 'title, path, old_path, content, settings, is_restricted, is_active', 'locale_column' => 'locale', 'default_locale' => 'da_DK', 'locale_alias' => '', ),
            'timestampable' => array('create_column' => 'created_at', 'update_column' => 'updated_at', 'disable_updated_at' => 'false', ),
        );
    } // getBehaviors()

} // CmsTableMap
