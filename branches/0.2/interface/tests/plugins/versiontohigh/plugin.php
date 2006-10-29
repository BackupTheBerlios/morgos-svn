<?php
$pluginClass = 'notCompatible2';

if (! class_exists ('notCompatible2')) {	

class notCompatible2 extends plugin {
	
	function notCompatible2 ($dir) {
		parent::plugin ($dir);
		$this->_name = 'Data tester plguin';
		$this->_ID = '{3781329d-3559-4cfe-8675-12bbc2368dad}';
		$this->_minMorgOSVersion = '0.0';
		$this->_maxMorgOSVersion = '0.1';
	}
	
	function load ($pluginAPI) {
		parent::load ($pluginAPI);
	}
}
}
?>