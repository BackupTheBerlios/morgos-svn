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
/** \file usertranslatedgroups.class.php
 * File that take care of one translated group
 *
 * @since 0.2
 * @author Nathan Samson
*/

class translatedGroup extends databaseObject {

	function translatedGroup ($db, $allExtraOptions, &$creator) {
		parent::databaseObject ($db, $allExtraOptions, array ('name', 'description', 'groupID'), 'translatedGroups', 'translatedGroupID', &$creator);
	}

}

?>