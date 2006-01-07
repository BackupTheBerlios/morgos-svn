<?php
	if (! class_exists ('StatisticsExtension')) {
	class StatisticsExtension {
		function StatisticsExtension ($UI) {
			$this->_construct ($UI);
		}
	
		function _construct ($UI) {
			$this->UI = $UI;
			$UI->pages->prependTextToPageContent ('Hello StatisticsExtension. <br />');
			$UI->pages->appendTextToPageContent ('<br />See You later alligator.');
		}
	}
	}
	$UI = $arrayOfObjects['UI'];
	return new StatisticsExtension ($UI);
?>
