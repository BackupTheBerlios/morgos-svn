<?php
	set_include_path(get_include_path () . PATH_SEPARATOR . '../../');
	include_once ('../config.class.php');
	
	global $testConfig;
	$testConfig = new config (); 
	addTestToRun ('config.class.php', 'addConfigItem', 'testAddConfigItem', array (&$testConfig));
	addTestToRun ('config.class.php', 'addConfigItemFromArray', 'testAddConfigItemFromArray', array (&$testConfig));
	addTestToRun ('config.class.php', 'addConfigItemFromFile', 'testAddConfigItemsFromFile', array (&$testConfig));
	addTestToRun ('config.class.php', 'getConfigItem', 'testGetConfigItem', array (&$testConfig));
	addTestToRun ('config.class.php', 'changeValueConfigItem', 'testChangeValueConfigItem', array (&$testConfig));
	addTestToRun ('config.class.php', 'getConfigDir', 'testGetConfigDir', array (&$testConfig));
	addTestToRun ('config.class.php', 'exists', 'testExists', array (&$testConfig));
	addTestToRun ('config.class.php', 'isDir', 'testtestIsDir', array (&$testConfig));
	addTestToRun ('config.class.php', 'removeConfigItem', 'testRemoveConfigItem', array (&$testConfig));
	
	function testAddConfigItem () {
	}
	
	function testAddConfigItemFromArray () {
	}
	
	function testAddConfigItemsFromFile () {
	}
	
	function testGetConfigItem () {
	}

	function testChangeValueConfigItem () {
	}

	function testGetConfigDir () {
	}

	function testExists () {
	}

	function testtestIsDir () {
	}

	function testRemoveConfigItem () {
	}

?>