<?php
$pluginClass = 'dataTesterPlugin';

if (! class_exists ('dataTesterPlugin')) {	

class dataTesterPlugin extends plugin {
	
	function dataTesterPlugin ($dir) {
		parent::plugin ($dir);
		$this->_name = 'Data tester plugin';
		$this->_ID = '{3d8c7836-2b7a-4b04-bfab-a46e20846675}';
		$this->_version = '1.0';
		$this->_minMorgOSVersion = '0.2';
		$this->_maxMorgOSVersion = '0.2';
	}
	
	function load (&$pluginAPI) {
		parent::load ($pluginAPI);
		global $loadDataTester;
		$loadDataTester++;
	}
	
	function unLoad () {
		parent::unLoad ();
		global $loadDataTester;
		$loadDataTester--;
	}
	
	function testData1 () {
		$this->_pluginAPI->_value = 1; 
	}
	
	function testData2 () {
		$this->_pluginAPI->_value = 2;
	}
	
	function testReturnTrue () {
		return $this->_pluginAPI->returnsTrue ();
	}
	
	function testReturnFalse () {
		return $this->_pluginAPI->returnsFalse ();
	}
}
}
?>