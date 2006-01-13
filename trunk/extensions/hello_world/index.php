<?php
	if (! class_exists ('HelloWorldExtension')) {
	class HelloWorldExtension {
		function HelloWorldExtension ($UI, $extensionDir) {
			$this->__construct (&$UI, $extensionDir);
		}
	
		function __construct (&$UI, $extensionDir) {
			$this->UI = &$UI;
			$this->extensionDir = $extensionDir;
			$this->i10nMan = new languages ($this->extensionDir . '/languages');
			$this->user = $UI->getUserClass ();
			$params = array ();
			$UI->signalMan->connectSignal ('loadPage', array ($this, 'doSomething'), $params);
		}
		
		function doSomething () {
			if ($this->user->isLoggedIn () == true) {
				$userInfo = $this->user->getUser ();
				$this->UI->pages->prependTextToPageContent ($this->i10nMan->translate ('Hello %1. <br />', $userInfo['username']));
			} else {
				$this->UI->pages->prependTextToPageContent ($this->i10nMan->translate ('Hello World. <br />'));
			}
			$this->UI->pages->appendTextToPageContent ($this->i10nMan->translate ('<br />See You later alligator.'));
		}
	}
	}
	$UI = &$arrayOfObjects['UI'];
	$extensionDir = $UI->extensions['{2345-6789-0123-4567}']['extension_dir'];
	return new HelloWorldExtension ($UI, $extensionDir);
?>
