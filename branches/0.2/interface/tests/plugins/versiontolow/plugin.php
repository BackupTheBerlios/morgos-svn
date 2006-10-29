<?php
$pluginClass = 'notCompatible';

if (! class_exists ('notCompatible')) {	

class notCompatible extends plugin {
	
	function notCompatible ($dir) {
		parent::plugin ($dir);
		$this->_name = 'Data tester plguin';
		$this->_ID = '{2b244bb9-b4e4-4738-9071-bf6b05bf3843}';
		$this->_minMorgOSVersion = '0.3';
		$this->_maxMorgOSVersion = '0.5';
	}
	
	function load ($pluginAPI) {
		parent::load ($pluginAPI);
	}
}
}
?>