<?php
/* MorgOS is a Content Management System written in PHP
 * Copyright (C) 2005-2006 MorgOS
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
 * This is the users class.
 *
 * @since 0.2
 * @author Sam Heijens
*/
class userCorePlugin extends plugin {
	
function userCorePlugin ($dir) {

		parent::plugin ($dir);
		$this->_name = 'Users core plugin';
		$this->_ID = '{5df79e7c-2c14-4ad2-b13e-5c420d33182a}';
		$this->_minMorgOSVersion = '0.2';
		$this->_maxMorgOSVersion = '0.2';
	}
	
	function load (&$pluginAPI) {
		parent::load (&$pluginAPI);
		$am = &$this->_pluginAPI->getActionManager ();
		$am->addAction (new action ('login', 'POST',  array (&$this, 'onLogin'), array ('login','password'), array ()));
	}
	
	function onLogin () {
	}
	
	function onLogout () {
	}
	
	function onRegistrar () {
	}
	
	function onForgotPassword () {
	}
	
	


}
?>