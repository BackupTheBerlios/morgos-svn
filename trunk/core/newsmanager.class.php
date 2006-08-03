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
/** \file newsmanager.class.php
 * File that take care of the newsmanager
 *
 * @since 0.2
 * @author Nathan Samson
 * @license GPL
*/

include_once ('core/newscommentitem.class.php');
include_once ('core/newstopic.class.php');
include_once ('core/newsitem.class.php');

class newsmanager {

	function newsmanager () {
	}

	/*Public newsitem functions*/
	
	function getAllNewsItems () {
	}
	
	function postNewsItem () {
	}
	
	function addOptionsToNewsItem () {
	}
	
	function getAllOptionsFromNewsItem () {
	}
	
	function areNewsItemsGloballyDisabled () {
	}	
	
	function setNewsItemsGloballyDisabled () {
	}	
	
	/*Public topics functions*/
	
	function getAllTopics () {
	}
	
	function addTopicToDatabase () {
	}
	
	function removeTopicFromDatabase () {
	}
	
	function addOptionsToTopic () {
	}
	
	function getAllOptionsFromTopic () {
	}
	
	/*Public commentitem functions*/
	
	function addOptionToComments () {
	}
	
	function areCommentsGloballyDisabled () {
	}
	
	function setCommentGloballyDisabled () {
	}
	
	function getAllOptionsFromCommentItems () {
	}
	
}