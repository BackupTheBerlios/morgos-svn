<?php
	if (! class_exists ('W3CExtension')) {
	class W3CExtension {
		function W3CExtension ($UI) {
			$this->_construct ($UI);
		}
	
		function _construct ($UI) {
			$this->UI = $UI;
			$sidebar = 'BOX (W3C, HTML and CSS)';
			$UI->appendToSideBar ($sidebar);
		}
	}
	}
	$UI = $arrayOfObjects['UI'];
	return new W3CExtension ($UI);
?>