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
define ('ERROR', 1);
define ('WARNING', 2);
define ('NOTICE', 3);

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
	
	var $_morgos;
	
	function pluginAPI (&$morgos) {
		$this->_morgos = &$morgos;
	}

	function setDBModule (&$dbModule) {$this->_dbModule = &$dbModule;}
	function &getDBModule () {return $this->_dbModule;}

	function setConfigManager (&$configManager) {$this->_configManager = &$configManager;}
	function &getConfigManager () {return $this->_configManager;}
	
	function setI18NManager (&$i18nManager) {$this->_i18nManager = &$i18nManager;}
	function &getI18NManager () {return $this->_i18nManager;}	
	
	function setUserManager (&$userManager) {$this->_userManager = &$userManager;}
	function &getUserManager () {return $this->_userManager;}
	
	function setPageManager (&$pageManager) {$this->_pageManager = &$pageManager;}
	function &getPageManager () {return $this->_pageManager;}
	
	function setPluginManager (&$pluginManager) {$this->_pluginManager = &$pluginManager;}
	function &getPluginManager () {return $this->_pluginManager;}
	
	function setEventManager (&$eventManager) {$this->_eventManager = &$eventManager;}
	function &getEventManager () {return $this->_eventManager;}
	
	function setActionManager (&$actionManager) {$this->_actionManager = &$actionManager;}
	function &getActionManager () {return $this->_actionManager;}
	
	function setSmarty (&$smarty) {$this->_smarty = &$smarty;}
	function &getSmarty () {return $this->_smarty;}
	
	/**
	 * Make the plugin do an action (and stops the current action). Only available for actions over GET
	 * 
	 * @param $action (string)
	 * @param $params (string array) The params that shopuld be given.
	*/
	function doAction ($action, $params = array ()) {
		$loc = 'index.php?action='.$action;
		foreach ($params as $name=>$value) {
			$loc .= '&'.$name.'='.$value;
		}
		$this->_morgos->shutdown ();
		header ('Location: '.$loc);
	}
	
	function executePreviousAction () {
		$hString = $this->_actionManager->getPreviousActionHeaderString (); 
		$this->_morgos->shutdown ();
		header ($hString);
	}
	
	/**
	 * Adds a message to the queue.
	 *
	 * @param $tMessage (string) the message, this is the exact message that will be shown.
	 *  it should be translated already.
	 * @param $type (ERROR|WARNING|NOTICE)
	 * @public
	*/
	function addMessage ($tMessage, $type) {
		$used = 0;
		foreach ($_COOKIE as $key=>$v) {
			if (substr ($key, 0, strlen ('message_')) == 'message_') {
				if (substr ($key, strlen ('message_'), 1) == $type) {
					$used++;
				}
			}
		}
		$newKey = 'message_'.$type.'_'.$used;
		setcookie ($newKey, $tMessage);
	}
	
	/**
	 * Returns all messages, AND removes them from the queue
	 *
	 * @return (string array array)
	*/
	function getAllMessages () {
		$messages = array (ERROR=>array(), WARNING=>array(), NOTICE=>array());
		foreach ($_COOKIE as $key=>$value) {
			if (substr ($key, 0, strlen ('message_')) == 'message_') {
				$type = (int) substr ($key, strlen ('message_'), 1);
				$messages[$type][] = $value;
				setcookie ($key, '');
			}
		}
		return $messages;
	}
	
	function addRuntimeMessage ($tMessage, $type) {
		$sm = &$this->getSmarty ();
		$sm->append_by_ref ('MorgOS_Notices', $tMessage);
		//$sm->debugging = true;
	}	
	
	function userCanViewPage () {
		return true;
	}
	
	function menuToArray ($menu) {
		$pageLang = 'en_UK';
		$array = array ();
		foreach ($menu as $menuItem) {
			$itemArray = array ();
			$itemArray['Childs'] = $this->menuToArray ($this->_pageManager->getMenu ($menuItem));
			$t = $menuItem->getTranslation ($pageLang);
			$itemArray['Title'] = $t->getTitle ();
			$itemArray['Link'] = $menuItem->getLink (); 
			$itemArray['ID'] = $menuItem->getID (); 
			$itemArray['PlaceInMenu'] = $menuItem->getPlaceInMenu (); 
			$array[] = $itemArray;
		}
		return $array;
	}
}

?>
