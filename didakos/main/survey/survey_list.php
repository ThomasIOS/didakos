<?php
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004-2008 Dokeos SPRL
	Copyright (c) 2006-2008	Patrick Cool

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact address: Dokeos, rue du Corbeau, 108, B-1030 Brussels, Belgium
	Mail: info@dokeos.com
==============================================================================
*/

/**
*	@package dokeos.survey
* 	@author unknown, the initial survey that did not make it in 1.8 because of bad code
* 	@author Patrick Cool <patrick.cool@UGent.be>, Ghent University: cleanup, refactoring and rewriting large parts of the code
* 	@version $Id: survey_list.php 15555 2008-06-11 20:39:15Z juliomontoya $
*
* 	@todo use quickforms for the forms
*/

// name of the language file that needs to be included
$language_file = 'survey';

// including the global dokeos file
require ('../inc/global.inc.php');

// including additional libraries
//require_once (api_get_path(LIBRARY_PATH)."/survey.lib.php");
require_once('survey.lib.php');
require_once (api_get_path(LIBRARY_PATH)."/course.lib.php");

/** @todo this has to be moved to a more appropriate place (after the display_header of the code)*/
if (!api_is_allowed_to_edit())
{
	Display :: display_header(get_lang('SurveyList'));
	SurveyUtil::survey_list_user($_user['user_id']);
	Display :: display_footer();
	exit;
}

// Database table definitions
$table_survey 			= Database :: get_course_table(TABLE_SURVEY);
$table_survey_question 	= Database :: get_course_table(TABLE_SURVEY_QUESTION);
$table_course 			= Database :: get_main_table(TABLE_MAIN_COURSE);
$table_user 			= Database :: get_main_table(TABLE_MAIN_USER);

// language variables
if (isset ($_GET['search']) && $_GET['search'] == 'advanced')
{
	$interbreadcrumb[] = array ('url' => 'survey_list.php', 'name' => get_lang('SurveyList'));
	$tool_name = get_lang('SearchASurvey');
}
else
{
	$tool_name = get_lang('SurveyList'); 
}

// Header
Display :: display_header($tool_name,'Survey');
//api_display_tool_title($tool_name);

// Action handling: searching
if (isset ($_GET['search']) AND $_GET['search'] == 'advanced')
{
	SurveyUtil::display_survey_search_form();
}
// Action handling: deleting a survey
if (isset($_GET['action']) AND $_GET['action'] == 'delete' AND isset($_GET['survey_id']) AND is_numeric($_GET['survey_id']))
{
	// getting the information of the survey (used for when the survey is shared)
	$survey_data = survey_manager::get_survey($_GET['survey_id']);
	// if the survey is shared => also delete the shared content
	if (is_numeric($survey_data['survey_share']))
	{
		survey_manager::delete_survey($survey_data['survey_share'], true);
	}
	$return = survey_manager :: delete_survey($_GET['survey_id']);
	if ($return)
	{
		Display :: display_confirmation_message(get_lang('SurveyDeleted'), false);
	}
	else
	{
		Display :: display_error_message(get_lang('ErrorOccurred'), false);
	}
}

if(isset($_GET['action']) && $_GET['action'] == 'empty')
{
	$return = survey_manager::empty_survey(intval($_GET['survey_id']));
	if ($return)
	{
		Display :: display_confirmation_message(get_lang('SurveyEmptied'), false);
	}
	else
	{
		Display :: display_error_message(get_lang('ErrorOccurred'), false);
	}
}

// Action handling: performing the same action on multiple surveys
if ($_POST['action'])
{
	if (is_array($_POST['id']))
	{
		foreach ($_POST['id'] as $key=>$value)
		{
			// getting the information of the survey (used for when the survey is shared)
			$survey_data = survey_manager::get_survey($value);
			// if the survey is shared => also delete the shared content
			if (is_numeric($survey_data['survey_share']))
			{
				survey_manager::delete_survey($survey_data['survey_share'], true);
			}
			// delete the actual survey
			survey_manager::delete_survey($value);
		}
		Display :: display_confirmation_message(get_lang('SurveysDeleted'), false);
	}
	else
	{
		Display :: display_error_message(get_lang('NoSurveysSelected'), false);
	}
}


// Action links
echo '<a href="create_new_survey.php?'.api_get_cidreq().'&amp;action=add">'.get_lang('CreateNewSurvey').'</a> | ';
//echo '<a href="survey_all_courses.php">'.get_lang('CreateExistingSurvey').'</a> | ';
echo '<a href="'.api_get_self().'?'.api_get_cidreq().'&amp;search=advanced">'.get_lang('Search').'</a>';

// Main content
SurveyUtil::display_survey_list();

// Footer
Display :: display_footer();

/* Bypass functions to make direct use from SortableTable possible */
function get_number_of_surveys()
{
	return SurveyUtil::get_number_of_surveys();
}
function get_survey_data($from, $number_of_items, $column, $direction)
{
	return SurveyUtil::get_survey_data($from, $number_of_items, $column, $direction);
}
function modify_filter($survey_id)
{
	return SurveyUtil::modify_filter($survey_id);
}
function anonymous_filter($anonymous)
{
	return SurveyUtil::anonymous_filter($anonymous);
}