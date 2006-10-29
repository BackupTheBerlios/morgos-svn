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
		$t = &$this->_pluginAPI->getI18NManager ();
		$t->loadTranslation ($this->getLoadedDir ().'/i18n');		
			
		$this->_calendarM = new calendar ($this->_pluginAPI->getDBModule ());		
		
		$em = &$this->_pluginAPI->getEventManager ();
		$am = &$this->_pluginAPI->getActionManager ();
		$em->subscribeToEvent ('viewPage', 
			new callback ('AddMiniCalendar', array ($this, 'addMiniCalendarToSidebar'), array ('pageID')));
			
		$am->addAction (new Action ('adminCalendarManager', 'GET', array ($this, 'onManageCalendar'),
			array (), array ()));

		$am->addAction (new Action ('adminNewCalendarEvent', 'GET', array ($this, 'onNewEvent'),
			array ('title', 'description',
				'Start_Date_Year', 'Start_Date_Month', 'Start_Date_Day', 'Start_Time_Hour', 'Start_Time_Minute',
				'End_Date_Year', 'End_Date_Month', 'End_Date_Day', 'End_Time_Hour', 'End_Time_Minute', 'groupID'), array ()));
				
		$am->addAction (new Action ('adminDeleteCalendarEvent', 'GET', array ($this, 'onDeleteEvent'),
			array (new IDInput ('eventID')), array ()));
			
		$am->addAction (new Action ('adminEditCalendarEvent', 'GET', array ($this, 'onEditEvent'),
			array ('eventID', 'title', 'description',
				'Start_Date_Year', 'Start_Date_Month', 'Start_Date_Day', 'Start_Time_Hour', 'Start_Time_Minute',
				'End_Date_Year', 'End_Date_Month', 'End_Date_Day', 'End_Time_Hour', 'End_Time_Minute', 'groupID'), array ()));
				
		$am->addAction (new Action ('adminEditCalendarEventForm', 'GET', array ($this, 'onEditEventForm'),
			array (), array (new IDInput ('eventID'))));
		
		$am->addAction (new Action ('adminNewCalendarGroup', 'GET', array ($this, 'onNewGroup'),
			array (new StringInput ('groupName'), new StringInput ('groupColor')), array ()));
		$am->addAction (new Action ('adminDeleteCalendarGroup', 'GET', array ($this, 'onDeleteGroup'),
			array (new IDInput ('groupID')), array ()));
		$am->addAction (new Action ('adminEditCalendarGroupForm', 'GET', array ($this, 'onEditGroupForm'),
			array (new IDInput ('groupID')), array ()));
		$am->addAction (new Action ('adminEditCalendarGroup', 'POST', array ($this, 'onEditGroup'),
			array (new IDInput ('groupID'), new StringInput ('groupName'), new StringInput ('groupColor')), array ()));
				
		$am->addAction (new Action ('calendarMonthView', 'GET', array ($this, 'onMonthView'),
			array (), array (new IntInput ('month'), new IntInput ('year'), new IDInput ('eventID'))));
	}
	
	function install (&$pluginAPI) {
		$db = &$pluginAPI->getDBModule ();
		$pageM = &$pluginAPI->getPageManager ();		
		$t = &$pluginAPI->getI18NManager ();
		$t->loadTranslation ($this->getLoadedDir ().'/i18n');
		
		$fTN = $db->getPrefix ().'calendar';
		$sql = "CREATE TABLE $fTN (
				eventID int(11) auto_increment NOT NULL,
				name varchar (255), 
				start datetime,
				end datetime,
				description text,
				groupID int(11),
				PRIMARY KEY (eventID)
			)";
		$q = $db->query ($sql);
		
		$fTN = $db->getPrefix ().'calendarGroup';
		$sql = "CREATE TABLE $fTN (
				groupID int(11) auto_increment NOT NULL,
				name varchar (255), 
				color varchar (15),
				PRIMARY KEY (groupID)
			)";
		$q = $db->query ($sql);
		
		/*$fTN = $db->getPrefix ().'calendarGroupEvents';
		$sql = "CREATE TABLE $fTN (
				groupID int(11) NOT NULL,
				eventID int(11) NOT NULL,
				PRIMARY KEY (groupID, eventID)
			)";
		$q = $db->query ($sql);*/
		
		$adminRoot = $pageM->newPage ();
		$adminRoot->initFromName ('admin');	
		
		$menuRoot = $pageM->newPage ();
		$menuRoot->initFromName ('site');	
		
		$cMP = $pageM->newPage ();
		$cMP->initFromArray (array ('parentPageID'=>$adminRoot->getID (), 'name'=>'Calendar_Admin_CalendarManager', 
				'action'=>'adminCalendarManager', 'pluginID'=>$this->getID ()));
		$pageM->addPageToDatabase ($cMP);
		$cMPT = $pageM->newTranslatedPage ();
		$cMPT->initFromArray (array ('languageCode'=>'en_UK', 
				'translatedTitle'=>$t->translate ('Manage calendar'), 'translatedContent'=>''));
		$cMP->addTranslation ($cMPT);
		
		$cMP = $pageM->newPage ();
		$cMP->initFromArray (array ('parentPageID'=>$menuRoot->getID (), 'name'=>'Calendar_CalendarMonthView', 
				'action'=>'calendarMonthView', 'pluginID'=>$this->getID ()));
		$pageM->addPageToDatabase ($cMP);
		$cMPT = $pageM->newTranslatedPage ();
		$cMPT->initFromArray (array ('languageCode'=>'en_UK', 
				'translatedTitle'=>$t->translate ('Calendar'), 'translatedContent'=>''));
		$cMP->addTranslation ($cMPT);
	}
	
	function unInstall (&$pluginAPI) {
		$db = &$pluginAPI->getDBModule ();
		$fTN = $db->getPrefix ().'calendar';
		$sql = "DROP TABLE $fTN";
		$q = $db->query ($sql);
		$fTN = $db->getPrefix ().'calendarGroup';
		$sql = "DROP TABLE $fTN";
		$q = $db->query ($sql);
		/*$fTN = $db->getPrefix ().'calendarGroupEvents';
		$sql = "DROP TABLE $fTN";
		$q = $db->query ($sql);*/
		
		$pageM = &$pluginAPI->getPageManager ();
		$page = $pageM->newPage ();
		$page->initFromName ('Calendar_Admin_CalendarManager');
		$pageM->removePageFromDatabase ($page);
		
		$page = $pageM->newPage ();
		$page->initFromName ('Calendar_CalendarMonthView');
		$pageM->removePageFromDatabase ($page);

	}
	
	function isInstalled (&$pluginAPI) {
		$db = &$pluginAPI->getDBModule ();
		$allTables = $db->getAllTables ();
		return in_array ($db->getPrefix ().'calendar', $allTables);
	}
	
	function addMiniCalendarToSidebar ($pageID) {
		$sm = &$this->_pluginAPI->getSmarty ();
		$calendarM = $this->_calendarM;
		$pageM = &$this->_pluginAPI->getPageManager ();
		
		$currentSidebar = $sm->get_template_vars ('Sidebar');
		
		$month = 'Oktober';
		$curMonth = $this->getCurrentMonth ();
		$curYear = $this->getCurrentYear ();
				
		$weeks = $this->buildWeeksArray ($curMonth, $curYear, 1);

			$groups = array ();
			foreach ($this->_calendarM->getAllGroups () as $group) {
				$groups[] = array ('Color'=>$group->getColor (), 'Name'=>$group->getName ());
			}
			$sm->assign ('Calendar_Groups', $groups);

		$sm->appendTo ('MorgOS_ExtraHead', '<link rel="stylesheet" type="text/css" href="'.$this->getSkinDir ().'/styles/calendar.css'.'" />');
		$sm->assign ('Calendar_Year', $curYear);
		$sm->assign ('Calendar_Month', $month);
		$sm->assign ('Calendar_Weeks', $weeks);
		$sm->appendTo ('MorgOS_ExtraSidebar', $sm->fetch ('minicalendar.tpl'));
		
		$home = $pageM->newPage ();
		$home->initFromName ('MorgOS_Home');
		if ($pageID == $home->getID ()) {
			$eventMessages = $calendarM->getUpcomingEvents (2);
			$eventMessagesString = '';
			foreach ($eventMessages as $event) {
				$sm->assign ('UpcomingEventMessage', $this->_calendarM->event2Array ($event));
				$eventMessagesString .= $sm->fetch ('eventmessage.tpl');
				$sm->assign ('UpcomingEventMessage', '');
			}
			$sm->prependTo ('MorgOS_CurrentPage_Content', $eventMessagesString);
		}
		
		return true;
	}
	
	function onManageCalendar () {
		$sm = &$this->_pluginAPI->getSmarty ();
		$pageM = &$this->_pluginAPI->getPageManager ();
		$em = &$this->_pluginAPI->getEventManager ();
		$t = &$this->_pluginAPI->getI18NManager ();
		
		$page = $pageM->newPage ();
		$page->initFromName ('Calendar_Admin_CalendarManager');
		$pageID = $page->getID ();
		if ($this->_pluginAPI->canUserViewPage ($pageID)) {
			$currentEvents = $this->_calendarM->getCurrentEventsArray (10);			
			$upcomingEvents = $this->_calendarM->getUpcomingEventsArray (10);
			
			$em->triggerEvent ('viewAnyAdminPage', array ($pageID, 'en_UK'));
			$sm->assign ('Calendar_CurrentEvents', $currentEvents);
			$sm->assign ('Calendar_UpcomingEvents', $upcomingEvents);
			$sm->assign ('Calendar_AvGroups', $this->getAvGroups ());
			$groups = array ();
			foreach ($this->_calendarM->getAllGroups () as $group) {
				$groups[] = $this->_calendarM->group2Array ($group);
			}
			$sm->assign ('Calendar_Groups', $groups);
			$sm->appendTo ('MorgOS_ExtraHead', '<link rel="stylesheet" type="text/css" href="'.$this->getSkinDir ().'/styles/calendar.css'.'" />');
			$sm->display ('admin/manageevents.tpl');
		}
	}
	
	function onNewEvent ($title, $description, 
			$yearStart, $monthStart, $dayStart, $hourStart, $minuteStart,
			$yearEnd, $monthEnd, $dayEnd, $hourEnd, $minuteEnd, $groupID) {	
		$sm = &$this->_pluginAPI->getSmarty ();
		$pageM = &$this->_pluginAPI->getPageManager ();
		$em = &$this->_pluginAPI->getEventManager ();
		$t = &$this->_pluginAPI->getI18NManager ();
		
		$page = $pageM->newPage ();
		$page->initFromName ('Calendar_Admin_CalendarManager');
		$pageID = $page->getID ();
		if ($this->_pluginAPI->canUserViewPage ($pageID)) {
		
			$startSQLTime = $yearStart.'-'.$monthStart.'-'.$dayStart.' '.$hourStart.':'.$minuteStart.':00';
			$endSQLTime = $yearEnd.'-'.$monthEnd.'-'.$dayEnd.' '.$hourEnd.':'.$minuteEnd.':00';
		
			$event = $this->_calendarM->newEvent ();
			$event->initFromArray (array ('start'=>$startSQLTime, 'end'=>$endSQLTime, 'name'=>$title, 'description'=>$description, 'groupID'=>$groupID));
			$event->addToDatabase ();
			$this->_pluginAPI->addMessage ($t->translate ('Event succesfully added.'), NOTICE);
			$this->_pluginAPI->executePreviousAction ();
		}
	}
	
	function onDeleteEvent ($eventID) {
		$pageM = &$this->_pluginAPI->getPageManager ();
		$em = &$this->_pluginAPI->getEventManager ();
		$t = &$this->_pluginAPI->getI18NManager ();
		
		$page = $pageM->newPage ();
		$page->initFromName ('Calendar_Admin_CalendarManager');
		$pageID = $page->getID ();
		if ($this->_pluginAPI->canUserViewPage ($pageID)) {
			$event = $this->_calendarM->newEvent ();
			$event->initFromDatabaseID ($eventID);
			$event->removeFromDatabase ();
			$this->_pluginAPI->addMessage ($t->translate ('Event succesfully deleted.'), NOTICE);
			$this->_pluginAPI->executePreviousAction ();
		}
	}
	
	function onNewGroup ($groupName, $groupColor) {
		$pageM = &$this->_pluginAPI->getPageManager ();
		$em = &$this->_pluginAPI->getEventManager ();
		$t = &$this->_pluginAPI->getI18NManager ();
		
		$page = $pageM->newPage ();
		$page->initFromName ('Calendar_Admin_CalendarManager');
		$pageID = $page->getID ();
		if ($this->_pluginAPI->canUserViewPage ($pageID)) {
			$group = $this->_calendarM->newGroup ();
			$group->initFromArray (array ('name'=>$groupName, 'color'=>$groupColor));
			$this->_calendarM->addGroupToDatabase ($group);
			$this->_pluginAPI->addMessage ($t->translate ('Group succesfully added.'), NOTICE);
			$this->_pluginAPI->executePreviousAction ();
		}
	}
	
	function onDeleteGroup ($groupID) {
		$pageM = &$this->_pluginAPI->getPageManager ();
		$em = &$this->_pluginAPI->getEventManager ();
		$t = &$this->_pluginAPI->getI18NManager ();
		
		$page = $pageM->newPage ();
		$page->initFromName ('Calendar_Admin_CalendarManager');
		$pageID = $page->getID ();
		if ($this->_pluginAPI->canUserViewPage ($pageID)) {
			$group = $this->_calendarM->newGroup ();
			$group->initFromDatabaseID ($groupID);
			$group->removeFromDatabase ();
			$this->_pluginAPI->addMessage ($t->translate ('Group succesfully deleted.'), NOTICE);
			$this->_pluginAPI->executePreviousAction ();
		}
	}
	
	function onEditGroup ($groupID, $newName, $newColor) {
		$pageM = &$this->_pluginAPI->getPageManager ();
		$t = &$this->_pluginAPI->getI18NManager ();
		
		$page = $pageM->newPage ();
		$page->initFromName ('Calendar_Admin_CalendarManager');
		$pageID = $page->getID ();
		if ($this->_pluginAPI->canUserViewPage ($pageID)) {
			$group = $this->_calendarM->newGroup ();
			$group->initFromDatabaseID ($groupID);
			$group->updateFromArray (array ('name'=>$newName, 'color'=>$newColor));
			$group->updateToDatabase ();
			$this->_pluginAPI->addMessage ($t->translate ('Group succesfully updated.'), NOTICE);
			$this->_pluginAPI->executePreviousAction ();
		}
	}
	
	function onMonthView ($month, $year, $eventID) {
		$sm = &$this->_pluginAPI->getSmarty ();
		$pageM = &$this->_pluginAPI->getPageManager ();
		$em = &$this->_pluginAPI->getEventManager ();
		
		if ($month == null) {
			$month = $this->getCurrentMonth ();
		}
		
		if ($year == null) {
			$year = $this->getCurrentYear ();
		}
		
		$weeks = $this->buildWeeksArray ($month, $year, 1);
		$monthView = $pageM->newPage ();
		$monthView->initFromName ('Calendar_CalendarMonthView');
		$em->triggerEvent ('viewPage', array ($monthView->getID (), 'en_UK'));
		$prevLink = 'index.php?action=calendarMonthView&year='.($year-1).'&month='.$month;
		$nextLink = 'index.php?action=calendarMonthView&year='.($year+1).'&month='.$month;
		$sm->assign ('Calendar_Year', array ('Text'=>$year, 'PreviousLink'=>$prevLink, 'NextLink'=>$nextLink));
		
		if ($eventID != null) {
			$event = $this->_calendarM->newEvent ();
			$event->initFromDatabaseID ($eventID);
			$sm->assign ('Calendar_CurrentEvent', $this->_calendarM->event2Array ($event));
		}
		
		$prevYear = $year;
		$prevMonth = $month-1;		
		if ($prevMonth < 1) {
			$prevYear--;
			$prevMonth = 12;
		}
		
		$nextYear = $year ;
		$nextMonth = $month+1;		
		if ($nextMonth > 12) {
			$nextYear++;
			$nextMonth = 1;
		}
		
		$prevLink = 'index.php?action=calendarMonthView&year='.$prevYear.'&month='.$prevMonth;
		$nextLink = 'index.php?action=calendarMonthView&year='.$nextYear.'&month='.$nextMonth;
		$sm->assign ('Calendar_Month', array ('Text'=>$this->getMonthName ($month), 'PreviousLink'=>$prevLink, 'NextLink'=>$nextLink));
		$sm->assign ('Calendar_Weeks', $weeks);
		$sm->assign ('Calendar_WeekDays', $this->getWeekDays (1));
		$events = $this->_calendarM->getAllEvents ();
		array_walk ($events, array ($this->_calendarM, 'event2Array'));
		$sm->assign ('Calendar_Events', $events);
		$sm->appendTo ('MorgOS_ExtraHead', '<script type="text/javascript">'.$sm->fetch ('js/eventListing.js.tpl').'</script>');
		$sm->display ('monthview.tpl');
	}
	
	function onEditEvent ($eventID, $title, $description, 
			$yearStart, $monthStart, $dayStart, $hourStart, $minuteStart,
			$yearEnd, $monthEnd, $dayEnd, $hourEnd, $minuteEnd, $groupID) {
			
		$pageM = &$this->_pluginAPI->getPageManager ();
		$sm = &$this->_pluginAPI->getSmarty ();
		$em = &$this->_pluginAPI->getEventManager ();
		$t = &$this->_pluginAPI->getI18NManager ();
		
		$page = $pageM->newPage ();
		$page->initFromName ('Calendar_Admin_CalendarManager');
		if ($this->_pluginAPI->canUserViewPage ($page->getID ())) {
			
			$event = $this->_calendarM->newEvent ();
			$event->initFromDatabaseID ($eventID);			
			$startSQLTime = $yearStart.'-'.$monthStart.'-'.$dayStart.' '.$hourStart.':'.$minuteStart.':00';
			$endSQLTime = $yearEnd.'-'.$monthEnd.'-'.$dayEnd.' '.$hourEnd.':'.$minuteEnd.':00';
		
			$event->updateFromArray (array ('start'=>$startSQLTime, 'end'=>$endSQLTime, 'name'=>$title, 'description'=>$description, 'groupID'=>$groupID));
			$event->updateToDatabase ();
			$this->_pluginAPI->addMessage ($t->translate ('Event saved.'), NOTICE);
			$this->_pluginAPI->executePreviousAction ();
		}
	}
	
	function onEditEventForm ($eventID) {
		$pageM = &$this->_pluginAPI->getPageManager ();
		$sm = &$this->_pluginAPI->getSmarty ();
		$em = &$this->_pluginAPI->getEventManager ();
		
		$page = $pageM->newPage ();
		$page->initFromName ('Calendar_Admin_CalendarManager');
		if ($this->_pluginAPI->canUserViewPage ($page->getID ())) {
			$em->triggerEvent ('viewAnyAdminPage', array ($page->getID (), 'en_UK'));
			$eventC = $this->_calendarM->newEvent ();
			$a = $eventC->initFromDatabaseID ($eventID);
			$eventA = $this->_calendarM->event2Array ($eventC);
			$sm->assign ('Calendar_Event', $eventA);
			$sm->assign ('Calendar_AvGroups', $this->getAvGroups ());
			$sm->display ('admin/editevent.tpl');
		}
	}
	
	function buildWeeksArray ($curMonth, $curYear, $firstDayOfWeek) {
		$calendarM = $this->_calendarM;	
	
		$firstDayOfMonth = mktime (0, 0, 0, $curMonth, 1, $curYear);		
	
		$dayOfWeek = (int) date ('w', $firstDayOfMonth);

		$lastDayOfWeek = $firstDayOfWeek + 6;
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
			foreach ($day['Events'] as $k=>$event) {
				$event['MonthMoreInfoLink'] = 'index.php?action=calendarMonthView&month='.$curMonth
					.'&year='.$curYear.'&eventID='.$event['ID'];
				$day['Events'][$k] = $event;
			}
			$w = date ('w', $cur);
			if ($w == 0 or $w == 6) {
				$day['weekend'] = true;
			} else {
				$day['weekend'] = false;
			}
			$dm = date ('n', $cur);
			if ($dm == $curMonth) {
				$day['othermonth'] = false;
			} else {
				$day['othermonth'] = true;
			}
			
			if (date ('d M Y', $cur) == date ('d M Y')) {
				$day['current'] = true;
			} else {
				$day['current'] = false;
			}
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
		return $weeks;
	}
	
	function getCurrentYear () {
		return 2006;
	}
	
	function getCurrentMonth () {
		return 10;
	}
	
	function getMonthName ($month) {
		$t = &$this->_pluginAPI->getI18nManager ();
		switch ($month) {
			case 1:
				return $t->translate ('January');
			case 2:
				return $t->translate ('February');
			case 3:
				return $t->translate ('March');
			case 4:
				return $t->translate ('April');
			case 5:
				return $t->translate ('May');
			case 6:
				return $t->translate ('June');
			case 7:
				return $t->translate ('July');
			case 8:
				return $t->translate ('August');
			case 9:
				return $t->translate ('September');
			case 10:
				return $t->translate ('October');
			case 11:
				return $t->translate ('November');
			case 12:
				return $t->translate ('December');
		}
	}
	
	function getAvGroups () {
		$groups = array ();
		foreach ($this->_calendarM->getAllGroups () as $group) {
			$groups[$group->getID ()] = $group->getName ();
		}
		return $groups;
	}
	
	/*function event2Array (&$event) {
		$event = array ('ID'=>$event->getID (), 'group'=>$this->group2Array ($event->getGroup ()), 
			'StartDate'=>$event->getStartDate (), 'EndDate'=>$event->getEndDate (), 
			'Title'=>$event->getTitle (), 'Description'=>$event->getDescription (), 
			'EditLink'=>'index.php?action=adminEditCalendarEventForm&eventID='.$event->getID ());
		return $event;
	}
	
	function group2Array (&$group)  {
		$group = array ('Color'=>$group->getColor (), 'Name'=>$group->getName (), 'ID'=>$group->getID ());
		return $group;
	}*/
	
	function getWeekDays ($firstDayOfWeek) {
		$days = array ();
		$lastDayOfWeek = $firstDayOfWeek + 6;
		for ($i = $firstDayOfWeek; $i<=$lastDayOfWeek; $i++) {
			$d = $i;
			if ($d >= 7) {
				$d -= 7;
			}
			$days[] = $this->getDayName ($d);
		}
		return $days;
	}
	
	function getDayName ($day) {
		$t = &$this->_pluginAPI->getI18nManager ();
		switch ($day) {
			case 1:
				return $t->translate ('Monday');
			case 2:
				return $t->translate ('Tuesday');
			case 3:
				return $t->translate ('Wednesday');
			case 4:
				return $t->translate ('Thursday');
			case 5:
				return $t->translate ('Friday');
			case 6:
				return $t->translate ('Saturday');
			case 0:
				return $t->translate ('Sunday');
		}
	}
}
?>
