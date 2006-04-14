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
/** \file news.class.php
 * File that take care of the news system
 *
 * $Id: user.class.php 90 2006-04-12 13:59:54Z nathansamson $
 * \author Nathan Samson
*/
define ('TBL_NEWS', TBL_PREFIX . 'news');
define ('TBL_COMMENTS', TBL_PREFIX . 'comments');
define ('TBL_TOPICS', TBL_PREFIX . 'topics');
/** \class news
 * class that take care of the news system

 * \author Nathan Samson
*/
class news {
	function news (&$genDB, &$i10nMan) {
		$this->genDB = &$genDB;
		$this->i10nMan = &$i10nMan;
		$this->defLanguage = $this->i10nMan->getDefaultLanguage ();
	}
	
	/** \fn addNewsItem ($subject, $message, $topic, $userID = NULL)
	 * Adds a new newsitem in the database
	 * \public
	 * \param $subject (string) The title of the news item
	 * \param $message (string) The text in the news item
	 * \param $topic (string) The topic of the news item
	 * \param $userID (mixed) The userID, if an anonymous user leave NULL
	 * \param $language (string) The language, if NULL the default is used
	 * \return (bool)
	*/
	function addNewsItem ($subject, $message, $topic, $userID = NULL, $language = NULL) {
		$subject = addslashes ($subject);
		$message = addslashes ($message);
		$topic = addslashes ($topic);
		$userID = addslashes ($userID);
		$language = addslashes ($language);
		if ($language == NULL) {
			$language = $this->defLanguage;
		}
		$time = date ('Y-m-d H:i:s'); // now, formatted as YYYY-MM-DD HH:MM:S		
		
		$SQL = 'SELECT id FROM ' . TBL_NEWS . " WHERE subject='$subject' AND language='$language'";
		$query = $this->genDB->query ($SQL);
		if ($query !== false) {
			if ($this->genDB->num_rows ($query) == 0) {
				$SQL = 'SELECT name FROM ' . TBL_TOPICS;
				$SQL .= " WHERE name='$topic' AND language='$language'";
				$topicquery = $this->genDB->query ($SQL);
				if ($topicquery !== false) {
					if ($this->genDB->num_rows ($topicquery) !== 0) {
						$SQL = 'INSERT into ' . TBL_NEWS;
						$SQL .= ' (subject, message, topic, author, date, language)';
						$SQL .= " VALUES ('$subject', '$message', '$topic', '$userID', '$time', '$language') ";
						$query = $this->genDB->query ($SQL);
						if ($query !== false) {
							return true;
						} else {
							return false;
						}
					} else {
						trigger_error ('ERROR: Topic doesn\'t exist, please select another.');
						return false;
					}
				} else {
					return false;
				}
			} else {
				trigger_error ('ERROR: A newsitem with the same subject already exists.');
				return false;
			}
		} else {
			return false;
		}
	}
	
	/** \fn removeNewsItem ($subject, $language)
	 * Deletes the news item
	 * \public
	 * \param $subject (string) The title of the news item
	 * \param $language (string) The language of the news item
	 * \return (bool)
	*/
	function removeNewsItem ($subject, $language) {
	}
	
	/** \fn updateNewsItem ($title, $language, $newTitle = NULL, $newLanguage = NULL, $newMessage = NULL, $newTopic = NULL)
	 * Updates the newsitem in the database
	 * \todo change so that the userID can be changed too. If any of the $new* params 
	 * are NULL they ends the same
	 * \public
	 * \param $subject (string)
	 * \param $language (string)
	 * \param $newTitle (string)
	 * \param $newLanguage (string)
	 * \param $newMessage (string)
	 * \param $newTopic (string)
	 * \return (bool)
	*/
	function updateNewsItem ($title, $language, $newTitle = NULL, $newLanguage = NULL, $newMessage = NULL, $newTopic = NULL) {
	}
	
	/** \fn addComment ($subject, $message, $language, $onNews, $parentID, $userID = NULL)
	 * adds a comment to a newsmessage or to another comment
	 * \public
	 * \param $subject (string)
	 * \param $message (string)
	 * \param $language (string)
	 * \param $onNews (bool) true if the comment is on a news message or false if on another comment
	 * \param $parentID (int) the ID of the parent, either a news message or a comment
	 * \param $userID (mixed)
	 * \return (bool)
	*/
	function addComment ($subject, $message, $language, $onNews, $parentID, $userID = NULL) {
	}
	
	/** \fn getAllNewsItems ($language = NULL, $orderBy = 'date', $asc = true, )
	 * returns an array with information of all the newsitems.
	 * the format of the array is like this:
	 * array[n]['IDNews'] (int)
	 * array[n]['subject'] (string)
	 * array[n]['message'] (string)
	 * array[n]['language'] (string)
	 * array[n]['author'] (mixed, NULL for anonymous)
	 * array[n]['topic'] (string)
	 * array[n]['numberOfComments'] (int)
	 * \public
	 * \param $language (string) the language, leave NULL for all items
	 * \param $orderBy (string) date, subject or topic
	 * \param $asc (bool) true if ascending, false if descending (default)
	 * \return (array array or false)
	*/
	function getAllNewsItems ($language = NULL, $orderBy = 'date', $asc = false) {
		if ($language == NULL) {
			$language = $this->defLanguage;
		}
		$language = addslashes ($language);

		if ($asc) {
			$asc = 'ASC';
		} else {
			$asc = 'DESC';
		}
		
		switch ($orderBy) {
			case 'date':
			case 'subject':
			case 'topic':
				break;
			default:
				$orderBy = 'date';
		}
		
		$newsItems = array ();
		$SQL = 'SELECT * FROM ' . TBL_NEWS . ' WHERE language=\'' . $language . '\' ORDER BY ' . $orderBy . " $asc";
		$query = $this->genDB->query ($SQL);
		if ($query !== false) {
			while ($i = $this->genDB->fetch_array ($query)) {
				$newsItems[] = $i;
			}
		} else {
			return false;
		}
		return $newsItems;
	}
	
	/** \fn getAllCommentsOnNewsThreaded ()
	 * \\to decide
	*/
	function getAllCommentsOnNewsThreaded () {
	}
	
	/** \fn getAllCommentsOnNewsFlat ()
	 * \\to decide
	*/
	function getAllCommentsOnNewsFlat () {
	}
	
	/** \fn getAllTopics ($language = NULL)
	 * returns all the topics
	 * \public
	 * \param $language (string) the language, leave NULL for all topics
	 * \return (array array or false)
	*/
	function getAllTopics ($language = NULL) {
		if ($language == NULL) {
			$language = $this->defLanguage;
		}
		$language = addslashes ($language);
		$SQL = 'SELECT * FROM ' . TBL_TOPICS;
		$SQL .= " WHERE language='$language'";
		$query = $this->genDB->query ($SQL);
		if ($query !== false) {
			$topics = array ();
			while ($t = $this->genDB->fetch_array ($query)) {
				$topics[] = $t;
			}
			return $topics;
		} else {
			return false;
		}
	}
	
	/** \fn addTopic ($name, $language, $description, $image)
	 * add a topic in the database
	 * \public
	 * \param $name (string)
	 * \param $language (string)
	 * \param $description (string)
	 * \param $image (string)
	 * \return (bool)
	*/
	function addTopic ($name, $language, $description, $image) {
		$name = addslashes ($name);
		$language = addslashes ($language);
		$description = addslashes ($description);
		$image = addslashes ($image);
		
		$SQL = 'SELECT name FROM ' . TBL_TOPICS . ' WHERE name=\'' . $name . '\' AND ' .
			  ' language=\'' . $language . '\'';
		$query = $this->genDB->query ($SQL);
		if ($query !== false) {
			if ($this->genDB->num_rows ($query) == 0) {
				$SQL = 'INSERT into ' . TBL_TOPICS . ' (name, language, description, image) ';
				$SQL .= " VALUES ('$name', '$language', '$description', '$image')";
				$query = $this->genDB->query ($SQL);
				if ($query !== false) {
					return true;
				} else {
					return false;
				}
			} else {
				trigger_error ('ERROR: Topic already exists.');
				return false;
			}
		} else {
			return false;
		}
	}
	
	/** \fn removeTopic ($name, $language)
	 * removes a topic from the database (and all its childs)
	 * \warning this removes all the childs of this topic
	 * \public
	 * \param $name (string)
	 * \param $language (string)
	 * \return (bool)
	*/
	function removeTopic ($name, $language) {
	}
	
	/** \fn updateTopic ($name, $language, $newName, $newLanguae, $newDescription, $newImage)
	 * updates the topic in the database
	 * \public
	 * \param $name (string)
	 * \param $language (string)
	 * \param $newName (string)
	 * \param $newLanguage (string)
	 * \param $newDescription (string)
	 * \param $newImage (string)
	 * \return (bool)
	*/
	function updateTopic ($name, $language, $newName, $newLanguae, $newDescription, $newImage) {
	}
	
	/** \fn getTopic ($name, $language)
	 * Returns a topic
	 * \public
	 * \return (array or false)
	*/
	function getTopic ($name, $language) {
		$SQL = 'SELECT * FROM ' . TBL_TOPICS;
		$SQL .= " WHERE name='$name' AND language='$language'";
		$query = $this->genDB->query ($SQL);		
		if ($query !== false) {
			return $this->genDB->fetch_array ();
		} else {
			return false;
		}
	}
}
?>
