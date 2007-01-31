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
 * This is the calendar class.
 *
 * @since 0.2
 * @author Nathan Samson
*/
define ('SQL_DATETIME', 'Y-m-d H:m:s');
define ('MINUTES_IN_HOUR', 60);
define ('HOURS_IN_DAY', 24);
define ('SECONDS_IN_MINUTE', 60);
define ('SECONDS_IN_HOUR', MINUTES_IN_HOUR*SECONDS_IN_MINUTE);
define ('SECONDS_IN_DAY', HOURS_IN_DAY*SECONDS_IN_HOUR);

class calendar {
	var $_db;

	function calendar (&$db) {
		$this->_db = &$db;
	}
	
	function getDayArray ($timestamp) {
		$array = array ();
		$array['Nr'] = (int) date ('d', $timestamp);
		$array['Events'] = $this->getAllEventsOnArray ($timestamp);
		return $array;
	}
	
	function getAllEventsOn ($timestamp) {
		$startTime = date (SQL_DATETIME, $timestamp);
		$endTime = date (SQL_DATETIME, $this->getNextDay ($timestamp)-1);
		$fTN = $this->_db->getPrefix ().'calendar';
		$sql = "SELECT eventID FROM $fTN WHERE ";
		$sql .= "(start <= '$startTime' AND end >= '$startTime') OR "; // start before, but not ended yet
		$sql .= "(start <= '$endTime'   AND end >= '$endTime') OR "; // start after, but not ended yet
		$sql .= "(start >= '$startTime' AND end <= '$endTime')"; // start after, and end before
		$q = $this->_db->query ($sql);
		if (isError ($q)) {
			return $q;
		}
		$events = array ();
		while ($row = $this->_db->fetchArray ($q)) {
			$event = $this->newEvent ();
			$event->initFromDatabaseID ($row['eventID']);
			$events[] = $event;
		}
		return $events;
	}
	
	function getAllEventsOnArray ($timestamp) {
		$results = array ();
		foreach ($this->getAllEventsOn ($timestamp) as $event) {
			$results[] = $this->event2Array ($event);
		}
		return $results;
	}
	
	function getUpcomingEvents ($count, $offset = 0) {
		$currentTime = date (SQL_DATETIME);
		$fTN = $this->_db->getPrefix ().'calendar';
		//die ($currentTime);
		$sql = "SELECT eventID FROM $fTN WHERE start>='$currentTime'";
		$q = $this->_db->query ($sql);
		if (isError ($q)) {
			return $q;
		}
		$events = array ();
		while ($row = $this->_db->fetchArray ($q)) {
			$event = $this->newEvent ();
			$event->initFromDatabaseID ($row['eventID']);
			$events[] = $event;
		}
		return $events;
	}
	
	function getUpcomingEventsArray ($count, $offset = 0) {
		$results = array ();
		foreach ($this->getUpcomingEvents ($count, $offset) as $event) {
			$results[] = $this->event2Array ($event);
		}
		return $results;
	}
	
	function getCurrentEvents ($count, $offset = 0) {
		$currentTime = date (SQL_DATETIME);
		$fTN = $this->_db->getPrefix ().'calendar';
		$sql = "SELECT eventID FROM $fTN WHERE end>='$currentTime' AND start<='$currentTime'";
		$q = $this->_db->query ($sql);
		if (isError ($q)) {
			return $q;
		}
		$events = array ();
		while ($row = $this->_db->fetchArray ($q)) {
			$event = $this->newEvent ();
			$event->initFromDatabaseID ($row['eventID']);
			$events[] = $event;
		}
		return $events;
	}
	
	function getCurrentEventsArray ($count, $offset = 0) {
		$results = array ();
		foreach ($this->getCurrentEvents ($count, $offset) as $event) {
			$results[] = $this->event2Array ($event);
		}
		return $results;
	}
	
	function getAllEvents () {
		$fTN = $this->_db->getPrefix ().'calendar';
		$sql = "SELECT eventID FROM $fTN";
		$q = $this->_db->query ($sql);
		if (isError ($q)) {
			return $q;
		}
		$events = array ();
		while ($row = $this->_db->fetchArray ($q)) {
			$event = $this->newEvent ();
			$event->initFromDatabaseID ($row['eventID']);
			$events[] = $event;
		}
		return $events;
	}
	
	function event2Array (&$event) {
		 $event = array ('ID'=>$event->getID (), 'Group'=>$this->group2Array ($event->getGroup ()), 
			'StartDate'=>$event->getStartDate (), 'EndDate'=>$event->getEndDate (), 
			'Title'=>$event->getTitle (), 'Description'=>$event->getDescription (), 
			'EditLink'=>'index.php?action=adminEditCalendarEventForm&eventID='.$event->getID (),
			'DeleteLink'=>'index.php?action=adminDeleteCalendarEvent&eventID='.$event->getID ());
		return $event;
	}
	
	function getNextDay ($timestamp) {
		return strtotime ('+1 day', $timestamp);
	}
	
	function newEvent () {
		return new calendarEvent ($this->_db, array (), $this);
	}
	
	function getAllGroups () {
		$groups = array ();
		$fTN = $this->_db->getPrefix ().'calendarGroup';
		$sql = "SELECT groupID FROM $fTN";
		$q = $this->_db->query ($sql);
		while ($row = $this->_db->fetchArray ($q)) {
			$group = $this->newGroup ();
			$group->initFromDatabaseID ($row['groupID']);
			$groups[] = $group;
		}
		return $groups;
	}
	
	function addGroupToDatabase ($group) {
		return $group->addToDatabase ();
	}
	
	function removeGroupFromDatabase ($group) {
		$fTN = $this->db->getPrefix () . 'calendarGroupEvents';
		$groupID = $group->getID ();
		$sql = "DROP FROM $fTN WHERE groupID='$groupID'";
		$this->db->query ($sql);
		return $group->removeFromDatabase ();
	}
	
	function newGroup () {
		return new calendarGroup ($this->_db, array (), $this);
	}
	
	function group2Array (&$group) {
		$group = array ('Color'=>$group->getColor (), 'Name'=>$group->getName (), 'ID'=>$group->getID (), 
			'DeleteLink'=>'index.php?action=adminDeleteCalendarGroup&groupID='.$group->getID (),
			'EditLink'=>'index.php?action=adminEditCalendarGroupForm&groupID='.$group->getID ());
		return $group;
	}	
}