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

class viewPageCoreAdminPlugin extends plugin {

	function viewPageCoreAdminPlugin ($dir) {
		parent::plugin ($dir);
	}
	
	function load (&$pluginAPI) {
		parent::load ($pluginAPI);
		
		$am = &$this->_pluginAPI->getActionManager ();
		$em = &$this->_pluginAPI->getEventManager ();
		
		$am->addAction (
			new action ('adminPageManager', 'GET',  array (&$this, 'onViewPageManager'), 
			array (), array (new IDInput ('pageID'), new LocaleInput ('pageLang'))));		
		
		// page edit action
		$am->addAction (
			new action ('adminMovePageDown', 'GET',  
				array (&$this, 'onMovePageDown'), new IDInput ('pageID'), array ()));
				
		$am->addAction (
			new action ('adminMovePageUp', 'GET',  
				array (&$this, 'onMovePageUp'), new IDInput ('pageID'), array ()));
				
		$am->addAction (
			new action ('adminSavePage', 'POST',  
				array (&$this, 'onSavePage'), array (new IDInput ('pageID'), new StringInput ('pageTitle'), new StringInput ('pageNavTitle'), new StringInput ('pageContent')), array ()));
				
		$am->addAction (
			new action ('adminNewPage', 'GET',  
				array (&$this, 'onNewPage'), array (new IDInput ('parentPageID'), 'pageTitle'), array ()));
				
		$am->addAction (
			new action ('adminDeletePage', 'GET',  
				array (&$this, 'onDeletePage'), array (new IDInput ('pageID')), array ()));
				
		$am->addAction (
			new action ('adminAddPageToMenu', 'GET',  
				array (&$this, 'onDeletePage'), array (new IDInput ('pageID')), array ()));
				
		$am->addAction (
			new action ('adminRemovePageFromMenu', 'GET',  
				array (&$this, 'onDeletePage'), array (new IDInput ('pageID')), array ()));
				
		$am->addAction (
			new action ('adminMovePageLevelDown', 'GET',  
				array (&$this, 'onMovePageLevelDown'), array (new IDInput ('pageID'), new IDInput ('newParentPageID')), array ()));
		
		$am->addAction (
			new action ('adminMovePageLevelUp', 'GET',  
				array (&$this, 'onMovePageLevelUp'), array ('pageID'), array ()));
	}
	
	function onMovePageLevelUp ($pageID) {
		$em = &$this->_pluginAPI->getEventManager ();
		$sm = &$this->_pluginAPI->getSmarty ();
		$pageM = &$this->_pluginAPI->getPageManager ();
		$page = $pageM->newPage ();			
		$page->initFromName ('MorgOS_Admin_PageManager');
		$em->triggerEvent ('viewAnyAdminPage', array ($page->getID ()));
		if ($this->_pluginAPI->canUserViewPage ($page->getID ())) {				
					$em = &$this->_pluginAPI->getEventManager ();
		$sm = &$this->_pluginAPI->getSmarty ();
		$page = $pageM->newPage ();			
		$page->initFromName ('MorgOS_Admin_PageManager');
		if ($this->_pluginAPI->canUserViewPage ($page->getID ())) {
			$oldPage = $pageM->newPage ();
			$oldPage->initFromDatabaseID ($pageID);
			$oldParentPage = $oldPage->getParentPage ();
			$newParentPage = $oldParentPage->getParentPage ();
			$oldPage->updateFromArray (array ('parentPageID'=>$newParentPage->getID (), 'placeInMenu'=>$newParentPage->getMaxPlaceInMenu ()));
			$oldPage->updateToDatabase ();				
			//$this->_pluginAPI->executeAction (); // fill in
			$this->_pluginAPI->executePreviousAction ();
		} else {
			$this->_pluginAPI->addRuntimeMessage ('Login as a valid admin user to view this page.', NOTICE);
			$sm->display ('admin/login.tpl');
		}
		} else {
			$this->_pluginAPI->addRuntimeMessage ('Login as a valid admin user to view this page.', NOTICE);
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onMovePageLevelDown ($pageID, $newParentPageID) {
		$em = &$this->_pluginAPI->getEventManager ();
		$sm = &$this->_pluginAPI->getSmarty ();
		$pageM = &$this->_pluginAPI->getPageManager ();
		$page = $pageM->newPage ();			
		$page->initFromName ('MorgOS_Admin_PageManager');
		$em->triggerEvent ('viewAnyAdminPage', array ($page->getID ()));
		if ($this->_pluginAPI->canUserViewPage ($page->getID ())) {				
					$em = &$this->_pluginAPI->getEventManager ();
		$sm = &$this->_pluginAPI->getSmarty ();
		$page = $pageM->newPage ();			
		$page->initFromName ('MorgOS_Admin_PageManager');
		if ($this->_pluginAPI->canUserViewPage ($page->getID ())) {
			$oldPage = $pageM->newPage ();
			$oldPage->initFromDatabaseID ($pageID);
			$newParentPage = $pageM->newPage ();
			$newParentPage->initFromDatabaseID ($newParentPageID);
			$oldPage->updateFromArray (array ('parentPageID'=>$newParentPage->getID (), 'placeInMenu'=>$newParentPage->getMaxPlaceInMenu ()));
			$oldPage->updateToDatabase ();				
			//$this->_pluginAPI->executeAction (); // fill in
			$this->_pluginAPI->executePreviousAction ();
		} else {
			$this->_pluginAPI->addRuntimeMessage ('Login as a valid admin user to view this page.', NOTICE);
			$sm->display ('admin/login.tpl');
		}
		} else {
			$this->_pluginAPI->addRuntimeMessage ('Login as a valid admin user to view this page.', NOTICE);
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onViewPageManager ($pageID, $pageLang) {
		$em = &$this->_pluginAPI->getEventManager ();
		$sm = &$this->_pluginAPI->getSmarty ();
		$pageManager = &$this->_pluginAPI->getPageManager ();
		$page = $pageManager->newPage ();			
		$page->initFromName ('MorgOS_Admin_PageManager');
		$em->triggerEvent ('viewAnyAdminPage', array ($page->getID ()));
		if ($this->_pluginAPI->canUserViewPage ($page->getID ())) {				
			if ($pageID === NULL) {
				$pageID = 1; /*The ID of site */
			}	
			$parentPage = $pageManager->newPage ();
			$a = $parentPage->initFromDatabaseID ($pageID);
			if (isError ($a)) {
				return $a;
			}
			$childPages = $pageManager->getMenu ($parentPage);
			$sm->assign ('MorgOS_PagesList', $this->_pluginAPI->menuToArray ($childPages));
			$tparent = $parentPage->getTranslation ('en_UK');
			if (! isError ($tparent)) {
				$tparentarray = array ('Title'=>$tparent->getTitle (), 'NavTitle'=>$tparent->getNavTitle (), 'Content'=>$tparent->getContent (), 'ID'=>$parentPage->getID (), 'RootPage'=>$parentPage->isRootPage (), 'PossibleNewParents'=>array ());
				$sm->assign ('MorgOS_ParentPage', $tparentarray);
			} else {
				$sm->assign ('MorgOS_ParentPage', array ('Title'=>'', 'NavTitle'=>'', 'Content'=>'', 'ID'=>$parentPage->getID (), 'RootPage'=>$parentPage->isRootPage ()));
			}
			
			if ($pageLang == null) {
				$pageLang = 'en_UK';
			}
			$tpage = $page->getTranslation ($pageLang);
			$tpagearray = array ('Title'=>$tpage->getTitle (), 'Content'=>$tpage->getContent ());
			$sm->assign ('MorgOS_CurrentAdminPage', $tpagearray);
			$curPage = $parentPage;
			$level = array ();
			while ($curPage !== null) {
				if ($curPage->isRootPage () == false) {
					$t = $curPage->getTranslation ($pageLang);
					$level[] = array ('Link'=>'index.php?action=adminPageManager&pageID='.$curPage->getID (), 'Name'=>$t->getNavTitle ());
				} else {
					$level[] = array ('Link'=>'index.php?action=adminPageManager&pageID='.$curPage->getID (), 'Name'=>'Menu');
				}
				$curPage = $curPage->getParentPage (); 
			}
			$level = array_reverse ($level);
			$sm->assign ('MorgOS_PageLevel', $level);
			$sm->display ('admin/pagemanager.tpl'); 
		} else {
			$this->_pluginAPI->addRuntimeMessage ('Login as a valid admin user to view this page.', NOTICE);
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onMovePageDown ($pageID) {
		$pageManager = &$this->_pluginAPI->getPageManager ();
		$page = $pageManager->newPage ();			
		$page->initFromName ('MorgOS_Admin_PageManager');
		$sm = &$this->_pluginAPI->getSmarty ();
		if ($this->_pluginAPI->canUserViewPage ($page->getID ())) {	
			$r = $pageManager->movePageDown ($pageID);
			if (! isError ($r)) {
				$this->_pluginAPI->executePreviousAction ();
			} elseif ($r->is ("PAGEMANAGER_PAGE_DOESNT_EXISTS")) {
				$i18nM = &$this->_pluginAPI->getI18NManager ();
				$this->_pluginAPI->error ($i18nM->translate ('Page doesn\'t exists'), true);
			} else {
				$this->_pluginAPI->error ('Onverwachte fout');
			}
		} else {
			$this->_pluginAPI->addRuntimeMessage ('Login as a valid admin user to view this page.', NOTICE);
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onMovePageUp ($pageID) {
		$pageManager = &$this->_pluginAPI->getPageManager ();
		$page = $pageManager->newPage ();			
		$page->initFromName ('MorgOS_Admin_PageManager');
		$sm = &$this->_pluginAPI->getSmarty ();
		if ($this->_pluginAPI->canUserViewPage ($page->getID ())) {	
			$r = $pageManager->movePageUp ($pageID);
			if (! isError ($r)) {
				$this->_pluginAPI->executePreviousAction ();
			} elseif ($r->is ("PAGEMANAGER_PAGE_DOESNT_EXISTS")) {
				$i18nM = &$this->_pluginAPI->getI18NManager ();
				$this->_pluginAPI->error ($i18nM->translate ('Page doesn\'t exists'), true);
			} else {
				$this->_pluginAPI->error ('Onverwachte fout', true);
			}
		} else {
			$this->_pluginAPI->addRuntimeMessage ('Login as a valid admin user to view this page.', NOTICE);
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onSavePage ($pageID, $pageTitle, $pageNavTitle, $pageContent) {
		$pageManager = &$this->_pluginAPI->getPageManager ();
		$page = &$pageManager->newPage ();			
		$page->initFromName ('MorgOS_Admin_PageManager');
		$sm = &$this->_pluginAPI->getSmarty ();
		$t = &$this->_pluginAPI->getI18NManager ();
		if ($this->_pluginAPI->canUserViewPage ($page->getID ())) {	
			$editedPage = $pageManager->newPage ();
			$editedPage->initFromDatabaseID ($pageID);
			$tPage = $editedPage->getTranslation ('en_UK');
			$pageContent = secureHTMLInput ($pageContent);
			$tPage->updateFromArray (array ('translatedContent'=>$pageContent, 'translatedTitle'=>$pageTitle, 'translatedNavTitle'=>$pageNavTitle));
			$tPage->updateToDatabase ();
			$this->_pluginAPI->addMessage ($t->translate ('Page saved'), NOTICE);
			$a = $this->_pluginAPI->executePreviousAction ();
		} else {
			$this->_pluginAPI->addRuntimeMessage ('Login as a valid admin user to view this page.', NOTICE);
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onNewPage ($parentPageID, $title) {
		$pageManager = &$this->_pluginAPI->getPageManager ();
		$page = &$pageManager->newPage ();			
		$page->initFromName ('MorgOS_Admin_PageManager');
		$sm = $this->_pluginAPI->getSmarty ();
		$t = $this->_pluginAPI->getI18nManager ();		
		
		if ($this->_pluginAPI->canUserViewPage ($page->getID ())) {	
			$newPage = $pageManager->newPage ();
			$i18nM = &$this->_pluginAPI->getI18NManager ();
			$ap = array ('name'=>$title, 'parentPageID'=>$parentPageID);
			$newPage->initFromArray ($ap);
			$pageManager->addPageToDatabase ($newPage);
			$tNewPage = $pageManager->newTranslatedPage ();
			$a = $tNewPage->initFromArray (array ('translatedTitle'=>$title, 'languageCode'=>'en_UK', 'translatedContent'=>'Newly created page.'));
			$newPage->addTranslation ($tNewPage);
			//$a = $this->_pluginAPI->executePreviousAction ();
			$this->_pluginAPI->addRuntimeMessage ($t->translate ('New page created.'), NOTICE);
			$this->onViewPageManager ($newPage->getID (), null);
		} else {
			$this->_pluginAPI->addRuntimeMessage ('Login as a valid admin user to view this page.', NOTICE);
			$sm->display ('admin/login.tpl');
		}
	}
	
	function onDeletePage ($pageID) {
		$pageManager = &$this->_pluginAPI->getPageManager ();
		$page = &$pageManager->newPage ();			
		$page->initFromName ('MorgOS_Admin_PageManager');
		$sm = $this->_pluginAPI->getSmarty ();
		if ($this->_pluginAPI->canUserViewPage ($page->getID ())) {	
			$page = $pageManager->newPage ();
			$page->initFromDatabaseID ($pageID);
			$pageManager->removePageFromDatabase ($page);
			
			$a = $this->_pluginAPI->executePreviousAction ();
		} else {
			$this->_pluginAPI->addRuntimeMessage ('Login as a valid admin user to view this page.', NOTICE);
			$sm->display ('admin/login.tpl');
		}	
	}
}

?>