<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 *
 */

// Call ProjectActivityTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "ProjectActivityTest::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once "testConf.php";
require_once ROOT_PATH."/lib/confs/Conf.php";
require_once ROOT_PATH."/lib/models/eimadmin/ProjectActivity.php";
require_once ROOT_PATH."/lib/common/UniqueIDGenerator.php";

/**
 * Test class for ProjectActivity.
 * Generated by PHPUnit_Util_Skeleton on 2007-07-07 at 17:44:48.
 */
class ProjectActivityTest extends PHPUnit_Framework_TestCase {

    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main() {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("ProjectActivityTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, making sure table is empty and creating database
     * entries needed during test.
     *
     * @access protected
     */
    protected function setUp() {

    	$conf = new Conf();
    	$this->connection = mysql_connect($conf->dbhost.":".$conf->dbport, $conf->dbuser, $conf->dbpass);
        mysql_select_db($conf->dbname);

		// NOTE: TRUNCATE TABLE resets AUTO_INCREMENT values and starts counting from the beginning.
		mysql_query("TRUNCATE TABLE `hs_hr_customer`", $this->connection);
		mysql_query("TRUNCATE TABLE `hs_hr_project`", $this->connection);
        mysql_query("TRUNCATE TABLE `hs_hr_project_activity`", $this->connection);

		// Insert a project and customer for use in the test
        mysql_query("INSERT INTO hs_hr_customer(customer_id, name, description, deleted) VALUES(1, 'Test customer', 'description', 0)");
        mysql_query("INSERT INTO hs_hr_customer(customer_id, name, description, deleted) VALUES(0, 'Internal customer', 'description', 0)");
        mysql_query("INSERT INTO hs_hr_project(project_id, customer_id, name, description, deleted) VALUES(0, 0, 'Internal project', 'Internal project', 0)");
        mysql_query("INSERT INTO hs_hr_project(project_id, customer_id, name, description, deleted) VALUES(1, 1, 'Test project 1', 'a test proj 1', 0)");
        mysql_query("INSERT INTO hs_hr_project(project_id, customer_id, name, description, deleted) VALUES(2, 1, 'Test project 2', 'a test proj 2', 0)");
        
		UniqueIDGenerator::getInstance()->resetIDs();
    }

    /**
     * Tears down the fixture, removed database entries created during test.
     *
     * @access protected
     */
    protected function tearDown() {
		mysql_query("TRUNCATE TABLE `hs_hr_project`", $this->connection);
        mysql_query("TRUNCATE TABLE `hs_hr_project_activity`", $this->connection);
		mysql_query("TRUNCATE TABLE `hs_hr_customer`", $this->connection);
		UniqueIDGenerator::getInstance()->resetIDs();
    }

    /**
     * Tests the ProjectActivity constructor
     */
    public function testNew() {

		$activity = new ProjectActivity();
		$this->assertNull($activity->getId(), "Activity Id should be null");
		$this->assertNull($activity->getName(), "Name should be null");
		$this->assertNull($activity->getProjectId(), "Project Id should be null");
		$this->assertFalse($activity->isDeleted(), "Activity was created in deleted state");

		$activity = new ProjectActivity(21);
		$this->assertEquals(21, $activity->getId(), "Activity Id not set in constructor");
		$this->assertNull($activity->getName(), "Name should be null");
		$this->assertNull($activity->getProjectId(), "Project Id should be null");
		$this->assertFalse($activity->isDeleted(), "Activity was created in deleted state");
    }

    /**
     * Tests the save() method.
     */
    public function testSave() {

		// Test that saving an activity without a project ID or a name is not allowed.
		$activity = new ProjectActivity();
		try {
			$activity->save();
			$this->fail("Exception not thrown");
		} catch (ProjectActivityException $e) {
			$this->assertEquals(0, $this->_getNumActivities(), "No rows should be inserted");
		}

		// Test that saving an activity without a project ID is not allowed.
		$activity = new ProjectActivity();
		$activity->setName("Test Project Activity");
		try {
			$activity->save();
			$this->fail("Exception not thrown");
		} catch (ProjectActivityException $e) {
			$this->assertEquals(0, $this->_getNumActivities(), "No rows should be inserted");
		}

		// Test that saving an activity without a name is not allowed.
		$activity = new ProjectActivity();
		$activity->setProjectId(1);
		try {
			$activity->save();
			$this->fail("Exception not thrown");
		} catch (ProjectActivityException $e) {
			$this->assertEquals(0, $this->_getNumActivities(), "No rows should be inserted");
		}

		// Save a valid new activity
		$activity1Id = UniqueIDGenerator::getInstance()->getLastId("hs_hr_project_activity", "activity_id") + 1;

		$activity1 = new ProjectActivity();
		$activity1->setProjectId(1);
		$activity1->setName("Development");
		$activity1->save();

		$this->assertEquals($activity1Id, $activity1->getId(), "activity ID not updated with auto_increment value");

		$result = mysql_query("SELECT * FROM hs_hr_project_activity");
		$this->assertEquals(1, mysql_num_rows($result), "Only one row should be inserted");
		$row = mysql_fetch_assoc($result);
		$this->_checkRow($activity1, $row);

		// Save a second activity.
		$activity2Id = UniqueIDGenerator::getInstance()->getLastId("hs_hr_project_activity", "activity_id") + 1;

		$activity2 = new ProjectActivity();
		$activity2->setProjectId(1);
		$activity2->setName("QA Testing");
		$activity2->save();

		$this->assertEquals($activity2Id, $activity2->getId(), "activity ID not updated with auto_increment value");

		$result = mysql_query("SELECT * FROM hs_hr_project_activity ORDER BY activity_id ASC");
		$this->assertEquals(2, mysql_num_rows($result), "Only one row should be inserted");

		// check both rows
		$this->_checkRow($activity1, mysql_fetch_assoc($result));
		$this->_checkRow($activity2, mysql_fetch_assoc($result));

		// Change attributes and save activity using existing object
		$activity1->setName("Updated activity");
		$activity1->setProjectId(2);
		$activity1->save();
		$this->assertEquals($activity1Id, $activity1->getId(), "activity ID should not change");

		$result = mysql_query("SELECT * FROM hs_hr_project_activity WHERE activity_id = $activity1Id");
		$this->_checkRow($activity1, mysql_fetch_assoc($result));

		// Change attributes and save activity using new object
		$activity3 = new ProjectActivity($activity2Id);
		$activity3->setProjectId(1);
		$activity3->setName("Installing");
		$activity3->save();

		$result = mysql_query("SELECT * FROM hs_hr_project_activity WHERE activity_id = $activity2Id");
		$this->_checkRow($activity3, mysql_fetch_assoc($result));

		// Verify that saving an activity without changes does not throw an exception
		try {
			$activity3->save();
		} catch (ProjectActivityException $e) {
			$this->fail("Saving without changes should not throw an exception");
		}

		// Verify that setting name to null and saving throws an exception
		$activity1->setName(null);
		try {
			$activity1->save();
			$this->fail("Exception not thrown");
		} catch (ProjectActivityException $e) {
			// expected
		}

		// Save an activity for the project 0
		$activity3Id = UniqueIDGenerator::getInstance()->getLastId("hs_hr_project_activity", "activity_id") + 1;

		$activity3 = new ProjectActivity();
		$activity3->setProjectId(0);
		$activity3->setName("Test internal");
		$activity3->save();

		$this->assertEquals($activity3Id, $activity3->getId(), "activity ID not updated with auto_increment value");

		$result = mysql_query("SELECT * FROM hs_hr_project_activity WHERE activity_id = $activity3Id");
		$this->_checkRow($activity3, mysql_fetch_assoc($result));
    }

    /**
     * Test testGetActivityList() method.
     */
    public function testGetActivityList() {

		// Verify that invalid project ids throw exceptions
		try {
			ProjectActivity::getActivityList("");
			$this->fail("Exception not thrown");
		} catch (ProjectActivityException $e) {
			// Expected
		}

		// Verify that invalid project ids throw exceptions
		try {
			ProjectActivity::getActivityList("xfe");
			$this->fail("Exception not thrown");
		} catch (ProjectActivityException $e) {
			// Expected
		}

		// Verify that invalid project ids throw exceptions
		try {
			ProjectActivity::getActivityList(null);
			$this->fail("Exception not thrown");
		} catch (ProjectActivityException $e) {
			// Expected
		}

		// Test with empty table
		$projId = 1;
		$list = ProjectActivity::getActivityList($projId);
		$this->assertType("array", $list);
		$this->assertEquals(0, count($list), "List should be empty");

		$list = ProjectActivity::getActivityList($projId, true);
		$this->assertType("array", $list);
		$this->assertEquals(0, count($list), "List should be empty");

		// create some activities
		$actList = $this->_getTestActivities();
		$this->_createActivites($actList);

		// query
		$projId = 1;
		$list = ProjectActivity::getActivityList($projId);
		$this->assertType("array", $list);
		$this->assertEquals(2, count($list), "2 activities should be returned.");

		foreach ($list as $activity) {
			$this->assertTrue($activity instanceof ProjectActivity, "Should return ProjectActivity objects");

			$id = $activity->getId();
			$this->assertEquals($actList[$id], $activity);
			$this->assertFalse($activity->isDeleted(), "Should not be deleted");
			$this->assertEquals($projId, $activity->getProjectId(), "Project ID not correct");
		}

		// query including deleted
		$projId = 1;
		$list = ProjectActivity::getActivityList($projId, true);
		$this->assertType("array", $list);
		$this->assertEquals(3, count($list), "3 activities should be returned.");

		foreach ($list as $activity) {
			$this->assertTrue($activity instanceof ProjectActivity, "Should return ProjectActivity objects");

			$id = $activity->getId();
			$this->assertEquals($actList[$id], $activity);
			$this->assertEquals($projId, $activity->getProjectId(), "Project ID not correct");
		}
    }

    /**
     * Tests getActivity() method
     */
    public function testGetActivity() {

		// Verify that invalid project ids throw exceptions
		try {
			ProjectActivity::getActivity("");
			$this->fail("Exception not thrown");
		} catch (ProjectActivityException $e) {
			// Expected
		}

		// Verify that invalid project ids throw exceptions
		try {
			ProjectActivity::getActivity("xfe");
			$this->fail("Exception not thrown");
		} catch (ProjectActivityException $e) {
			// Expected
		}

		// Verify that invalid project ids throw exceptions
		try {
			ProjectActivity::getActivity(null);
			$this->fail("Exception not thrown");
		} catch (ProjectActivityException $e) {
			// Expected
		}

    	// non existant activity id.
    	$obj = ProjectActivity::getActivity(1);
    	$this->assertNull($obj);

		// create some activities
		$actList = $this->_getTestActivities();
		$this->_createActivites($actList);

    	$obj = ProjectActivity::getActivity(2);
    	$this->assertNotNull($obj);
    	$this->assertTrue($obj instanceof ProjectActivity);
    	$this->assertEquals($actList[$obj->getId()], $obj);

		// verify that deleted activites are returned as well
    	$obj = ProjectActivity::getActivity(3);
    	$this->assertNotNull($obj);
    	$this->assertTrue($obj instanceof ProjectActivity);
    	$this->assertTrue($obj->isDeleted());
    	$this->assertEquals($actList[$obj->getId()], $obj);

    	// non existant activity id (with entries in table)
    	$obj = ProjectActivity::getActivity(5);
    	$this->assertNull($obj);

    }

    /**
     * test testgetActivitiesWithName() method.
     */
    public function testGetActivitiesWithName() {

		// Verify that invalid project ids throw exceptions
		try {
			ProjectActivity::getActivitiesWithName("", "Test");
			$this->fail("Exception not thrown");
		} catch (ProjectActivityException $e) {
			// Expected
		}

		// Verify that invalid project ids throw exceptions
		try {
			ProjectActivity::getActivitiesWithName("xafd", "Test");
			$this->fail("Exception not thrown");
		} catch (ProjectActivityException $e) {
			// Expected
		}

		// Verify that invalid project ids throw exceptions
		try {
			ProjectActivity::getActivitiesWithName(null, "Test");
			$this->fail("Exception not thrown");
		} catch (ProjectActivityException $e) {
			// Expected
		}

		// Test that activity name is escaped to avoid sql injection.
		// If not, following will throw an error.
		ProjectActivity::getActivitiesWithName(1, "' WHERE xkaf in (SELECT * from xaf)");

    	// non existent name (with empty table)
    	$list = ProjectActivity::getActivitiesWithName(1, "Test activity");
    	$this->assertEquals(0, count($list));

		// create some activities
		$actList = $this->_getTestActivities();
		$this->_createActivites($actList);

    	// non existent name
    	$list = ProjectActivity::getActivitiesWithName(1, "Test activity 2");
    	$this->assertEquals(0, count($list));

		// valid name
    	$list = ProjectActivity::getActivitiesWithName(1, "test 1");
    	$this->assertEquals(1, count($list));
    	$obj = $list[0];
    	$this->assertEquals($actList[$obj->getId()], $obj);

		// verify that deleted activities are not included by default
    	$list = ProjectActivity::getActivitiesWithName(1, "test 3");
    	$this->assertEquals(0, count($list));

		// include deleted activities
    	$list = ProjectActivity::getActivitiesWithName(1, "test 3", true);
    	$this->assertEquals(1, count($list));
    	$obj = $list[0];
    	$this->assertEquals($actList[$obj->getId()], $obj);

		// multiple matches
		mysql_query("UPDATE hs_hr_project_activity SET name = 'test name' where project_id = 1");
    	$list = ProjectActivity::getActivitiesWithName(1, "test name");
    	$this->assertEquals(2, count($list));

    	$list = ProjectActivity::getActivitiesWithName(1, "test name", true);
    	$this->assertEquals(3, count($list));
    }

	/**
	 * Tests the deleteActivities() method.
	 */
	public function testDeleteActivities() {

		$projId = 1;
		$ids = array(1, 2, 3, 4);

		// Verify that invalid project ids throw exceptions
		try {
			ProjectActivity::deleteActivities($ids, "Test");
			$this->fail("Exception not thrown");
		} catch (ProjectActivityException $e) {
			// Expected
		}

		// Verify that invalid project ids throw exceptions
		try {
			ProjectActivity::deleteActivities($ids, "");
			$this->fail("Exception not thrown");
		} catch (ProjectActivityException $e) {
			// Expected
		}

		// Verify that invalid activity ids throw exceptions
		try {
			ProjectActivity::deleteActivities(null, 1);
			$this->fail("Exception not thrown");
		} catch (ProjectActivityException $e) {
			// Expected
		}

		// Verify that invalid activity ids throw exceptions
		try {
			ProjectActivity::deleteActivities(array(1, ""), 1);
			$this->fail("Exception not thrown");
		} catch (ProjectActivityException $e) {
			// Expected
		}

		// Verify that invalid activity ids throw exceptions
		try {
			ProjectActivity::deleteActivities(array(1, "ew"), 1);
			$this->fail("Exception not thrown");
		} catch (ProjectActivityException $e) {
			// Expected
		}

		// try deleting unavailable ids.
		$numDeleted = ProjectActivity::deleteActivities($ids, $projId);
		$this->assertEquals(0, $numDeleted);

		$numDeleted = ProjectActivity::deleteActivities($ids);
		$this->assertEquals(0, $numDeleted);

		// create some activites
		$actList = $this->_getTestActivities();
		$this->_createActivites($actList);
		mysql_query("UPDATE hs_hr_project_activity SET deleted = 0");

		// delete one and check
		$ids = array(1);
		$numDeleted = ProjectActivity::deleteActivities($ids);
		$this->assertEquals(1, $numDeleted);

		$num = $this->_getNumActivities("activity_id = 1 AND deleted = 1");
		$this->assertEquals(1, $num);

		$num = $this->_getNumActivities("deleted = 1");
		$this->assertEquals(1, $num);
		$num = $this->_getNumActivities("deleted = 0");
		$this->assertEquals(3, $num);

		// delete already deleted activity, verify no change
		$numDeleted = ProjectActivity::deleteActivities($ids);
		$this->assertEquals(0, $numDeleted);

		$num = $this->_getNumActivities("activity_id = 1 AND deleted = 1");
		$this->assertEquals(1, $num);
		$num = $this->_getNumActivities("deleted = 1");
		$this->assertEquals(1, $num);

		mysql_query("UPDATE hs_hr_project_activity SET deleted = 0");

		// verify that only activies in given project are deleted.
		// NOTE: 1,2,3 belong to projId 1, 4 to projId 2
		$projId = 2;
		$ids = array(1, 2, 3);
		$numDeleted = ProjectActivity::deleteActivities($ids, $projId);
		$this->assertEquals(0, $numDeleted);

		$num = $this->_getNumActivities("deleted = 1");
		$this->assertEquals(0, $num);

		$ids = array(1, 2, 3, 4);
		$numDeleted = ProjectActivity::deleteActivities($ids, $projId);
		$this->assertEquals(1, $numDeleted);

		$num = $this->_getNumActivities("deleted = 1");
		$this->assertEquals(1, $num);

		$num = $this->_getNumActivities("activity_id = 4 AND deleted = 1");
		$this->assertEquals(1, $num);

		// delete multiple activities
		$ids = array(1, 2, 3);
		$numDeleted = ProjectActivity::deleteActivities($ids);
		$this->assertEquals(3, $numDeleted);

		$num = $this->_getNumActivities("deleted = 1");
		$this->assertEquals(4, $num);
	}

    /**
     * Returns the number of rows in the project_activity table
     *
     * @param  string $where where clause
     * @return int number of rows
     */
    private function _getNumActivities($where = null) {

    	$sql = "SELECT COUNT(*) FROM hs_hr_project_activity";
    	if (!empty($where)) {
    		$sql .= " WHERE " . $where;
    	}
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result, MYSQL_NUM);
        $count = $row[0];
		return $count;
    }

    /**
     * Checks that the attributes of the activity object and the database row match.
     *
     * @param ProjectActivity $activity
     * @param array           $row
     */
    private function _checkRow($activity, $row) {
		$this->assertEquals($activity->getName(), $row['name'], "Activity name not correct");
		$this->assertEquals($activity->getProjectId(), $row['project_id'], "Project id wrong");
		$this->assertEquals($activity->getId(), $row['activity_id'], "Activity id wrong");
		$this->assertEquals($activity->isDeleted(), (bool)$row['deleted'], "Deleted value wrong");
    }

    /**
     * Creates some ProjectActivity objects for use in the tests
     * @return array Array of ProjectActivity objects
     */
    private function _getTestActivities() {
		$activities['1'] = $this->_getActivityObject(1, 1, "test 1", false);
		$activities['2'] = $this->_getActivityObject(2, 1, "test 2", false);
		$activities['3'] = $this->_getActivityObject(3, 1, "test 3", true);
		$activities['4'] = $this->_getActivityObject(4, 2, "test 4", false);
		return $activities;
    }

    /**
     * Create a ProjectActivity object with the passed parameters
     */
    private function _getActivityObject($activity_id, $project_id, $name, $deleted) {
    	$activity = new ProjectActivity($activity_id);
    	$activity->setProjectId($project_id);
    	$activity->setName($name);
    	$activity->setDeleted($deleted);
    	return $activity;
    }

    /**
     * Saves the given Project Activity objects in the databas
     *
     * @param ProjectActivity $activities ProjectActivity objects to save.
     */
    private function _createActivites($activities) {
		foreach ($activities as $activity) {
			$sql = sprintf("INSERT INTO hs_hr_project_activity(activity_id, project_id, name, deleted) " .
                           "VALUES(%d, %d, '%s', %d)",
                           $activity->getId(), $activity->getProjectId(), $activity->getName(),
                           ($activity->isDeleted() ? 1 : 0));
            mysql_query($sql);
			UniqueIDGenerator::getInstance()->initTable();
		}
    }
}

// Call ProjectActivityTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "ProjectActivityTest::main") {
    ProjectActivityTest::main();
}
?>
