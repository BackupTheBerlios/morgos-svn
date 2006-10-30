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
 * This is the mailListPlugin class.
 *
 * @since 0.2
 * @author Sam Heijens
*/
class mailListPlugin extends plugin 
  {
  function mailListPlugin ($dir) 
    {
    parent::plugin ($dir);
	$this->_name = 'Mailinglist Plugin';
	$this->_ID = '{263bd2e5-a996-461a-b5e9-c2aec9d38b47}';
	$this->_minMorgOSVersion = '0.3';
    $this->_maxMorgOSVersion = '0.3';
	$this->_version = '1.1';
	}
	
  function load (&$pluginAPI) 
    {
     include_once ($this->getLoadedDir ().'/mailingList.class.php');
     include_once ($this->getLoadedDir ().'/mailingMember.class.php');
	parent::load ($pluginAPI);
	$executor = array ($this, 'onViewManager');
    $requiredOptions = array ();
    $smth = new action('adminMailingListManager','GET', $executor, $requiredOptions);
    $actionmanager = &$pluginAPI->getActionManager();
    $actionmanager->addAction($smth);
	}
  
  function onViewManager()
    {
    $sm = &$this->_pluginAPI->getSmarty ();
    $pageM = &$this->_pluginAPI->getPageManager ();
    $em = &$this->_pluginAPI->getEventManager ();
    $page = $pageM->newPage ();
    $page->initFromName ('MailingList_Admin_Manager');
    $pageID = $page->getID ();
    if ($this->_pluginAPI->canUserViewPage ($pageID)) 
      {
      $em->triggerEvent ('viewAnyAdminPage', array ($pageID, 'en_UK'));
      $sm->display('admin/listmanager.tpl');
      }
    else 
      {
      $this->_pluginAPI->addRuntimeMessage ('Login as a valid admin user to view this page.', NOTICE);
      $sm->display('admin/login.tpl');
      }
    }
	
  function install (&$pluginAPI) 
	{
	$db = &$pluginAPI->getDBModule();
	$prefix = $db->getPrefix();
	$query1 = "CREATE TABLE {$prefix}mailingMembers (userID INT( 11 ) NOT NULL AUTO_INCREMENT ,userName VARCHAR( 255 ) NOT NULL ,emailAdres VARCHAR( 255 ) NOT NULL ,listID INT( 11 ) NOT NULL ,PRIMARY KEY ( userID ))";
	$query2 = "CREATE TABLE {$prefix}mailingLists (listID INT( 11 ) NOT NULL AUTO_INCREMENT ,listName VARCHAR( 255 ) NOT NULL ,PRIMARY KEY ( listID ))";
	$r1 = $db->query($query1);
	$r2 = $db->query($query2);
	$pageM = &$pluginAPI->getPageManager();
	$adminRoot = $pageM->newPage();
    $adminRoot->initFromName ('admin');
    $pID = $adminRoot->getID();
    $mainPage = $pageM->newPage();
	$mainPage->initFromArray (array ('parentPageID'=>$pID, 'name'=>'MailingList_Admin_Manager', 'action'=>'adminMailingListManager', 'pluginID'=>$this->getID ()));
	$pageM ->addPageToDatabase ($mainPage);
	$transPage = $pageM->newTranslatedPage();
    $transPage->initFromArray (array ('languageCode'=>'en_UK', 'translatedTitle'=>'Mailinglist Manager', 'translatedContent'=>'This is the Mailinglist Manager'));
    $mainPage->addTranslation ($transPage);
    return true;
	}
	
  function unInstall (&$pluginAPI) 
	{
	$db = &$pluginAPI->getDBModule();
	$prefix = $db->getPrefix();
	$query1 = "DROP TABLE {$prefix}mailingLists";
	$query2 = "DROP TABLE {$prefix}mailingMembers";
	$r1 = $db->query($query1);
	$r2 = $db->query($query2);
	$pageM = $pluginAPI->getPageManager();
	$mainPage = $pageM->newPage ();    
	$mainPage->initFromName ('MailingList_Admin_Manager');
	$pageM->removePageFromDatabase ($mainPage);
	return true;
	}
	
  function isInstalled (&$pluginAPI) 
	{
	$db = &$pluginAPI->getDBModule ();
    $listTable = $db->tableExists ('mailingLists');
    $memberTable = $db->tableExists ('mailingMembers');
    
    if ($listTable and $memberTable) 
      {
      return true;
      } 
    else 
      {
      return false;
      }
	}	
  }
  
  
  /* dit weet ik nog niet waar die moet:

    function addMember($emailAdres,$memberName,$listID)
    {
    $member->initFromArray ($emailAdres=>$memberEmail, $memberName=>$memberName, $listID=>$listID);
    $member->addToDatabase();
    }
    
    function removeMember($emailAdres,$memberName,$listID)
    {
    $member->initFromArray ($emailAdres=>$memberEmail, $memberName=>$memberName, $listID=>$listID);
    $member->removeFromDatabase();
    }
    
    */
?>