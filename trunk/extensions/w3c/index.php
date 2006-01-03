<?php
	class W3CExtension {
		function W3CExtension ($UI) {
			$this->_construct ($UI);
		}
	
		function _construct ($UI) {
			$this->UI = $UI;
			$sidebar = 'BOX ()';
			//$UI->pages->addToSideBar ($sidebar);
		}
	}
	
	$UI = $arrayOfObjects['UI'];
	return new W3CExtension ($UI);
?>