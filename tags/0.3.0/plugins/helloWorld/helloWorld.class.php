<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005-2007 MorgOS
 * This program is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * any later version.
 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Library General Public License for more details.
 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. 
*/
/**
 * This is the helloWorld class.
 *
 * @since 0.2
 * @author Nathan Samson
*/
class helloWorldPlugin extends Plugin {
	
	function helloWorldPlugin ($dir) {
		parent::plugin ($dir);
		$this->_name = 'Hello world example plugin';
		$this->_ID = '{5c81a4b6-8141-45d6-8003-652e46447575}';
		$this->_minMorgOSVersion = '0.3';
		$this->_maxMorgOSVersion = '0.3';
		$this->_version = '1.1';
	}
	
	function load ($pluginAPI) {
		parent::load ($pluginAPI);
		return new Error ("LOADING_ERROR");
		$eventM = &$this->_pluginAPI->getEventManager ();
		$a = $eventM->subscribeToEvent ('viewPage', 
			new Callback ('onViewHelloWorld', array ($this, 'onViewPage')));
		
	}
	
	function onViewPage () {
		$sm = &$this->_pluginAPI->getSmarty ();
		$sm->prependTo ('MorgOS_CurrentPage_Content', 'Hello world. <br />');
		return true;
	}
	
}
?>