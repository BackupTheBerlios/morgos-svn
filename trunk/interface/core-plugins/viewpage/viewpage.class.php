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
 * This is the viewPage class.
 *
 * @since 0.2
 * @author Nathan Samson
*/
class viewPageCorePlugin extends InstallablePlugin {
	var $_adminPlugin;
	
	function viewPageCorePlugin ($dir) {
		parent::plugin ($dir);
		include_once ($dir.'/viewpageadmin.class.php');
		$this->_name = 'Viewpage core plugin';
		$this->_ID = '{529e4a98-02a7-46bb-be2a-671a7dfc852f}';
		$this->_minMorgOSVersion = MORGOS_VERSION;
		$this->_maxMorgOSVersion = MORGOS_VERSION;
		$this->_version = MORGOS_VERSION;
		$this->_adminPlugin = new viewPageCoreAdminPlugin ($this->_loadedDir);
	}
	
	function load (&$pluginAPI) {
		parent::load ($pluginAPI);
		$this->_adminPlugin->load ($pluginAPI);
		
		$am = &$this->_pluginAPI->getActionManager ();
		$am->addAction (
			new action ('viewPage', 'GET',  array ($this, 'onViewPage'), array (), 
				array (new IDInput ('pageID'), new LocaleInput ('pageLang'))));
		
		$em = &$this->_pluginAPI->getEventManager ();
		$em->addEvent (new Event ('viewPage', array ('pageID')));
		
		$em->subscribeToEvent ('viewPage', 
			new callback ('setPageVars', array ($this, 'setPageVars'), 
				array ('pageID')));
	}
	
	function onViewPage ($pageID) {
		$pMan = &$this->_pluginAPI->getPageManager ();
		$sm = &$this->_pluginAPI->getSmarty ();
		$em = &$this->_pluginAPI->getEventManager ();
		$page = $pMan->newPage ();
		
		if ($pageID !== null) {			
			$a = $page->initFromDatabaseID ($pageID);
			if (isError ($a)) {
				if ($a->is ('DATABASEOBJECT_ID_NOT_FOUND')) {
					$root = $pMan->newPage ();
					$root->initFromName ('site');
					$menu = $pMan->getMenu ($root);
					$page = $menu[0];
					$a = $em->triggerEvent ('viewPage', 
						array ($page->getID (), $pageLang));
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

		$a = $em->triggerEvent ('viewPage', array ($page->getID ()));
		foreach ($a as $r) {
			if ($r == false or isError ($r)) {
				return $r;
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
		return $this->_pluginAPI->menuToArray (
			$pageManager->getMenu ($rootPage), $pageLang);
	}	
	
	function getHeaderImageLink () {
		return 'skins/default/images/logo.png';
	}
	
	function setPageVars ($pageID) {
		$pM = &$this->_pluginAPI->getPageManager ();
		$config = &$this->_pluginAPI->getConfigManager ();
		$pageLang = $config->getStringItem ('/user/contentLang');
		$root = $pM->getSitePage ();
		$page = $pM->newPage ();
		$page->initFromDatabaseID ($pageID);
		$tPage = $page->getTranslation ($pageLang);	
		if (isError ($tPage)) {
			if ($tPage->is ('PAGE_TRANSLATION_DOESNT_EXISTS')) {
				if ($pageLang == $this->_pluginAPI->getDefaultLanguage ()) {
					return new Error ('DEFAULT_PAGE_TRANSLATION_DOESNT_EXISTS');
				} else {
					return $this->setPageVars ($pageID, 
							$this->_pluginAPI->getDefaultLanguage ());
				}
			} else {
				return $tPage;
			}
		}
		
		$sm = &$this->_pluginAPI->getSmarty ();
		$sm->assign ('MorgOS_CurrentPage_Title', $tPage->getTitle ());
		$sm->assign ('MorgOS_CurrentPage_Content', $tPage->getContent ());		
		$sm->assign ('MorgOS_Site_HeaderImage', $this->getHeaderImageLink ());
		$sm->assign ('MorgOS_Copyright', 'Powered by MorgOS &copy; 2005-2007');
		$sm->assign ('MorgOS_Menu', $this->getMenuArray ($page->getParentPage (), 
			$pageLang));
		$sm->assign ('MorgOS_RootMenu', $this->getMenuArray ($root, $pageLang));
		$sm->assign ('MorgOS_ExtraSidebar', '');
		$sm->assign ('MorgOS_ExtraHead', '');
		$sm->assign ('MorgOS_SiteTitle', 
			$config->getStringItem ('/site/title'));
		return true;
	}
	
	function isCorePlugin () {return true;}
	
	function install (&$pluginAPI, &$dbDriver, $siteDefaultLanguage) {
		$pageM = new PageManager ($dbDriver);
		$pageM->installAllTables ();
		$site = $pageM->getSitePage ();
		$t = &$pluginAPI->getI18NManager();

		$home = $pageM->newPage ();
		$home->initFromArray (array (
				'name'=>'MorgOS_Home', 
				'parent_page_id'=>$site->getID (),
				'place_in_menu'=>MORGOS_MENU_FIRST));	
		$pageM->addPageToDatabase ($home);
		$tHome = $pageM->newTranslatedPage ();
		
		$tHome->initFromArray (array (
				'language_code'=>$siteDefaultLanguage, 
				'translated_title'=>$t->translate ('Home'), 
				'translated_content'=>$t->translate ('This is the homepage.')));
		$home->addTranslation ($tHome);	
		
		$this->_adminPlugin->install ($pluginAPI, $dbDriver, $siteDefaultLanguage);
	}
	
	function isInstalled (&$pluginAPI) {
		$db = &$pluginAPI->getDBDriver ();
		return $db->tableExists ('pages') && $db->tableExists ('translatedPages');
	}
}
?>
