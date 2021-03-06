<?php
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2006 Dokeos S.A.
	Copyright (c) 2006 Ghent University (UGent)

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact address: Dokeos, 44 rue des palais, B-1030 Brussels, Belgium
	Mail: info@dokeos.com
==============================================================================
*/

/**
*	These files are a complete rework of the forum. The database structure is
*	based on phpBB but all the code is rewritten. A lot of new functionalities
*	are added:
* 	- forum categories and forums can be sorted up or down, locked or made invisible
*	- consistent and integrated forum administration
* 	- forum options: 	are students allowed to edit their post?
* 						moderation of posts (approval)
* 						reply only forums (students cannot create new threads)
* 						multiple forums per group
*	- sticky messages
* 	- new view option: nested view
* 	- quoting a message
*
*	@Author Patrick Cool <patrick.cool@UGent.be>, Ghent University
*	@Copyright Ghent University
*	@Copyright Patrick Cool
*
* 	@package dokeos.forum
*/

/**
 **************************************************************************
 *						IMPORTANT NOTICE
 * Please do not change anything is this code yet because there are still
 * some significant code that need to happen and I do not have the time to
 * merge files and test it all over again. So for the moment, please do not
 * touch the code
 * 							-- Patrick Cool <patrick.cool@UGent.be>
 **************************************************************************
 */

/*
==============================================================================
		INIT SECTION
==============================================================================
*/
	include('../inc/global.inc.php');
	$this_section=SECTION_COURSES;
	/* ------------	ACCESS RIGHTS ------------ */
	// notice for unauthorized people.
	api_protect_course_script(true);
/*
-----------------------------------------------------------
	Language Initialisation
-----------------------------------------------------------
*/
// name of the language file that needs to be included
$language_file = 'forum';
require ('../inc/global.inc.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
include_once (api_get_path(LIBRARY_PATH).'groupmanager.lib.php');

$fck_attribute['Width'] = '100%';
$fck_attribute['Height'] = '400';
$fck_attribute['ToolbarSet'] = 'Middle';
$fck_attribute['Config']['IMUploadPath'] = 'upload/forum/';
$fck_attribute['Config']['FlashUploadPath'] = 'upload/forum/';
if(!api_is_allowed_to_edit()) $fck_attribute['Config']['UserStatus'] = 'student';


$nameTools=get_lang('Forum');
?>
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title></title>
<style type="text/css" media="screen, projection">
/*<![CDATA[*/
@import "<?php echo api_get_path(WEB_CODE_PATH); ?>css/<?php echo api_get_setting('stylesheets');?>/default.css";
/*]]>*/
</style>
</head>
<body>
<?php

/*
-----------------------------------------------------------
	Including necessary files
-----------------------------------------------------------
*/
include('forumconfig.inc.php');
include('forumfunction.inc.php');


/*
==============================================================================
		MAIN DISPLAY SECTION
==============================================================================
*/
/*
-----------------------------------------------------------
	Retrieving forum and forum categorie information
-----------------------------------------------------------
*/
// we are getting all the information about the current forum and forum category.
// note pcool: I tried to use only one sql statement (and function) for this
// but the problem is that the visibility of the forum AND forum cateogory are stored in the item_property table
$current_thread=get_thread_information($_GET['thread']); // note: this has to be validated that it is an existing thread
$current_forum=get_forum_information($current_thread['forum_id']); // note: this has to be validated that it is an existing forum.
$current_forum_category=get_forumcategory_information($current_forum['forum_category']);

/*
-----------------------------------------------------------
	Is the user allowed here?
-----------------------------------------------------------
*/
// if the user is not a course administrator and the forum is hidden
// then the user is not allowed here.
if (!api_is_allowed_to_edit() AND ($current_forum['visibility']==0 OR $current_thread['visibility']==0))
{
	forum_not_allowed_here();
}

/*
-----------------------------------------------------------
	Display Forum Category and the Forum information
-----------------------------------------------------------
*/
// we are getting all the information about the current forum and forum category.
// note pcool: I tried to use only one sql statement (and function) for this
// but the problem is that the visibility of the forum AND forum cateogory are stored in the item_property table
/*
echo "<table width='100%'>\n";

// the forum category
echo "\t<tr class=\"forum_category\">\n\t\t<td colspan=\"6\">";
echo '<a href="index.php" '.class_visible_invisible($current_forum_category['visibility']).'>'.$current_forum_category['cat_title'].'</a><br />';
echo '<span>'.$current_forum_category['cat_comment'].'</span>';
echo "</td>\n";
echo "\t</tr>\n";

// the forum
echo "\t<tr class=\"forum_header\">\n";
echo "\t\t<td colspan=\"6\"><a href=\"viewforum.php?forum=".$current_forum['forum_id']."\" ".class_visible_invisible($current_forum['visibility']).">".$current_forum['forum_title']."</a><br />";
echo '<span>'.$current_forum['forum_comment'].'</span>';
echo "</td>\n";
echo "\t</tr>\n";
echo "</table>";
*/

$sql="SELECT * FROM $table_posts posts, $table_users users
		WHERE posts.thread_id='".$current_thread['thread_id']."'
		AND posts.poster_id=users.user_id
		ORDER BY posts.post_id ASC";
$result=api_sql_query($sql, __FILE__, __LINE__);

echo "<table width=\"100%\" cellspacing=\"5\" border=\"0\">\n";
while ($row=mysql_fetch_array($result))
{
	echo "\t<tr>\n";
	echo "\t\t<td rowspan=\"2\" class=\"forum_message_left\">";
	if ($row['user_id']=='0')
	{
		$name=$row['poster_name'];
	}
	else
	{
		$name=$row['firstname'].' '.$row['lastname'];
	}
	echo display_user_link($row['user_id'], $name).'<br />';
	echo $row['post_date'].'<br /><br />';

	echo "</td>\n";
	echo "\t\t<td class=\"forum_message_post_title\">".$row['post_title']."</td>\n";
	echo "\t</tr>\n";

	echo "\t<tr>\n";
	echo "\t\t<td class=\"forum_message_post_text\">".$row['post_text']."</td>\n";
	echo "\t</tr>\n";
}
echo "</table>";

?>
</body>
</html>
