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
	var $_adminPlugin;
	
	function viewPageCorePlugin ($dir) {
		parent::plugin ($dir);
		include_once ($dir.'/viewpageadmin.class.php');
		$this->_name = 'Viewpage core plugin';
		$this->_ID = '{529e4a98-02a7-46bb-be2a-671a7dfc852f}';
		$this->_minMorgOSVersion = MORGOS_VERSION;
		$this->_maxMorgOSVersion = MORGOS_VERSION;
		$this->_version = MORGOS_VERSION;
	}
	
	function load (&$pluginAPI) {
		parent::load ($pluginAPI);

		$this->_adminPlugin = new viewPageCoreAdminPlugin ($this->_loadedDir);
		$this->_adminPlugin->load ($pluginAPI);
		
		$am = &$this->_pluginAPI->getActionManager ();
		$am->addAction (
			new action ('viewPage', 'GET',  array ($this, 'onViewPage'), array (), 
				array (new IDInput ('pageID'), new LocaleInput ('pageLang'))));
		
		$em = &$this->_pluginAPI->getEventManager ();
		$em->addEvent (new Event ('viewPage', array ('pageID', 'pageLang')));
		
		$em->subscribeToEvent ('viewPage', new callback ('setPageVars', array ($this, 'setPageVars'), array ('pageID', 'pageLang')));
	}
	
	function onViewPage ($pageID, $pageLang) {
		$pMan = &$this->_pluginAPI->getPageManager ();
		$sm = &$this->_pluginAPI->getSmarty ();
		$em = &$this->_pluginAPI->getEventManager ();
		$page = $pMan->newPage ();
		if ($pageLang == null) {
			$pageLang = 'en_UK';
		}		
		
		if ($pageID !== null) {			
			$a = $page->initFromDatabaseID ($pageID);
			if (isError ($a)) {
				if ($a->is ('DATABASEOBJECT_ID_NOT_FOUND')) {
					$root = $pMan->newPage ();
					$root->initFromName ('site');
					$menu = $pMan->getMenu ($root);
					$page = $menu[0];
					$a = $em->triggerEvent ('viewPage', array ($page->getID (), $pageLang));
					$sm->display ('404.tpl');
					return;
				} else {
					return $a;
				}
			}
		} else {
			$root = $pMan->newPage ();
			$root->initFromName ('site');
			$menu = $pMan->getMenu ($root);
			$page = $menu[0];
		}		
		
		if ($pageLang == null) {
			$pageLang = 'en_UK';
		}

		$a = $em->triggerEvent ('viewPage', array ($page->getID (), $pageLang));
		foreach ($a as $r) {
			if ($r == false or isError ($r)) {
				return;
			}
		}
		if ($page->getAction ()) {
			$aM = &$this->_pluginAPI->getActionManager ();
			$aM->executeAction ($page->getAction (), false);
		} else {		
			$sm->display ('genericpage.tpl');
		}
	}

	function getMenuArray ($rootPage, $pageLang) {
		$array = array ();
		$pageManager = &$this->_pluginAPI->getPageManager ();
		return $this->_pluginAPI->menuToArray ($pageManager->getMenu ($rootPage));
	}	
	
	function getHeaderImageLink () {
		return 'skins/default/images/logo.png';
	}
	
	function setPageVars ($pageID, $pageLang) {
		$pM = &$this->_pluginAPI->getPageManager ();
		$config = &$this->_pluginAPI->getConfigManager ();
		$root = $pM->newPage ();
		$root->initFromName ('site');
		$page = $pM->newPage ();
		$page->initFromDatabaseID ($pageID);
		if ($pageLang == null) {
			$pageLang = 'en_UK';
		}
		$tPage = $page->getTranslation ($pageLang);	
		if (isError ($tPage)) {
			return $tPage;
		}
		
		$sm = &$this->_pluginAPI->getSmarty ();
		$sm->assign ('MorgOS_CurrentPage_Title', $tPage->getTitle ());
		$sm->assign ('MorgOS_CurrentPage_Content', $tPage->getContent ());		
		$sm->assign ('MorgOS_Site_HeaderImage', $this->getHeaderImageLink ());
		$sm->assign ('MorgOS_Copyright', 'Powered by MorgOS &copy; 2006');
		$sm->assign ('MorgOS_Menu', $this->getMenuArray ($page->getParentPage (), $pageLang));
		$sm->assign ('MorgOS_RootMenu', $this->getMenuArray ($root, $pageLang));
		$sm->assign ('MorgOS_ExtraSidebar', '');
		$sm->assign ('MorgOS_ExtraHead', '');
		$sm->assign ('MorgOS_SiteTitle', 
			$config->getStringItem ('/site/title'));
		return true;
	}
	
	function isCorePlugin () {return true;}
	
	function install (&$db) {
		$PM = new PageManager ($db);
		return $PM->installAllTables ();
	}
	
	function isInstalled (&$pluginAPI) {
		$db = &$pluginAPI->getDBModule ();
		return $db->tableExists ('pages') && $db->tableExists ('translatedPages');
	}
}
?>
