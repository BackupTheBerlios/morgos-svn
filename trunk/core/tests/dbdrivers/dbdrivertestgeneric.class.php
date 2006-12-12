<?php

class DBDriverGenericTest extends TestCase {
	var $_moduleName;
	var $_module;

	function setUp ($moduleName) {
		//parent::__construct ();
		$this->_moduleName = $moduleName;
		$this->_module = databaseLoadModule ($this->_moduleName);
		$a = $this->connect ();
		if ($a === false) {
			return;
		}
	}
	
	function tearDown () {
		// shutting down prevents for working MySQL driver 
		// (closure of ALL connections?)
		//$this->_module->disconnect ();
	}

	function connect () {
		$config = parse_ini_file ('core/tests/options.ini', true);
		$mOpts = $config[$this->_moduleName];
		$a = $this->_module->connect ($mOpts['Host'], $mOpts['User'], 
			$mOpts['Password'], $mOpts['DatabaseName']);
		if (isError ($a)) {
			return false;
		}
	}
	
	function testConnect () {
		$config = parse_ini_file ('core/tests/options.ini', true);
		$mOpts = $config[$this->_moduleName];
		$a = $this->_module->connect ($mOpts['Host'], $mOpts['User'].'data', 
			$mOpts['Password'], $mOpts['DatabaseName']);
		$this->assertTrue ($a->is ('DBDRIVER_CANT_CONNECT'));
	}
	
	function testQuery () {
		if ($this->_module->getType () == 'PostgreSQL') {
			$sql = "CREATE TABLE sql_test (test_id serial , 
				name varchar (255), bool smallint, atext text, PRIMARY KEY (test_id))";
		} else {
			$sql = "CREATE TABLE sql_test (test_id int AUTO_INCREMENT, 
				name varchar (255), bool int (1), atext text, PRIMARY KEY (test_id))";
		}
		$r = $this->_module->query ($sql);
		$this->assertFalse (isError ($r));
		
		$wrongSQL = "BLA BLA BLO";
		$r = $this->_module->query ($wrongSQL);
		$this->assertTrue ($r->is ('SQL_QUERY_FAILED'));
		$this->assertEquals ($wrongSQL, $r->getParam (1));
	}
	
	
	
	function testLatestInsertID () {

		$sql = "INSERT INTO sql_test (name, bool, atext)
			VALUES ('Nathan', 1, 'Long text, but not so long')";
		$query = $this->_module->query ($sql);
		$latestID = $this->_module->latestInsertID ($query);
		
		$IDsql= "SELECT test_id FROM sql_test WHERE name='Nathan'";
		$query = $this->_module->query ($IDsql);
		$row = $this->_module->fetchArray ($query);
		$expLatestID = $row['test_id'];
		$this->assertEquals ($expLatestID, $latestID);
	}
	
	function testNumRows () {
		$sql = "SELECT bool FROM sql_test WHERE name='Nathan'";
		$query = $this->_module->query ($sql);
		$this->assertEquals (1, $this->_module->numRows ($query));
	}
	
	function testAffectedRows () {
		$sql = "UPDATE sql_test SET name='Nele' WHERE name='Nathan'";
		$query = $this->_module->query ($sql);
		$this->assertEquals (1, $this->_module->affectedRows ($query));
	}
	
	function testGetAllFields () {
		$expAllFields = array ();
		$allFields = $this->_module->getAllFields ('sql_test');
		$expAllFields = array ();
		
		$expAllFields[] = array (
						'Field'=>'test_id',
						'Type'=>'int',
						'Null'=>false,
						'MaxLength'=>11,
						'Default'=>null
						);
						
		$expAllFields[] = array (
						'Field'=>'name',
						'Type'=>'string',
						'Null'=>true,
						'MaxLength'=>255,
						'Default'=>null
						);
						
		$expAllFields[] = array (
						'Field'=>'bool',
						'Type'=>'int',
						'Null'=>true,
						'MaxLength'=>1,
						'Default'=>null
						);		
		
		$expAllFields[] = array (
						'Field'=>'atext',
						'Type'=>'text',
						'Null'=>true,
						'MaxLength'=>0,
						'Default'=>null
						);
		$this->assertEquals ($expAllFields, $allFields);
	}
	
	function testGetAllDBFields () {
		// this is something that SQLWrapper should test
	}	
	
	function testGetAllTables () {
		$expAllTables = array ('sql_test');
		$this->assertEquals ($expAllTables, $this->_module->getAllTables ());
	}
	
	function testEscapeString () {
	}
	
	function testTableExists () {
		$this->assertTrue ($this->_module->tableExists ('sql_test'));
		$this->assertFalse ($this->_module->tableExists ('JODELAHITI'));
	}
	
	function testDisconnect () {
		$a = $this->_module->disconnect ();
		$this->assertFalse (isError ($a));
		$a = $this->_module->disconnect ();
		$this->assertTrue (isError ($a));
		$this->assertTrue ($a->is ('DBDRIVER_NOT_CONNECTED'));
	}

}

?>