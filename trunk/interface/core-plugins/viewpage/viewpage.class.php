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
 * This is the viewPage class.
 *
 * @since 0.2
 * @author Nathan Samson
*/
class viewPageCorePlugin extends plugin {
	
	function viewPageCorePlugin ($dir) {
		parent::plugin ($dir);
		$this->_name = 'Viewpage core plugin';
		$this->_ID = '{529e4a98-02a7-46bb-be2a-671a7dfc852f}';
		$this->_minMorgOSVersion = '0.2';
		$this->_maxMorgOSVersion = '0.2';
	}
	
	function load (&$pluginAPI) {
		parent::load ($pluginAPI);
		$am = &$this->_pluginAPI->getActionManager ();
		$am->addAction (
			new action ('viewPage', 'GET',  array ($this, 'onViewPage'), array (), array ('pageID', 'pageLang')));
		
		$em = &$this->_pluginAPI->getEventManager ();
		$em->addEvent (new Event ('viewPage'));
	}
	
	function onViewPage ($pageID, $pageLang) {
		$pMan = &$this->_pluginAPI->getPageManager ();
		$root = $pMan->newPage ();
		$root->initFromGenericName ('site');
		$page = $pMan->newPage ();
		if ($pageID !== null) {			
			$page->initFromDatabaseID ($pageID);
		} else {
			$menu = $pMan->getMenu ($root);
			$page = $menu[0];
		}
		
		$sm = &$this->_pluginAPI->getSmarty ();
		$sm->assign ('MorgOS_CurrentPage_Title', $page->getName ());
		$sm->assign ('MorgOS_CurrentPage_Content', $page->getContent ());		
		$sm->assign ('MorgOS_Site_HeaderImage', $this->getHeaderImageLink ());
		$sm->assign ('MorgOS_Copyright', 'Powered by MorgOS &copy; 2006');
		$sm->assign ('MorgOS_Menu', $this->getMenuArray ($page->getParentPage ()));
		$sm->assign ('MorgOS_RootMenu', $this->getMenuArray ($root, false));
		
		$em = &$this->_pluginAPI->getEventManager ();
		$a = $em->triggerEvent ('viewPage');
		foreach ($a as $r) {
			if ($r == false or isError ($r)) {
				return;
			}
		}		
		
		$sm->display ('index.tpl');
	}

	function getMenuArray ($rootPage, $rec = true) {
		$array = array ();
		$pageManager = $this->_pluginAPI->getPageManager ();
		$menu = $pageManager->getMenu ($rootPage);
		foreach ($menu as $menuItem) {
			$itemArray = array ();
			if ($rec == true) {
				$itemArray['Childs'] = $this->getMenuArray ($menuItem, false);
			} else {
				$itemArray['Childs'] = array ();
			}
			$itemArray['Title'] = $menuItem->getName ();
			$itemArray['Link'] = $menuItem->getLink (); 
			$array[] = $itemArray;
		}
		return $array;
	}	
	
	function getHeaderImageLink () {
		return 'skins/default/images/logo.png';
	}
}
?>
