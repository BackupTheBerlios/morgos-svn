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
 * This is the calendarPlugin class.
 *
 * @since 0.2
 * @author Nathan Samson
*/

class calendarPlugin extends plugin {
	
	function calendarPlugin ($dir) {
		parent::plugin ($dir);
		include_once ($dir.'/calendarevent.class.php');
		include_once ($dir.'/calendar.class.php');
		$this->_name = 'Calendar plugin';
		$this->_ID = '{f8db0ac9-13e3-4aa6-9d94-4ee22d6ca974}';
		$this->_minMorgOSVersion = '0.2';
		$this->_maxMorgOSVersion = '0.2';
		$this->_version = '0.1';
	}
	
	function load (&$pluginAPI) {
		parent::load ($pluginAPI);	
		$this->_calendarM = new calendar ($this->_pluginAPI->getDBModule ());		
		
		$em = &$this->_pluginAPI->getEventManager ();
		$am = &$this->_pluginAPI->getActionManager ();
		$em->subscribeToEvent ('viewPage', 
			new callback ('AddMiniCalendar', array ($this, 'addMiniCalendarToSidebar')));
			
		$am->addAction (new Action ('adminCalendarManager', 'GET', array ($this, 'onManageCalendar'),
			array (), array ()));

		$am->addAction (new Action ('adminNewCalendarEvent', 'GET', array ($this, 'onNewEvent'),
			array ('title', 'description',
				'Start_Date_Year', 'Start_Date_Month', 'Start_Date_Day', 'Start_Time_Hour', 'Start_Time_Minute',
				'End_Date_Year', 'End_Date_Month', 'End_Date_Day', 'End_Time_Hour', 'End_Time_Minute'), array ()));
			
		$am->addAction (new Action ('adminEditCalendarEvent', 'GET', array ($this, 'onEditEvent'),
			array ('eventID', 'title', 'description',
				'Start_Date_Year', 'Start_Date_Month', 'Start_Date_Day', 'Start_Time_Hour', 'Start_Time_Minute',
				'End_Date_Year', 'End_Date_Month', 'End_Date_Day', 'End_Time_Hour', 'End_Time_Minute'), array ()));
	}
	
	function install (&$pluginAPI) {
		$db = &$pluginAPI->getDBModule ();
		$pageM = &$pluginAPI->getPageManager ();		
		
		$fTN = $db->getPrefix ().'calendar';
		$sql = "CREATE TABLE $fTN (
				eventID int(11) auto_increment NOT NULL,
				name varchar (255), 
				start datetime,
				end datetime,
				description text,
				PRIMARY KEY (eventID)
			)";
		$q = $db->query ($sql);
		
		$adminRoot = $pageM->newPage ();
		$adminRoot->initFromName ('admin');		
		
		$cMP = $pageM->newPage ();
		$cMP->initFromArray (array ('parentPageID'=>$adminRoot->getID (), 'name'=>'Calendar_Admin_CalendarManager', 
				'action'=>'adminCalendarManager', 'pluginID'=>$this->getID ()));
		$pageM->addPageToDatabase ($cMP);
		$cMPT = $pageM->newTranslatedPage ();
		$cMPT->initFromArray (array ('languageCode'=>'en_UK', 
				'translatedTitle'=>'Manage calendar', 'translatedContent'=>''));
		$cMP->addTranslation ($cMPT);
	}
	
	function unInstall (&$pluginAPI) {
		$db = &$pluginAPI->getDBModule ();
		$fTN = $db->getPrefix ().'calendar';
		$sql = "DROP TABLE $fTN";
		$q = $db->query ($sql);
	}
	
	function isInstalled (&$pluginAPI) {
		$db = &$pluginAPI->getDBModule ();
		$allTables = $db->getAllTables ();
		return in_array ($db->getPrefix ().'calendar', $allTables);
	}
	
	function addMiniCalendarToSidebar () {
		$sm = &$this->_pluginAPI->getSmarty ();
		$calendarM = $this->_calendarM;		
		
		$currentSidebar = $sm->get_template_vars ('Sidebar');
		
		$month = 'Oktober';
		$curMonth = 10;
		$curYear = 2006;
		$firstDayOfMonth = mktime (0, 0, 0, $curMonth, 1, $curYear);	
				
		$dayOfWeek = (int) date ('w', $firstDayOfMonth);

		$firstDayOfWeek = 1;
		$lastDayOfWeek = 7;
		if ($dayOfWeek < $firstDayOfWeek) {
			$dayOfWeek += 7; 
		}
		if ($dayOfWeek == $firstDayOfWeek) {
			$start = $firstDayOfMonth;
		} else {
			$start = $firstDayOfMonth - SECONDS_IN_DAY*($dayOfWeek-$firstDayOfWeek);
		}
		$lastDayOfMonth = mktime (0, 0, 0, $curMonth+1, 0, $curYear);
		$dayOfWeek = (int) date ('w', $lastDayOfMonth);
		if ($dayOfWeek < $firstDayOfWeek) {
			$dayOfWeek += 7; 
		}
		if ($dayOfWeek == $lastDayOfWeek) {
			$end = $lastDayOfMonth;
		} else {
			$end = $lastDayOfMonth + SECONDS_IN_DAY*($lastDayOfWeek-$dayOfWeek);
		}

		$curDay = $start;
		$weeks = array ();
		$i = 1;
		$cur = $start;
		$curWeek =  array ();
		while ($cur <= $end)	{
			$day = $calendarM->getDayArray ($cur);
			$curWeek[] = $day;
			$cur = $calendarM->getNextDay ($cur);
			
			$i++;
			if ($i > 7) {
				$weeks[] = array (
					'Nr'=>(int) date ('W', strtotime ('-1 day', $cur)), 
					'Days'=>$curWeek);
				$curWeek = array ();
				$i = 1;
			}
		}

		$sm->appendTo ('MorgOS_ExtraHead', '<link rel="stylesheet" type="text/css" href="'.$this->getLoadedDir ().'/skins/default/styles/calendar.css'.'" />');
		$sm->assign ('Calendar_Year', $curYear);
		$sm->assign ('Calendar_Month', $month);
		$sm->assign ('Calendar_Weeks', $weeks);
		$sm->appendTo ('MorgOS_ExtraSidebar', $sm->fetch ('minicalendar.tpl'));
		return true;
	}
	
	function onManageCalendar () {
		$sm = &$this->_pluginAPI->getSmarty ();
		$pageM = &$this->_pluginAPI->getPageManager ();
		$em = &$this->_pluginAPI->getEventManager ();
		
		$page = $pageM->newPage ();
		$page->initFromName ('Calendar_Admin_CalendarManager');
		$pageID = $page->getID ();
		if ($this->_pluginAPI->canUserViewPage ($pageID)) {
			$currentEvents = $this->_calendarM->getCurrentEventsArray (10);			
			$upcomingEvents = $this->_calendarM->getUpcomingEventsArray (10);
			
			$em->triggerEvent ('viewAnyAdminPage', array ($pageID, 'en_UK'));
			$sm->assign ('Calendar_CurrentEvents', $currentEvents);
			$sm->assign ('Calendar_UpcomingEvents', $upcomingEvents);
			$sm->display ('admin/manageevents.tpl');
		}
	}
	
	function onNewEvent ($title, $description, 
			$yearStart, $monthStart, $dayStart, $hourStart, $minuteStart,
			$yearEnd, $monthEnd, $dayEnd, $hourEnd, $minuteEnd) {	
		$sm = &$this->_pluginAPI->getSmarty ();
		$pageM = &$this->_pluginAPI->getPageManager ();
		$em = &$this->_pluginAPI->getEventManager ();
		
		$page = $pageM->newPage ();
		$page->initFromName ('Calendar_Admin_CalendarManager');
		$pageID = $page->getID ();
		if ($this->_pluginAPI->canUserViewPage ($pageID)) {
		
			$startSQLTime = $yearStart.'-'.$monthStart.'-'.$dayStart.' '.$hourStart.':'.$minuteStart.':00';
			$endSQLTime = $yearEnd.'-'.$monthEnd.'-'.$dayEnd.' '.$hourEnd.':'.$minuteEnd.':00';
		
			$event = $this->_calendarM->newEvent ();
			$event->initFromArray (array ('start'=>$startSQLTime, 'end'=>$endSQLTime, 'name'=>$title, 'description'=>$description));
			$event->addToDatabase ();
			$this->_pluginAPI->addMessage ('Event succesfully added.', NOTICE);
			$this->_pluginAPI->executePreviousAction ();
		}
	}
	
	function onEditEvent () {
	}
}
?>
