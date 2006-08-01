<?php
	if (! class_exists ('StatisticsExtension')) {
	class StatisticsExtension {
		function StatisticsExtension ($UI) {
			$this->__construct ($UI);
		}
	
		function __construct ($UI) {
			$this->UI = $UI;
			$UI->pages->prependTextToPageContent ('Hello StatisticsExtension. <br />', 'view_statistics');
			$UI->pages->appendTextToPageContent ('<br />See You later alligator.', 'view_statistics');
			$params = array ();
//			$UI->signalMan->connectSignal ('loadPage', array ($this, 'onLoadPage'), $params);
		}
		
		function onLoadPage ($name, $language) {
			$buffer = @fopen ('data/statistics.txt', 'a');
			if ($buffer === false) {
				trigger_error ('WARNING: ' . 'Could not open file, site statistics are not saved.');
				return;
			}
			$time = time ();
			$ID = $_SERVER['REMOTE_ADDR'];
			if ($this->UI->user->isLoggedIn ()) {
				$user = $this->UI->user->getUserInfo ();
				$ID .= '/' . $user;
			}
			$browser = $_SERVER['HTTP_USER_AGENT'];
			fwrite ($time . ' ' . $ID . ' ' . $name . '/' . $language . ' ' . $browser . "\n");
			fclose ($buffer);
		}
	}
	}
	$UI = $arrayOfObjects['UI'];
	return new StatisticsExtension ($UI);
?>
