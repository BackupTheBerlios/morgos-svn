<?php
$pluginClass = 'errorOnLoad';

if (! class_exists ('errorOnLoad')) {	

class errorOnLoad extends plugin {
	
	function errorOnLoad ($dir) {
		parent::plugin ($dir);
		$this->_name = 'Error on load';
		$this->_ID = '{6f8cdf28-41a4-4e87-8613-c3f235c745aa}';
		$this->_version = '1.0';
		$this->_minMorgOSVersion = '0.2';
		$this->_maxMorgOSVersion = '0.2';
	}
	
	function load (&$pluginAPI) {
		parent::load ($pluginAPI);
		return new Error ("I_AM_STUCK");
	}
}
}
?>