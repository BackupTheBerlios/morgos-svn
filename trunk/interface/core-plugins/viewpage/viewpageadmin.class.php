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
 * This is the viewPage admin class.
 *
 * @since 0.2
 * @author Nathan Samson
*/

class viewPageCoreAdminPlugin extends InstallablePlugin {

	function viewPageCoreAdminPlugin ($dir) {
		parent::plugin ($dir);
	}
	
	function load (&$pluginAPI) {
		parent::load ($pluginAPI);
		
		$am = &$this->_pluginAPI->getActionManager ();
		$em = &$this->_pluginAPI->getEventManager ();
		
		$am->addAction (
			new action ('adminPageManager', 'GET',  array (&$this, 'onViewPageManager'), 
			array (), array (new IDInput ('parentPageID'), new LocaleInput ('pageLang')), 'MorgOS_Admin_PageManager'));		
		
		// page edit action
		$am->addAction (
			new action ('adminMovePageDown', 'GET',  
				array (&$this, 'onMovePageDown'), new IDInput ('pageID'), array (), 'MorgOS_Admin_PageManager', false));
				
		$am->addAction (
			new action ('adminMovePageUp', 'GET',  
				array (&$this, 'onMovePageUp'), new IDInput ('pageID'), array (), 'MorgOS_Admin_PageManager', false));
				
		$am->addAction (
			new action ('adminSavePage', 'POST',  
				array (&$this, 'onSavePage'), array (new IDInput ('pageID'), 
					new StringInput ('pageTitle'), new StringInput ('pageNavTitle'), 
					new StringInput ('pageContent')), array (), 'MorgOS_Admin_PageManager', false));
				
		$am->addAction (
			new action ('adminNewPage', 'GET',  
				array (&$this, 'onNewPage'), array (new IDInput ('parentPageID'), 'pageTitle'), 
					array (), 'MorgOS_Admin_PageManager', false));
				
		$am->addAction (
			new action ('adminDeletePage', 'GET',  
				array (&$this, 'onDeletePage'), array (new IDInput ('pageID')), 
				array (), 'MorgOS_Admin_PageManager', false));
				
		$am->addAction (
			new action ('adminAddPageToMenu', 'GET',  
				array (&$this, 'onDeletePage'), array (new IDInput ('pageID')), 
				array (), 'MorgOS_Admin_PageManager', false));
				
		$am->addAction (
			new action ('adminRemovePageFromMenu', 'GET',  
				array (&$this, 'onDeletePage'), array (new IDInput ('pageID')), 
				array (), 'MorgOS_Admin_PageManager', false));
				
		$am->addAction (
			new action ('adminMovePageLevelDown', 'GET',  
				array (&$this, 'onMovePageLevelDown'), array (new IDInput ('pageID'), new IDInput ('newParentPageID')), 
				array (), 'MorgOS_Admin_PageManager', false));
		
		$am->addAction (
			new action ('adminMovePageLevelUp', 'GET',  
				array (&$this, 'onMovePageLevelUp'), array ('pageID'), 
				array (), 'MorgOS_Admin_PageManager', false));
	}
	
	function onMovePageLevelUp ($pageID) {
		$pageM = &$this->_pluginAPI->getPageManager ();
		
		$oldPage = $pageM->newPage ();
		$oldPage->initFromDatabaseID ($pageID);
		$oldParentPage = $oldPage->getParentPage ();
		$newParentPage = $oldParentPage->getParentPage ();
		$oldPage->updateFromArray (array ('parent_page_id'=>$newParentPage->getID (), 'place_in_menu'=>$newParentPage->getMaxPlaceInMenu ()));
		$oldPage->updateToDatabase ();				
		//$this->_pluginAPI->executeAction (); // fill in
		$this->_pluginAPI->executePreviousAction ();
	}
	
	function onMovePageLevelDown ($pageID, $newParentPageID) {
		$pageM = &$this->_pluginAPI->getPageManager ();
		$oldPage = $pageM->newPage ();
		$oldPage->initFromDatabaseID ($pageID);
		$newParentPage = $pageM->newPage ();
		$newParentPage->initFromDatabaseID ($newParentPageID);
		$oldPage->updateFromArray (array ('parent_page_id'=>$newParentPage->getID (), 'place_in_menu'=>$newParentPage->getMaxPlaceInMenu ()));
		$oldPage->updateToDatabase ();				
		//$this->_pluginAPI->executeAction (); // fill in
		$this->_pluginAPI->executePreviousAction ();
	}
	
	function onViewPageManager ($pageID) {
		$sm = &$this->_pluginAPI->getSmarty ();
		$eventM = &$this->_pluginAPI->getEventManager ();
		$pageManager = &$this->_pluginAPI->getPageManager ();
		if ($pageID === NULL) {
			$pageID = 1; /*The ID of /site */
		}
		$parentPage = $pageManager->newPage ();
		$a = $parentPage->initFromDatabaseID ($pageID);
		if (isError ($a)) {
			return $a;
		}
		$childPages = $pageManager->getMenu ($parentPage);
		$sm->assign ('MorgOS_PagesList', $this->_pluginAPI->menuToArray ($childPages));
		$pageLang = $this->_pluginAPI->getDefaultLanguage ();
		$tparent = $parentPage->getTranslation ($pageLang);
		if (! isError ($tparent)) {
			$tparentarray = array ('Title'=>$tparent->getTitle (), 'NavTitle'=>$tparent->getNavTitle (), 'Content'=>$tparent->getContent (), 'ID'=>$parentPage->getID (), 'RootPage'=>$parentPage->isRootPage (), 'PossibleNewParents'=>array ());
			$sm->assign ('MorgOS_ParentPage', $tparentarray);
		} else {
			$sm->assign ('MorgOS_ParentPage', array ('Title'=>'', 'NavTitle'=>'', 'Content'=>'', 'ID'=>$parentPage->getID (), 'RootPage'=>$parentPage->isRootPage ()));
		}
		
		$page = $pageManager->newPage ();
		$page->initFromName ('MorgOS_Admin_PageManager');
		$pID = $page->getID ();		
		
		$curPage = $parentPage;
		$level = array ();
		while ($curPage !== null) {
			if ($curPage->isRootPage () == false) {
				$t = $curPage->getTranslation ($pageLang);
				$level[] = array ('Link'=>'index.php?action=admin&pageID='.$pID.'&parentPageID='.$curPage->getID (), 'Name'=>$t->getNavTitle ());
			} else {
				$level[] = array ('Link'=>'index.php?action=admin&pageID='.$pID.'&parentPageID='.$curPage->getID (), 'Name'=>'Menu');
			}
			$curPage = $curPage->getParentPage (); 
		}
		$level = array_reverse ($level);
		$sm->assign ('MorgOS_PageLevel', $level);
		$eventM->triggerEvent ('viewAnyAdminPage', array (&$pID));
		$sm->appendTo ('MorgOS_AdminPage_Content', 
			$sm->fetch ('admin/page/pagemanager.tpl'));
		$sm->display ('admin/genericpage.tpl'); 
	}
	
	function onMovePageDown ($pageID) {
		$pageManager = &$this->_pluginAPI->getPageManager ();
		$sm = &$this->_pluginAPI->getSmarty ();
		
		$r = $pageManager->movePageDown ($pageID);
		if (! isError ($r)) {
			$this->_pluginAPI->executePreviousAction ();
		} elseif ($r->is ("PAGEMANAGER_PAGE_DOESNT_EXISTS")) {
			$i18nM = &$this->_pluginAPI->getI18NManager ();
			$this->_pluginAPI->error ($i18nM->translate ("Page doesn't exists"), true);
		} else {
			$this->_pluginAPI->error ('Onverwachte fout');
		}
	}
	
	function onMovePageUp ($pageID) {
		$pageManager = &$this->_pluginAPI->getPageManager ();
		
		$r = $pageManager->movePageUp ($pageID);
		if (! isError ($r)) {
			$this->_pluginAPI->executePreviousAction ();
		} elseif ($r->is ("PAGEMANAGER_PAGE_DOESNT_EXISTS")) {
			$i18nM = &$this->_pluginAPI->getI18NManager ();
			$this->_pluginAPI->error ($i18nM->translate ("Page doesn't exists"), true);
		} else {
			$this->_pluginAPI->error ('Onverwachte fout', true);
		}
	}
	
	function onSavePage ($pageID, $pageTitle, $pageNavTitle, $pageContent) {
		$pageManager = &$this->_pluginAPI->getPageManager ();
		$t = &$this->_pluginAPI->getI18NManager ();
		
		$editedPage = $pageManager->newPage ();
		$editedPage->initFromDatabaseID ($pageID);
		$pageLang = $this->_pluginAPI->getUserSetting ('pageLang');
		$tPage = $editedPage->getTranslation ($pageLang);
		$pageContent = secureHTMLInput ($pageContent);
		$tPage->updateFromArray (array ('translated_content'=>$pageContent, 'translated_title'=>$pageTitle, 'translated_nav_title'=>$pageNavTitle));
		$tPage->updateToDatabase ();
		$this->_pluginAPI->addMessage ($t->translate ('Page saved'), NOTICE);
		$a = $this->_pluginAPI->executePreviousAction ();
	}
	
	function onNewPage ($parentPageID, $title) {
		$pageManager = &$this->_pluginAPI->getPageManager ();
		$t = &$this->_pluginAPI->getI18nManager ();		

		$newPage = $pageManager->newPage ();
		$ap = array ('name'=>$title, 'parent_page_id'=>$parentPageID);
		$newPage->initFromArray ($ap);
		$pageManager->addPageToDatabase ($newPage);
		$tNewPage = $pageManager->newTranslatedPage ();
		$pageLang = $this->_pluginAPI->getDefaultLanguage ();
		$a = $tNewPage->initFromArray (array ('translated_title'=>$title, 
			'language_code'=>$pageLang, 'translated_content'=>'Newly created page.'));
		$newPage->addTranslation ($tNewPage);
		$this->_pluginAPI->addRuntimeMessage ($t->translate ('New page created.'), NOTICE);
		$this->onViewPageManager ($newPage->getID (), null);
	}
	
	function onDeletePage ($pageID) {
		$pageManager = &$this->_pluginAPI->getPageManager ();
		$page = $pageManager->newPage ();
		$page->initFromDatabaseID ($pageID);
		$pageManager->removePageFromDatabase ($page);
			
		$a = $this->_pluginAPI->executePreviousAction ();
	}
	
	function install (&$pluginAPI, &$dbModule, $siteDefaultLanguage) {
		$pageM = new pageManager ($dbModule);
		$t = &$pluginAPI->getI18NManager();	
		$admin = $pageM->getAdminPage ();
		$pman = $pageM->newPage ();
		$pman->initFromArray (array (
			'name'=>'MorgOS_Admin_PageManager', 
			'parent_page_id'=>$admin->getID (), 
			'action'=>'adminPageManager'));
		$pageM->addPageToDatabase ($pman);	
		$tPMan = $pageM->newTranslatedPage ();
		$tPMan->initFromArray (array (
			'language_code'=>$siteDefaultLanguage, 
			'translated_title'=>$t->translate ('Page Manager'), 
			'translated_content'=>$t->translate ('Edit pages here.')));
		$pman->addTranslation ($tPMan);
	}
}

?>
