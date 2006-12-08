<?php
chdir ('../../');
require_once ('core/tests/base.php');

class MorgOSTests extends TestSuite {
	var $tests = array ();

	function MorgOSTests () {
		include_once ('core/tests/dbdrivers/dbdrivertestgeneric.class.php');
		$this->addTestFile ('core/tests/compatible.functions.test.php');
		$this->addTestFile ('core/tests/varia.functions.test.php');
		$this->addTestFile ('core/tests/config.class.test.php');
		global $avModules;		
		
		$this->addTestFile ('core/tests/databasemanager.functions.test.php');

		$config = parse_ini_file ('core/tests/options.ini', true);
		foreach ($avModules as $module=>$object) {
			$mod = MorgOSTests::loadModuleFromConfig ($module, $config);
			if (isError ($a)) {
				echo 'SKIPPED: '.$module;
				continue;
			}
			MorgOSTests::removeAllTablesForModule ($mod);
		
			switch ($module) {
				case 'MySQL':
					$this->addTestFile ('core/tests/dbdrivers/mysql.test.driver.class.php');
					break;
				case 'PostgreSQL': 
					$this->addTestFile ('core/tests/dbdrivers/pgsql.test.driver.class.php');
					break;
			}
			$mod->disconnect ();
		}
		global $dbModule;
		$dbModule = MorgOSTests::loadModuleFromConfig ($config['defaultModule'], $config);
		MorgOSTests::removeAllTablesForModule ($dbModule);
		$this->addTestFile ('core/tests/sqlwrapper.class.test.php');
		$this->addTestFile ('core/tests/pagemanager.class.test.php');
		$this->addTestFile ('core/tests/usermanager.class.test.php');
		
		
		require_once ('PHPUnit/GUI/HTML.php');
		$GUI = new PHPUnit_GUI_HTML ($this->getAllTests ());
		$GUI->show ();
	}
	
	function getAllTests () {
		return $this->tests;
	}
	
	function addTestFile ($file) {
		$currentClasses = get_declared_classes ();
		include_once ($file);
		$newCurrentClasses = get_declared_classes (); 
		
		$diff = array_diff ($newCurrentClasses, $currentClasses);
		foreach ($diff as $newClass) {
			if (get_parent_class ($newClass) == 'testcase') {
				$class = new TestSuite ($newClass);
				//$class->run ('a');
				$this->tests[] = $class; 
			}
		}
	}
	
	function loadModuleFromConfig ($module, $config) {
		$mod = databaseLoadModule ($module);	
		$mOpts = $config[$module];
		$a = $mod->connect ($mOpts['Host'], $mOpts['User'], 
			$mOpts['Password']);
		$a = $mod->selectDatabase ($mOpts['DatabaseName']);
		
		return $mod;
	}
	
	function removeAllTablesForModule ($module) {
		foreach ($module->getAllTables () as $tableName) {
			$sql = "DROP TABLE $tableName";
			$module->query ($sql);
		}
	}
}

$tests = new MorgOSTests ();
?>