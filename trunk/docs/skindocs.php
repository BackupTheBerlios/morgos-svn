<?php

/**
 * \page Skins Creating a skin
 *
 * \section Introduction
 * A skin is made up from some templates.
 * This templates are processed, and the actual content will be added.
 * The template parser is http://smarty.php.net/
 *
 *
 * To enable your skin it should have a \subpage skin.php
 * 
 * \subsection a Mandatory templates
 * - \subpage Genericpage
 * - \subpage Navigation
 * - \subpage Sidebar
 * - \subpage Footer
 * - \subpage Header
 * - \subpage BoxLoginForm
 * - \subpage BoxUserForm
 * - \subpage UserRegisterForm
 * - \subpage UserSideboxContent
 * - \subpage Usermessages
 * - \subpage Sidebox
 * - \subpage Sideelement
 * - \subpage 404
 * - \subpage error
 *
 * \subsection b Mandatory admin templates
 * - \subpage AdminLogin
 * - \subpage AdminGenericpage
 * - \subpage AdminHeader
 * - \subpage AdminFooter
 * - \subpage AdminNavigation
 * - \subpage AdminUsermessages
 * - \subpage AdminSidebar
 * - \subpage AdminSidebox
 * - \subpage AdminSideelement
 *
 * \subsection c Actual admin content (also mandatory)
 * - \subpage AdminPage_PageManager and \subpage AdminPage_Editor
 * - \subpage AdminUser_UserManager
 * - \subpage AdminPlugin_PluginManager and \subpage AdminPlugin_PluginList
 *
 * \subsection d Variables that can be used on every location
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
 * You should test your skin with the lowest and highest version it is compatible with.
 * If you can't for some reason, you should change your compatible versions.
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
 * It should have the var $MorgOS_Sidebar_Content.
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
 * \page BoxLoginForm
 * Name: user/boxloginform.tpl
 * \paragraph
 * This is the content of a sidebox when the user isn't logged in.
 * It should contain a login form. It should also contain links for 
 * registring and "Forgot password"
 * - index.php?action=userRegisterForm: the register page
 * - index.php?action=userForgotPasswordForm: not yet implemented
 * \section Form specification
 * Action: index.php
 * Method: POST
 * Required fields:
 *	- action: type hidden, value=userLogin
 *	- userLogin: type text: the login for the user
 *	- userPassword: type password: the password
 * Additional fields:
 *	None
*/

/**
 * \page BoxUserForm
 * Name: user/boxuserform.tpl
 * \paragraph
 * This is the content of a sidebox when the user is logged in.
 * It should show some links:
 * - index.php?action=userLogout: Logout link
*/

/**
 * \page UserSideboxContent
 * Name: user/sideboxcontent.tpl
 * \paragraph
 * This should include the BoxUserForm or BoxLoginForm
 * You should do it with:
 * {morgos_side_box EvalBoxTitle="Title"
		BoxContentFile="user/boxuserform.tpl"}
 * Check if the user is logged in with $MorgOS_CurUser.
 * The name of the user is $MorgOS_CurUser.Name
*/

/**
 * \page UserRegisterForm
 * Name: user\register.tpl
 * \paragraph
 * This is the content of a register form page.
 * \section Form Definition
 * Action: index.php
 * Method: POST
 * Required fields:
 *	- action: type hidden, value=userRegister
 *	- login: type text: the login for the user
 *	- email: type text: the email
 *	- password1: type password: the password
 *	- password2: type password: repeat password (should be same as password1)
 * Additional fields:
 *	None
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
 * This is an element in the sidebar (eg. the user functions). It does only have this vars
 * $BoxTitle: The title of the box (is always text/image) and
 * $BoxContent: the content of the box (can be text/list/image/form/...)
*/

/**
 * \page Sideelement
 * Name: sideelement.tpl
 * \paragraph
 * This is an element in the sidebar. It is the same as \subpage Sidebox, 
 * but doesn't have a title.
 * \paragraph 
 * It does have only one var:
 * $ElementContent: The content of the element (can be everything)
*/

/**
 * \page AdminLogin Admin login
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

/**
 * \page AdminGenricpage Admin Genericpage
 * Name: admin/genricpage.tpl
 *
 * \paragraph
 * This is a genericpage for an admin page.
 * It should include the admin header, footer, navigation and the sidebar
 * Userfull vars
 * - $MorgOS_AdminPage_Title: the page of the title
 * - $MorgOS_AdminPage_Content: the content of the page
*/

/**
 * \page AdminHeader Admin header
 * The Header of a page
 * In the <html><head> part on the end you should have
 * $MorgOS_ExtraAdminHead:
 * Other usefull vars:
 * - $MorgOS_AdminTitle: The admin title
 * - $MorgOS_AdminPage_Title: The page of the title
*/

/**
 * \page AdminFooter Admin footer
 * The footer of an admin page
 * Variables:
 * - $MorgOS_Copyright: The copyrigt notice
*/

/**
 * \page AdminNavigation Admin navigation
 * The admin navigation
 *
 * Variables:
 *  - $MorgOS_Admin_RootMenu: same layout as $MorgOS_RootMenu
*/

/**
 * \page AdminUsermessages Admin usermessage
 * Sames as \subpage Usermessages, but for the admin
*/

/**
 * \page AdminSidebar Admin sidebar
 * Same as \subpage Sidebar but for the admin
*/

/**
 * \page AdminSidebox Admin sidebox
 * Same as \subpage Sidebox but for the admin
*/

/**
 * \page AdminSideelement Admin sideelement
 * Same as \subpage Sideelement but for the admin
*/

/**
 * \page AdminPage_PageManager Admin pagemanager
 * Name: admin/page/pagemanager.tpl
 *
 * \paragraph
 * This should show the pages that are part of the user selected menu.
 * It should also show some Forms (creating a new page, editing current page)
 * 
 * \pragraph Usefull vars
 * - $MorgOS_PageLevel: An array of level items.
 * 		a level item is an array with 2 values: Link, and Name
 *		The level items are the parent in the menus.
 * - $MorgOS_PagesList: an array of page items (items in the menu)
 *	They have these values:
 *	* Title: the title of the page
 *	* PlaceInMenu: The number of the place
 *	* ID: the id of the page
 *		This should be used in some links
 *		- index.php?action=adminMovePageDown&pageID=$ID (to move the page down in the menu)
 *		- index.php?action=adminMovePageUp&pageID=$ID the opposite
 *		- index.php?action=adminDeletePage&pageID=$ID to delete the page
 *		- index.php?action=adminMovePageLevelUp&pageID=$ID To move the page one level up 
 *			(check with CanMoveUp if it is not toplevel already)
 *	* Link: the view link (for normal page view)
 * - $MorgOS_ParentPage: an array of the current page
 *	* ID: the ID
 *	* RootPage: if it is a toplevel page (if it is, don't show editPage)
 *	* Content: current content
 *	* Title: current title
 *	* NavTitle: current menu title
 * \paragraph Editing page Form Specification:
 * Action: index.php
 * Method: POST
 * Required fields:
 *	- action: type hidden, value=adminSavePage
 *	- pageID: type hidden, value= the ID of the parentpage
 *	- pageTitle: type text: new/update page title
 *	- pageNavTitle: type text: new/updated menu title
 *	- pageContent: type textarea: new/updated content
 * Additional fields:
 *	None
 * \paragraph Add a new page Form Specification:
 * Action: index.php
 * Method: POST
 * Required fields:
 *	- action: type hidden, value=adminNewPage
 *	- parentPageID: type hidden, value= the ID of the parentpage
 *	- pageTitle: type text: page title
 * Additional fields:
 *	None
*/

/**
 * \page AdminPage_Editor Admin pageeditor
 * This should show a textbox.
*/

/**
 * \page AdminUser_UserManager Admin usermanager
*/

?>