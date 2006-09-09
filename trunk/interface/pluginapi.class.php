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
	var $_i18nManager;
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
	
	function setI18NManager (&$i18nManager) {$this->_i18nManager = $i18nManager;}
	function getI18NManager () {return $this->_i18nManager;}	
	
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
	
	/**
	 * Make the plugin do an action (and stops the current action). Only available for actions over GET
	 * @bug this doen't shutdown morgos
	 * 
	 * @param $action (string)
	 * @param $params (string array) The params that shopuld be given.
	*/
	function doAction ($action, $params = array ()) {
		$loc = 'index.php?action='.$action;
		foreach ($params as $name=>$value) {
			$loc .= '&'.$name.'='.$value;
		}
		header ('Location: '.$loc);
	}
}

?>