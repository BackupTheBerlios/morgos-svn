<?php

/**
 * \page Skins Creating a skin
 *
 * \section Introduction
 * A skin does is made up from some templates.
 * This templates are progressed, and the actual content will be added.
 * The template parser is http://smarty.php.net/
 *
 * To enable your skin it should have a \subpage skin.php
 * 
 * \subsection Mandatory templates
 * - \subpage Genericpage
 * - \subpage Navigation
 * - \subpage Sidebar
 * - \subpage Footer
 * - \subpage Header
 * - \subpage Userbox
 * - \subpage Usermessages
 * - \subpage Sidebox
 * - \subpage Sideelement
 * - \subpage 404
 * - \subpage error
 *
 * \subsection Mandatory admin templates
 * - \subpage Login
 *
 * \subsection Variables that can be used on every location
 * - $SkinPath: the location of the skin. Usefull for including images/css files 
*/

/**
 * \page skin.php
 * This is a php file.
 *
 * \section Its look
 * <?php
 * 	$SkinID = "{ID}";
 *	$SkinName = "MySkinName";
 *	$SkinVersion = '1.0';
 *	$SkinMinMorgOSVersion = '0.3';
 *	$SkinMaxMorgOSVersion = '0.5';
 * ?>
 * 
 * This is a skin with name MySkinName, its on version 1.0. 
 * It supports morgos versions 0.3, 0.4 and 0.5
 * You Should test your skin with 0.3 and 0.5. 
 * If you can't test on MorgOS version 0.3 MinMorgOSVersion should be 0.5 
 *  (unless you test it with 0.4)
 *  
 * ID should be an unique GUID identifier: You could create one on
 * http://www.hoskinson.net/webservices/guidgeneratorclient.aspx
*/

/**
 * \page Genericpage
 * This template has the name genericpage.tpl
 * \paragraph
 * This is the template that shows a normal page, 
 *  it should include the \subpage Header, \subpage Footer, \subpage Navigation, 
 *  \subpage Sidebar, and shows the content of a page. 
 * (it is possible that header includes Navigation for example)
 *
 * \subsection Possible variables
 * - $MorgOS_CurrentPage_Title: The title of the page.
 * - $MorgOS_CurrentPage_Content: The content of the page.
*/

/**
 * \page Navigation
 * This template should have the name navigation.tpl.
 * \paragraph
 * This shows the root navigation. If desired it can also the subpages 
 * (or the current subpages)
 * \subsection Intresting variables
 * - $MorgOS_RootMenu: It is an array of menu items. A menu item is also an array.
 *   The elements of a menu item:
 *		- Title: The title of the menu
 *		- Link: The link to the page
 *		- Childs: An array of subpages. The items are also menu item. 
 *				(so they have same structure)
*/

/**
 * \page Sidebar
 * Name: sidebar.tpl
 * \paragraph
 * Here do plugins add their side content (login box, poll, latest messages, ...)
 * It is possible you add here another navigation menu.
*/

/**
 * \page Footer
 * Name: footer.tpl
 * \paragraph
 * The footer of the file.
 * \subsection Variables
 *  $MorgOS_Copyright: The copyrigt message for MorgOS.
*/

/**
 * \page Header
 * Name: header.tpl
 * \paragraph
 * The header of the file.
 * \subsection Variables
 * - $MorgOS_ExtraHead: Extra content that should be appended at the end of <head>
 * - $MorgOS_SiteTitle: The title of the site.
*/

/**
 * \page Userbox
*/

/**
 * \page Usermessages
 * Name: usermessages.tpl
 * \paragraph
 * This shows the notices intended for the user.
 * \subsection The different messages
 * - $MorgOS_Errors: an array of error texts (Failed to login, incorrect username/password)
 * - $MorgOS_Notices: notices (You are logged in)
 * - $MorgOS_Warnings: warnings (This site will be closed for maintenance on blablabla)
*/

/**
 * \page Sidebox
 * Name sidebox.tpl
 * \paragraph
 * This is an element in the sidebar. It does only have this vars
 * $BoxTitle: The title of the box (is always text/image)
 * $BoxContent: the content of the box (can be text/list/image/form/...)
*/

/**
 * \page Sideelement
 * Name: sideelement.tpl
 * \paragraph
 * This is an element in the sidebar. It does have only one var:
 * $ElementContent: The content of the element (can be everything)
*/

/**
 * \page Login
 * Name admin/login.tpl
 * \paragraph
 * This is the page that users see when they access the admin but they aren't logged in.
 * This page should offer a login form
 * 
 * \section Form specification
 * Action: index.php
 * Method: POST
 * Required fields:
 *	- action: type hidden, value=adminLogin
 *	- adminLogin: type text: the login for the admin
 *	- adminPassword: type password: the password
 * Additional fields:
 *	None
*/
?>