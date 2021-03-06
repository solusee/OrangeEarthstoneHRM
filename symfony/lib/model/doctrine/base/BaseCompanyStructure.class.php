<?php

/**
 * BaseCompanyStructure
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $title
 * @property string $description
 * @property integer $lft
 * @property integer $rgt
 * @property integer $id
 * @property integer $parnt
 * @property string $loc_code
 * @property string $dept_id
 * @property Doctrine_Collection $employees
 * @property Location $location
 * @property Doctrine_Collection $PerformanceReview
 * 
 * @method string              getTitle()             Returns the current record's "title" value
 * @method string              getDescription()       Returns the current record's "description" value
 * @method integer             getLft()               Returns the current record's "lft" value
 * @method integer             getRgt()               Returns the current record's "rgt" value
 * @method integer             getId()                Returns the current record's "id" value
 * @method integer             getParnt()             Returns the current record's "parnt" value
 * @method string              getLocCode()           Returns the current record's "loc_code" value
 * @method string              getDeptId()            Returns the current record's "dept_id" value
 * @method Doctrine_Collection getEmployees()         Returns the current record's "employees" collection
 * @method Location            getLocation()          Returns the current record's "location" value
 * @method Doctrine_Collection getPerformanceReview() Returns the current record's "PerformanceReview" collection
 * @method CompanyStructure    setTitle()             Sets the current record's "title" value
 * @method CompanyStructure    setDescription()       Sets the current record's "description" value
 * @method CompanyStructure    setLft()               Sets the current record's "lft" value
 * @method CompanyStructure    setRgt()               Sets the current record's "rgt" value
 * @method CompanyStructure    setId()                Sets the current record's "id" value
 * @method CompanyStructure    setParnt()             Sets the current record's "parnt" value
 * @method CompanyStructure    setLocCode()           Sets the current record's "loc_code" value
 * @method CompanyStructure    setDeptId()            Sets the current record's "dept_id" value
 * @method CompanyStructure    setEmployees()         Sets the current record's "employees" collection
 * @method CompanyStructure    setLocation()          Sets the current record's "location" value
 * @method CompanyStructure    setPerformanceReview() Sets the current record's "PerformanceReview" collection
 * 
 * @package    orangehrm
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseCompanyStructure extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('hs_hr_compstructtree');
        $this->hasColumn('title', 'string', 2147483647, array(
             'type' => 'string',
             'notnull' => true,
             'length' => 2147483647,
             ));
        $this->hasColumn('description', 'string', 2147483647, array(
             'type' => 'string',
             'notnull' => true,
             'length' => 2147483647,
             ));
        $this->hasColumn('lft', 'integer', 4, array(
             'type' => 'integer',
             'default' => '0',
             'notnull' => true,
             'length' => 4,
             ));
        $this->hasColumn('rgt', 'integer', 4, array(
             'type' => 'integer',
             'default' => '0',
             'notnull' => true,
             'length' => 4,
             ));
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'primary' => true,
             'length' => 4,
             ));
        $this->hasColumn('parnt', 'integer', 4, array(
             'type' => 'integer',
             'default' => '0',
             'notnull' => true,
             'length' => 4,
             ));
        $this->hasColumn('loc_code', 'string', 13, array(
             'type' => 'string',
             'length' => 13,
             ));
        $this->hasColumn('dept_id', 'string', 32, array(
             'type' => 'string',
             'length' => 32,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('Employee as employees', array(
             'local' => 'id',
             'foreign' => 'work_station'));

        $this->hasOne('Location as location', array(
             'local' => 'loc_code',
             'foreign' => 'loc_code'));

        $this->hasMany('PerformanceReview', array(
             'local' => 'id',
             'foreign' => 'subDivisionId'));
    }
}