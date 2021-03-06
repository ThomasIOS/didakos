<?php
// name of the language file that needs to be included 
$language_file='admin';

$cidReset=true;

include('../inc/global.inc.php');

// setting the section (for the tabs)
$this_section=SECTION_PLATFORM_ADMIN;

api_protect_admin_script();

$id_session=intval($_GET['id_session']);
$course_code=trim(stripslashes($_GET['course_code']));

$formSent=0;
$errorMsg='';

// Database Table Definitions
$tbl_user			= Database::get_main_table(TABLE_MAIN_USER);
$tbl_course			= Database::get_main_table(TABLE_MAIN_COURSE);
$tbl_session		= Database::get_main_table(TABLE_MAIN_SESSION);
$tbl_session_course	= Database::get_main_table(TABLE_MAIN_SESSION_COURSE);

$tool_name=get_lang('ModifySessionCourse');

$result=api_sql_query("SELECT name,title FROM $tbl_session_course,$tbl_session,$tbl_course WHERE id_session=id AND course_code=code AND id_session='$id_session' AND course_code='".addslashes($course_code)."'",__FILE__,__LINE__);

if(!list($session_name,$course_title)=mysql_fetch_row($result))
{
	header('Location: session_course_list.php?id_session='.$id_session);
	exit();
}

$interbreadcrumb[]=array('url' => 'index.php',"name" => get_lang('PlatformAdmin'));
$interbreadcrumb[]=array('url' => "session_list.php","name" => get_lang("SessionList"));
$interbreadcrumb[]=array('url' => "session_course_list.php?id_session=$id_session","name" => get_lang("CourseSessionList")." &quot;".htmlentities($session_name,ENT_QUOTES,$charset)."&quot;");

if($_POST['formSent'])
{
	$formSent=1;

	$id_coach=intval($_POST['id_coach']);


	api_sql_query("UPDATE $tbl_session_course
				   SET id_coach='$id_coach'
				   WHERE id_session='$id_session' AND course_code='$course_code'",__FILE__,__LINE__);
	header('Location: '.$_GET['page'].'?id_session='.$id_session);
	exit();

}
else
{
	$result=api_sql_query("SELECT id_coach FROM $tbl_session_course WHERE id_session='$id_session' AND course_code='$course_code'",__FILE__,__LINE__);

	if(!$infos=mysql_fetch_array($result))
	{
		//header('Location: '.$_GET['page'].'?id_session='.$id_session);
		exit();
	}
}

$sql="SELECT user_id,lastname,firstname,username FROM $tbl_user WHERE status='1' ORDER BY lastname,firstname,username";

$result=api_sql_query($sql,__FILE__,__LINE__);

$Coaches=api_store_result($result);

Display::display_header($tool_name);

api_display_tool_title($tool_name);
?>

<form method="post" action="<?php echo api_get_self(); ?>?id_session=<?php echo $id_session; ?>&course_code=<?php echo urlencode($course_code); ?>&page=<?php echo $_GET['page'] ?>" style="margin:0px;">
<input type="hidden" name="formSent" value="1">

<table border="0" cellpadding="5" cellspacing="0" width="550">

<?php
if(!empty($errorMsg))
{
?>

<tr>
  <td colspan="2">

<?php
	Display::display_normal_message($errorMsg);
?>

  </td>
</tr>

<?php
}
?>

<tr>
  <td width="30%"><?php echo get_lang("CoachName") ?>&nbsp;&nbsp;</td>
  <td width="70%"><select name="id_coach" style="width:250px;">
	<option value="0">----- <?php echo get_lang("Choose") ?> -----</option>
	<option value="0" <?php if((!$sent && $enreg['user_id'] == $infos['id_coach']) || ($sent && $enreg['user_id'] == $id_coach)) echo 'selected="selected"'; ?>><?php echo get_lang('None') ?></option>
<?php

foreach($Coaches as $enreg)
{
?>

	<option value="<?php echo $enreg['user_id']; ?>" <?php if((!$sent && $enreg['user_id'] == $infos['id_coach']) || ($sent && $enreg['user_id'] == $id_coach)) echo 'selected="selected"'; ?>><?php echo $enreg['lastname'].' '.$enreg['firstname'].' ('.$enreg['username'].')'; ?></option>

<?php
}

unset($Coaches);
?>

  </select></td>
</tr>
<tr>
  <td>&nbsp;</td>
  <td><input type="submit" value="<?php echo get_lang('Ok'); ?>"></td>
</tr>

</table>

</form>

<?php
Display::display_footer();
?>