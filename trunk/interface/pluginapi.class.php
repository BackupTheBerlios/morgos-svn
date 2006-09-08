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
 * This is the pluginAPI class.
 *
 * @since 0.2
 * @author Nathan Samson
*/

class pluginAPI {
	var $_dbModule;
	var $_configManager;
	var $_userManager;
	var $_pageManager;
	
	var $_pluginManager;
	var $_eventManager;
	var $_actionManager;
	var $_smarty;

	function setDBModule (&$dbModule) {$this->_dbModule = $dbModule;}
	function getDBModule () {return $this->_dbModule;}

	function setConfigManager (&$configManager) {$this->_configManager = $configManager;}
	function getConfigManager () {return $this->_configManager;}
	
	function setUserManager (&$userManager) {$this->_userManager = $userManager;}
	function getUserManager () {return $this->_userManager;}
	
	function setPageManager (&$pageManager) {$this->_pageManager = $pageManager;}
	function getPageManager () {return $this->_pageManager;}
	
	function setPluginManager (&$pluginManager) {$this->_pluginManager = $pluginManager;}
	function getPluginManager () {return $this->_pluginManager;}
	
	function setEventManager (&$eventManager) {$this->_eventManager = $eventManager;}
	function getEventManager () {return $this->_eventManager;}
	
	function setActionManager (&$actionManager) {$this->_actionManager = $actionManager;}
	function getActionManager () {return $this->_actionManager;}
	
	function setSmarty (&$smarty) {$this->_smarty = $smarty;}
	function getSmarty () {return $this->_smarty;}
}

?>