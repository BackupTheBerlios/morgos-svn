<?php
$pluginClass = 'notCompatibleWithPHP';

if (! class_exists ('notCompatibleWithPHP')) {	

class notCompatibleWithPHP extends plugin {
	
	function notCompatibleWithPHP ($dir) {
		parent::plugin ($dir);
		$this->_name = 'Data tester plguin';
		$this->_ID = '{a9de79f4-4a09-4286-8893-ef2c53b5676a}';
		$this->_minMorgOSVersion = '0.2';
		$this->_maxMorgOSVersion = '0.2';
	}
	
	function load ($pluginAPI) {
		parent::load ($pluginAPI);
	}
	
	function isPHPCompatible () {
		return false;
	}
}
}
?>