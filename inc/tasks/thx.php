<?php
/**
  * Version: 		    Thank you 2.6.x
  * Compatibillity: 	MyBB 1.8.x
  * Website: 		    http://www.mybb.com
  * Autor: 		        Dark Neo
*/

// Deny direct access to this file...
if(!defined("IN_MYBB"))
{
	die("This file can't be loaded directly please make sure IN_MYBB is defined.");
}

if(defined("IN_ADMINCP"))
{
	// Do you want to change thanks ammount per day and rep points to be earned when thanks, load this then !!!
	$plugins->add_hook("admin_user_groups_edit_graph_tabs", "thx_edit_group_tabs");
	$plugins->add_hook("admin_user_groups_edit_graph", "thx_edit_group");
	// Send all new data to thanks count and rep points if enabled...
	$plugins->add_hook("admin_user_groups_edit_commit", "thx_edit_group_do");	
	// Hooks for promotions on admincp
	$plugins->add_hook('admin_formcontainer_output_row', 'thx_promotion_formcontainer_output_row');
	$plugins->add_hook('admin_user_group_promotions_edit_commit', 'thx_promotion_commit');
	$plugins->add_hook('admin_user_group_promotions_add_commit', 'thx_promotion_commit');
	// Load admincp controls like recount thanks and enable groups to recount thanks.
	$plugins->add_hook('admin_tools_action_handler', 'thx_admin_action');
	$plugins->add_hook('admin_tools_menu', 'thx_admin_menu');
	$plugins->add_hook('admin_tools_permissions', 'thx_admin_permissions');
	$plugins->add_hook('admin_load', 'thx_admin');
	// Load Myalerts integration link on admincp...
	$plugins->add_hook('admin_load', 'thx_admin_load');	
}
else
{	
	// Load postbit data from plugin
	$plugins->add_hook("postbit", "thx");
	$plugins->add_hook("showthread_linear", "thx_thread");
	// Load message changes hide tags and more to postbit, announcements, preview, only missing on pm because don't needed
	$plugins->add_hook("postbit_announcement", "thx_code");
	$plugins->add_hook("postbit_prev", "thx_code");
	$plugins->add_hook("parse_message", "thx_code");
	$plugins->add_hook("postbit", "thx_clear",20);
	$plugins->add_hook("postbit_prev", "thx_clear",20);
	$plugins->add_hook("postbit_pm", "thx_clear",20);
	$plugins->add_hook("postbit_announcement", "thx_clear",20);
	// Load memprofile additional info
	$plugins->add_hook('member_profile_end', 'thx_memprofile');
	// When quote hide tags
	$plugins->add_hook("parse_quoted_message", "thx_quote");
	// Do you use AJAX ? then run it on click thanks or remove thanks buttons
	$plugins->add_hook("xmlhttp", "do_action");
	// Don't use AJAX ? then run this on click thanks button or remove thanks button reloading page but send data to db
	$plugins->add_hook("showthread_start", "direct_action");
	// Do you delete some post, load new values to thanks counts.
	$plugins->add_hook("class_moderation_delete_post", "deletepost_edit");
	// Did you need aditional info to be load like templates or additional function or any globalize it
	$plugins->add_hook("global_start", "thx_global_start");
	$plugins->add_hook("global_intermediate", "thx_global_intermediate");
	// Load who is viewing this script on thx
	$plugins->add_hook("fetch_wol_activity_end", "thx_wol_activity");
	$plugins->add_hook("build_friendly_wol_location_end", "thx_friendly_wol_activity");
	// Adding new tool to send mail on new reply
	$plugins->add_hook("newthread_end", "thx_send_mail_button_thread",10);
	//$plugins->add_hook("newreply_end", "thx_send_mail_button_post",10);
	$plugins->add_hook("newthread_do_newthread_end", "thx_send_mail_button_thread_update",10);
	if(defined("THIS_SCRIPT") && THIS_SCRIPT == "newpoints.php"){
		$plugins->add_hook("newpoints_home_end", "thx_newpoints_home",10);
	}	
}
$plugins->add_hook('task_promotions', 'thx_promotion_task');
// Load all info about this plugin
function thx_info()
{
	global $mybb, $db, $lang;

	// Load a function to get all info about plugin installation and new improvements (Like MyAlerts)
	$thx_config_link = thx_getdata($thx_config_link);
	
	// Send array of info for this function as usually
	return array(
		'name'			=>	$db->escape_string($lang->thx_title),
		'description'	=>	$db->escape_string($lang->thx_desc) . $thx_config_link,
		'website'		=>	'https://www.mybb.com',
		'author'		=>	'Whiteneo',
		'authorsite'	=>	'https://soportemybb.es',
		'version'		=>	'2.6.3',
		'codename'		=>	'thankyou_mybb_system',
        'compatibility' =>	'18*'
	);
}
// Run on plugin installation
function thx_install()
{
	global $db;
	ini_set('max_execution_time', 300);	

	if($db->table_exists("thx_backup"))
	{
		$db->query("RENAME TABLE ".TABLE_PREFIX."thx_backup TO ".TABLE_PREFIX."thx");
	}
	else
	{
		$db->query("CREATE TABLE IF NOT EXISTS ".TABLE_PREFIX."thx (
			txid INT UNSIGNED NOT NULL AUTO_INCREMENT, 
			uid int(10) UNSIGNED NOT NULL DEFAULT '0', 
			adduid int(10) UNSIGNED NOT NULL DEFAULT '0', 
			pid int(10) UNSIGNED NOT NULL DEFAULT '0', 
			time int(10) NOT NULL DEFAULT '0', 
			PRIMARY KEY (`txid`), 
			INDEX (`adduid`, `pid`, `time`) 
			);"
		);
	}

	// Add new field to table users for thanks plugin purposes
	if(!$db->field_exists("thx", "users"))
	{
		$sq[] = "ALTER TABLE ".TABLE_PREFIX."users ADD `thx` INT(10) NOT NULL DEFAULT '0', ADD `thxcount` INT(10) NOT NULL DEFAULT '0', ADD `thxpost` INT(10) NOT NULL DEFAULT '0', ADD `thx_ammount` INT(10) NOT NULL DEFAULT '0', ADD `thx_antiflood` INT(10) NOT NULL DEFAULT '0'";
	}
	elseif (!$db->field_exists("thxpost", "users"))		
	{
		$sq[] = "ALTER TABLE ".TABLE_PREFIX."users ADD `thxpost` INT(10) NOT NULL DEFAULT '0'";
	}
	elseif (!$db->field_exists("thx_ammount", "users"))		
	{
		$sq[] = "ALTER TABLE ".TABLE_PREFIX."users ADD `thx_ammount` INT(10) NOT NULL DEFAULT '0'";
	}
	elseif (!$db->field_exists("thx_antiflood", "users"))		
	{
		$sq[] = "ALTER TABLE ".TABLE_PREFIX."users ADD `thx_antiflood` INT(10) NOT NULL DEFAULT '0'";
	}
	if(!$db->field_exists("thx", "promotions"))
	{
		$db->add_column("promotions", "thx", "int NOT NULL default '0'");
	}
	if(!$db->field_exists("thxtype", "promotions"))
	{
		$db->add_column("promotions", "thxtype", "char(2) NOT NULL default ''");
	}
	
	// Set new values to enable max thanks ammount
	if(!$db->field_exists("thx_max_ammount", "usergroups"))
	{
		$sq[] = "ALTER TABLE ".TABLE_PREFIX."usergroups ADD thx_max_ammount INT(10) NOT NULL DEFAULT '10', ADD thx_rep_points INT(10) NOT NULL DEFAULT '1'";
	}

	// Set new values for newpoints improvements
	if(!$db->field_exists("thx_newpoints_earn", "usergroups"))
	{
		$sq[] = "ALTER TABLE ".TABLE_PREFIX."usergroups ADD thx_newpoints_earn INT(10) NOT NULL DEFAULT '25', ADD thx_newpoints_give INT(10) NOT NULL DEFAULT '10'";
	}

	// Set new values to posts table to load thanks per post
	if(!$db->field_exists("pthx", "posts"))
	{
		$sq[] = "ALTER TABLE ".TABLE_PREFIX."posts ADD `pthx` INT(10) NOT NULL DEFAULT '0'";
	}

	// Set new values to posts table to send mail if available when thanks
	if(!$db->field_exists("thx_send_mail", "posts"))
	{
		$sq[] = "ALTER TABLE ".TABLE_PREFIX."posts ADD `thx_send_mail` INT(5) NOT NULL DEFAULT '0'";
	}
	
	// Insert all data to db if available
	if(is_array($sq))
	{
		foreach($sq as $q)
		{
			$db->query($q);
		}
	}
}

// Verify if plugin is installed
function thx_is_installed()
{
	global $db;
	// If table fr thanks exist then return value, else say don't installed this plugin...
	if($db->table_exists('thx')){
		return true;
	}
	else{
		return false;
	}
}
// Run activate function and verify if exist or wont to load or not data, to prevent lose data or damage of changed data...
function thx_activate()
{
	global $db, $lang, $cache, $plugins;
	
	// Lang exist, then load it !!!
	$lang->load("thx", false, true);

	// Get the information available for this plugin to use it  
    $plugin_info = thx_info();

	// Load cache data and compare if version is the same or don't
    $dnt_plugins = $cache->read('dnt_plugins');

	if($plugin_info['version'] > $dnt_plugins['thx']['version']){
		thx_load_all();
	}
	else{
		thx_update();
	}
	
}
// Run on plugin activation if necesary...
function thx_update()
{
	global $cache;
	
	// Get the information available for this plugin to use it  
    $plugin_info = thx_info();

	// Load cache data and compare if version is the same or don't
	$dnt_plugins = $cache->read('dnt_plugins');
	$dnt_plugins['thx'] = array(
		'title' => 'Thanks',
		'version' => $plugin_info['version'],
	);
		
	// Update version on cache if exist new value, replacing with the old one
	$cache->update('dnt_plugins', $dnt_plugins);
}	
// Load all data on activate function, insert new templates, stylesheets, etc to db...
function thx_load_all()
{
	global $mybb, $db, $lang, $cache, $plugins;
	
	// Lang exist, then load it !!!
	$lang->load("thx", false, true);
	
	thx_update();
	
	//Create stylesheet for this plugin...
	$query_tid = $db->simple_select("themes", "tid", "def=1");
	$themetid = $db->fetch_field($query_tid,'tid');
	$style = array(
			'name'        => 'thx_buttons.css',
			'tid'           => (int)$themetid,
			'attachedto'   => 'showthread.php|member.php|thx.php',						
			'stylesheet'   => '.postbit_buttons a span.thx_buttons, .postbit_buttons a span.gracias,.postbit_buttons a span.egracias{font-weight: bold;background-position: 0 -20px}
.thx_buttons{position: relative;display: inline;padding: 0px 2px;border-radius: 2px;background: none !important}
.thx_buttons .gracias{color: #4b9134 !important;text-decoration: none}
.thx_buttons .egracias{color: #913434 !important;text-decoration: none}
.bad_thx{color: #FFF;font-size: 12px;font-weight: bold;text-decoration: none;background: none repeat scroll 0% 0% #913434 !important;box-shadow: 0px 0px 1em #B6B6B6;border-radius: 50%;padding: 5px 10px;text-align: center;float: right;margin-top: -10px;margin-right: -10px}
.neutral_thx{color: #FFF;font-size: 12px;font-weight: bold;text-decoration: none;background: none repeat scroll 0% 0% #2C2727 !important;box-shadow: 0px 0px 1em rgb(182, 182, 182);border-radius: 50%;padding: 5px 10px;text-align: center;float: right;margin-top: -10px;margin-right: -10px}
.good_thx{color: #FFF;font-size: 12px;font-weight: bold;text-decoration: none;background: #4B9134 none repeat scroll 0% 0% !important;box-shadow: 0px 0px 1em #BFDDBE;border-radius: 50%;padding: 5px 10px;text-align: center;float: right;margin-top: -10px;margin-right: -10px}
.good_thx a, bad_thx a{background: none !important;color: #fff}
.thx_avatar{background: transparent;border: 1px solid #F0F0F0;padding: 5px;border-radius: 5px;width: 30px;height: 30px;display: block}
.info_thx, .exito_thx, .alerta_thx, .error_thx {font-size:13px;border: 1px solid;margin: 10px 0px;padding:10px 8px 10px 50px;background-repeat: no-repeat;background-position: 10px center;text-align: center;font-weight: bold;border-radius: 5px}
.info_thx {color: #00529B;background-color: #BDE5F8;background-image: url(images/thx/info.png)}
.exito_thx {background-color: #DFF2BF;background-image:url(images/thx/bien.png)}
.alerta_thx {color: #9F6000;background-color: #FEEFB3;background-image: url(images/thx/alerta.png)}
.error_thx {color: #D8000C;background-color: #FFBABA;background-image: url(images/thx/mal.png)}
.thx_list{position: absolute;font-size: 10px;font-weight: bold;border: 1px solid #FFFFFF;border-radius: 4px;padding: 20px;margin: 5px;background: #FFFFFF;width: 350px;opacity: 0.8;text-align: center}
.cs0{display: none;}
.thx_list_normal{font-size: 10px;font-weight: bold;border-top: 1px solid #9F9696;border-bottom: 1px solid #9F9696;background: transparent;opacity: 0.8;border-style: dashed;border-left: none;border-right: none}
.thx_list_normal_thead {background: none;color: #333;border-bottom: 1px dotted #33A868;padding: 8px;margin: -10px}
.thx_list_avatar{width: 20px;height: 20px;background-color: #FCFDFD;border: 1px solid #F0F0F0;padding: 3px;border-radius: 5px}
.thx_list_username{padding: 5px;bottom: 10px;position: relative}
.thx_window{background: #DFF2BF !important;color: #000000 !important;border-width: 1px 1px 2px !important;border-style: solid !important;border-color: #DBF2BF !important;border-radius: 3px}
.thx_rec{display: block;color: green}
.thx_rec a:link,.thx_rec a:active{color: green;font-weight: bold;float: right}
.thx_giv{display: block;color: blue;}
.thx_giv a:link,.thx_giv a:active{color: blue;font-weight: bold;float: right;}
.jGrowl-notification button{background: none;border: none;color: #fff}
.thx_given{font-size: 10px;display: block;width: 40px;padding: 5px;margin: 5px 0px;font-weight: bold;color: #ffffff;border-radius: 3px;background: none repeat scroll 0% 0% #3C3CB3;text-shadow: 0px 0px 1px #f0f0f0}
.thx_received{font-size: 10px;display: block;width: 40px;padding: 5px;margin: 5px 0px;font-weight: bold;color: #ffffff;border-radius: 3px;background: none repeat scroll 0% 0% #4b9134;text-shadow: 0px 0px 1px #f0f0f0}
.thx_thanked_post{background-image: linear-gradient(to bottom, #DFF0D8 0px, #C8E5BC 100%);background-repeat: repeat-x;box-shadow: 0px 1px 0px rgba(255, 255, 255, 0.25) inset, 0px 1px 2px rgba(0, 0, 0, 0.05);border-radius: 10px;padding: 10px;min-height: 30px}
.thx_meter{width:120px;height:10px;line-height: 13px;background: url(images/thx/bg_green.gif) repeat-x top left}
.thx_meter_text {position: absolute;font-size: 8px;color: #424242;padding: 0px 25px;margin-top: -2px}
.thx_thanked_post_img{width: 20px;height: 20px;float: right;opacity: 0.8;background-image: linear-gradient(to bottom, #ffffff 0px, #f0f0f0 100%);border-radius: 3px;padding: 5px;border: 1px solid #f0f0f0}
#thx_spinner {display: block;position: absolute;z-order:1000;background: #fff;text-align: center;vertical-align: middle;z-index: 999999;border-radius: 5px;}
#thx_spinner img {position: relative;top: 30%}',
			'lastmodified' => TIME_NOW,
            'cachefile' => 'thx_buttons.css'
		);

		require_once MYBB_ADMIN_DIR.'inc/functions_themes.php';
		cache_stylesheet($style['tid'], $style['cachefile'], $style['stylesheet']);
		update_theme_stylesheet_list($theme['tid'], false, true);			

	// Verify if myalerts exists and if compatible with 1.8.x then add alert type
	if(function_exists("myalerts_info")){
		// Load myalerts info into an array
		$my_alerts_info = myalerts_info();
		// Set version info to a new var
		$verify = $my_alerts_info['version'];
		// If MyAlerts 2.0 or better then do this !!!
		if($verify >= "2.0.0"){
			// Load cache data and compare if version is the same or don't
			$myalerts_plugins = $cache->read('mybbstuff_myalerts_alert_types');
			if($myalerts_plugins['thanks']['code'] != 'thanks'){
			//Adding alert type to db
			$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
				$alertType = new MybbStuff_MyAlerts_Entity_AlertType();
				$alertType->setCode('thanks');
				$alertType->setEnabled(true);
			$alertTypeManager->add($alertType);
			}
		}	
	}

	// Adding new group of templates for this plugin...  
	$templategrouparray = array(
		'prefix' => 'thanks',
		'title'  => 'Thanks'
	);
	$db->insert_query("templategroups", $templategrouparray);

	// Adding every template needed ...
	$templatearray = array(
		'title' => 'thanks_postbit_list',
		'template' => "<div class=\"thx_list_normal\" id=\"thx{\$post[\'pid\']}\"{\$thx_list_style}>
	<div class=\"thx_list_normal_thead\">{\$lang->thx_latest_entries}</div>
	<br />
	<span>{\$entries}</span>
	<br />
	<span>
		<a href=\"{\$mybb->settings[\'bburl\']}/thx.php?thanked_pid={\$post[\'pid\']}&amp;my_post_key={\$mybb->post_code}\">{\$lang->thx_latest_entries_view_all}</a>
	</span>
</div>",
		'sid' => '-2',
		'version' => '1800',
		'dateline' => TIME_NOW
		);	
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		'title' => 'thanks_postbit_list_modal',
		'template' => "<div class=\"modal\">
	<div class=\"thx_list\" id=\"thx{\$post[\'pid\']}\">
		<div class=\"thead\">{\$lang->thx_latest_entries}</div>
		<br />
		<span>{\$entries}</span>
		<br />
		<span style=\"float: right; margin-top: -10px;\">
			<a href=\"{\$mybb->settings[\'bburl\']}/thx.php?thanked_pid={\$post[\'pid\']}&amp;my_post_key={\$mybb->post_code}\">{\$lang->thx_latest_entries_view_all}</a>
		</span>
	</div>	
</div>",
		'sid' => '-2',
		'version' => '1800',
		'dateline' => TIME_NOW
		);	
	$db->insert_query("templates", $templatearray);
		
	$templatearray = array(
		'title' => 'thanks_postbit_list_entries',
		'template' => "<a href=\"{\$thx[\'profile_link\']}\">{\$thx[\'avatar\']}<span class=\"thx_list_username\">{\$thx[\'user_name\']}</span>{\$thx[\'date\']}</a>{\$thx[\'sep\']}",
		'sid' => '-2',
		'version' => '1800',
		'dateline' => TIME_NOW
		);	
		$db->insert_query("templates", $templatearray);
			
	$templatearray = array(
		'title' => 'thanks_memprofile',
		'template' => "<br />
<table id=\"thx_profile\" border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\">
	<tr>
		<td class=\"thead\"><strong>{\$lang->thx_title}</strong></td>
	</tr>
	<tr>
		<td class=\"trow1\">
			{\$memprofile[\'thx_detailed_info\']}
			<br />
			{\$memprofile[\'thx_info\']}
			<br />
			{\$memprofile[\'thx_info2\']}
		</td>
	</tr>
</table>
<br />",
		'sid' => '-2',
		'version' => '1800',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);	
	
	$templatearray = array(
		'title' => 'thanks_hide_tag',
		'template' => "<div class=\"alerta_thx message\">{\$msg}</div>",
		'sid' => '-2',
		'version' => '1800',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);	
	
	$templatearray = array(
		'title' => 'thanks_unhide_tag',
		'template' => "<div class=\"exito_thx message\">{\$msg}</div>",
		'sid' => '-2',
		'version' => '1800',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);	

	$templatearray = array(
		'title' => 'thanks_guests_tag',
		'template' => "<div class=\"error_thx message\">{\$msg}</div>",
		'sid' => '-2',
		'version' => '1800',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);	

	$templatearray = array(
		'title' => 'thanks_admins_tag',
		'template' => "<div class=\"info_thx message\">{\$msg}</div>",
		'sid' => '-2',
		'version' => '1800',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);	

	$templatearray = array(
		'title' => 'thanks_results',
		'template' => "<tr>
	<td class=\"{\$trow}\">
		<div class=\"float_left\">{\$gived[\'avatar\']}{\$gived[\'username\']}</div>
		<div class=\"float_right thanked\">
			<span class=\"thx_given\"><span class=\"fa fa-thumbs-up\">&nbsp;</span>{\$gived[\'thx\']}</span>
			<span class=\"thx_received\"><span class=\"fa fa-thumbs-up\">&nbsp;</span>{\$gived[\'thxcount\']}</span>
		</div>
	</td>			
	<td class=\"{\$trow}\">
		{\$gived[\'txid\']}
	</td>		
	<td class=\"{\$trow}\">
		<a href=\"{\$gived[\'url\']}\">{\$gived[\'subject\']}</a>
	</td>	
	<td class=\"{\$trow}\">
		<div class=\"float_left\">{\$gived[\'ugavatar\']}{\$gived[\'ugname\']}</div>
		<div class=\"float_right thanked\">
			<span class=\"thx_given\"><span class=\"fa fa-thumbs-up\">&nbsp;</span>{\$gived[\'uthx\']}</span>
			<span class=\"thx_received\"><span class=\"fa fa-thumbs-up\">&nbsp;</span>{\$gived[\'uthxcount\']}</span>
		</div>
	</td>
	<td class=\"{\$trow}\" align=\"center\">
		{\$gived[\'time\']}
	</td>
</tr>",
		'sid' => '-2',
		'version' => '1800',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);	

	$templatearray = array(
		'title' => 'thanks_results_none',
		'template' => "<tr>
	<td class=\"trow1\" colspan=\"5\" align=\"center\">
		{\$lang->thx_empty}
	</td>
</tr>",
		'sid' => '-2',
		'version' => '1806',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);	

	$templatearray = array(
		'title' => 'thanks_send_mail_thread',
		'template' => "<br /><label><input type=\"checkbox\" class=\"checkbox\" name=\"thx_send_mail\" value=\"1\"{\$thx_send_mail} />&nbsp;<strong>{\$lang->thx_send_mail_thread}</strong></label>",
		'sid' => '-2',
		'version' => '1806',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);	

	$templatearray = array(
		'title' => 'thanks_send_mail_post',
		'template' => "<br /><label><input type=\"checkbox\" class=\"checkbox\" name=\"thx_send_mail\" value=\"1\"{\$thx_send_mail} />&nbsp;<strong>{\$lang->thx_send_mail_post}</strong></label>",
		'sid' => '-2',
		'version' => '1800',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);	
	
	$templatearray = array(
		'title' => 'thanks_content',
		'template' => "<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"text-align: center;\">
	<tr>
		<td class=\"thead\" colspan=\"5\">
			<strong>{\$lang->thx_system_dnt}</strong>
		</td>
	</tr>
	<tr>
		<td class=\"tcat smalltext\"><strong>{\$lang->thx_user}</strong></td>
		<td class=\"tcat smalltext\"><strong>{\$lang->thx_id}</strong></td>
		<td class=\"tcat smalltext\"><strong>{\$lang->thx_details}</strong></td>
		<td class=\"tcat smalltext\"><strong>{\$lang->thx_added}</strong></td>
		<td class=\"tcat smalltext\"><strong>{\$lang->thx_date}</strong></td>
	</tr>
	{\$users_list}
</table>",
		'sid' => '-2',
		'version' => '1800',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);	

	$templatearray = array(
		'title' => 'thanks_page',
		'template' => "<html>
	<head>
		<title>{\$mybb->settings[\'bbname\']} - {\$lang->thx_system_dnt}</title>
		{\$headerinclude}
	</head>
	<body>
		{\$header}
		<form method=\"post\" action=\"thx.php?action=search\">
			<input class=\"textarea\" name=\"fromid\" type=\"number\" placeholder=\"{\$lang->thx_fromid}\" min=\"1\" style=\"text-align:center;\" />
			<input class=\"textarea\" name=\"byid\" type=\"number\" placeholder=\"{\$lang->thx_toid}\" min=\"1\" style=\"text-align:center;\" />
			<input class=\"textarea\" name=\"pid\" type=\"number\" placeholder=\"{\$lang->thx_postid}\" min=\"1\" style=\"text-align:center;\" />
			<input name=\"my_post_key\" type=\"hidden\" value=\"{\$mybb->post_code}\" />			
			<input class=\"button\" name=\"searchlikes\" type=\"submit\" value=\"Search\" />
		</form>		
		{\$lang->thx_found}{\$numtot}		
		{\$multipage}
		{\$content}
		{\$multipage}
		{\$footer}
	</body>
</html>",
		'sid' => '-2',
		'version' => '1800',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);	

	// Add task to set max ammoun per day running every day...
	$thx_task = array(
		"title" => "Thanks per day",
		"description" => "Set all counters to 0 for every user on max ammount",
		"file" => "thx",
		"minute" => '0',
		"hour" => '0',
		"day" => '*',
		"month" => '*',
		"weekday" => '*',
		"nextrun" => time() + (1*24*60*60),
		"enabled" => '1',
		"logging" => '1'
	);
	$db->insert_query("tasks", $thx_task);

	// Add settings group for this plugin
    $query = $db->simple_select("settinggroups", "COUNT(*) as thx_rows");
    $thx_rows = $db->fetch_field($query, "thx_rows");
	
	$thx_group = array(
		"name"			=> "Gracias",
		"title"			=> $db->escape_string($lang->thx_opt_title),
		"description"	=> $db->escape_string($lang->thx_opt_desc),
		"disporder"		=> $thx_rows+1,
		"isdefault"		=> 0
	);	
	$db->insert_query("settinggroups", $thx_group);
	$gid = $db->insert_id();
	
	// Add every setting to be used by this plugin
	$thx[]= array(
		"name"			=> "thx_active",
		"title"			=> $db->escape_string($lang->thx_opt_enable),
		"description"	=> $db->escape_string($lang->thx_opt_enable_desc),
		"optionscode" 	=> "onoff",
		"value"			=> 1,
		"disporder"		=> 10,
		"gid"			=> (int)$gid,
	);

	$thx[] = array(
		"name"			=> "thx_hidesystem_notgid",
		"title"			=> $db->escape_string($lang->thx_ngid_title),
		"description"   => $db->escape_string($lang->thx_ngid_desc),
		"optionscode" 	=> "groupselect",
		"value"			=> '1,5,7',
		"disporder"		=> 20,
		"gid"			=> $db->escape_string($gid),
	);	
	
	$thx[] = array(
		"name"			=> "thx_count",
		"title"			=> $db->escape_string($lang->thx_count_title),
		"description"	=> $db->escape_string($lang->thx_count_desc),
		"optionscode" 	=> "onoff",
		"value"			=> 1,
		"disporder"		=> 30,
		"gid"			=> (int)$gid,
	);

	$thx[] = array(
		"name"			=> "thx_counter",
		"title"			=> $db->escape_string($lang->thx_counter_title),
		"description"	=> $db->escape_string($lang->thx_counter_desc),
		"optionscode" 	=> "onoff",
		"value"			=> 1,
		"disporder"		=> 40,
		"gid"			=> (int)$gid,
	);	
	$thx[] = array(
		"name"			=> "thx_del",
		"title"			=> $db->escape_string($lang->thx_del_title),
		"description"	=> $db->escape_string($lang->thx_del_desc),
		"optionscode" 	=> "onoff",
		"value"			=> 1,
		"disporder"		=> 50,
		"gid"			=> (int)$gid,
	);
	
	$thx[] = array(
		"name"			=> "thx_hidemode",
		"title"			=> $db->escape_string($lang->thx_date_title),
		"description"	=> $db->escape_string($lang->thx_date_desc),
		"optionscode" 	=> "onoff",
		"value"			=> 1,
		"disporder"		=> 60,
		"gid"			=> (int)$gid,
	);
	
	$thx[] = array(
		"name"			=> "thx_hidesystem",
		"title"			=> $db->escape_string($lang->thx_hide_title),
		'description'   => $db->escape_string($lang->thx_hide_desc),
		"optionscode" 	=> "yesno",
		"value"			=> 1,
		"disporder"		=> 70,
		"gid"			=> (int)$gid,
	);

	$thx[] = array(
		"name"			=> "thx_hidesystem_tag",
		"title"			=> $db->escape_string($lang->thx_hidetag_title),
		'description'   => $db->escape_string($lang->thx_hidetag_desc),
		"optionscode" 	=> "text",
		"value"			=> $db->escape_string($lang->thx_hidetag_value),
		"disporder"		=> 80,
		"gid"			=> (int)$gid,
	);	

	$thx[] = array(
		"name"			=> "thx_hidesystem_gid",
		"title"			=> $db->escape_string($lang->thx_gid_title),
		"description"   => $db->escape_string($lang->thx_gid_desc),
		"optionscode" 	=> "groupselect",
		"value"			=> 4,
		"disporder"		=> 90,
		"gid"			=> (int)$gid,
	);	

    $thx[] = array(
        'name' 			=> "thx_limit",
        'title' 		=> $db->escape_string($lang->thx_limit_title),
        'description' 	=> $db->escape_string($lang->thx_limit_desc),
        'optionscode' 	=> 'yesno',
        'value' 		=> 1,
        'disporder' 	=> 100,
		"gid"			=> (int)$gid,
    );  	
	
    $thx[] = array(
        'name' 			=> "thx_reputation",
        'title' 		=> $db->escape_string($lang->thx_rep_title),
        'description' 	=> $db->escape_string($lang->thx_rep_desc),
        'optionscode' 	=> 'select \n1='.$db->escape_string($lang->thx_rep_op1).' \n2='.$db->escape_string($lang->thx_rep_op2).' \n3='.$db->escape_string($lang->thx_rep_op3).' \n4='.$db->escape_string($lang->thx_rep_op4),
        'value' 		=> 3,
        'disporder' 	=> 110,
		"gid"			=> (int)$gid,
    );  	

    $thx[] = array(
        'name' 			=> "thx_antiflood",
        'title' 		=> $db->escape_string($lang->thx_antiflood_title),
        'description' 	=> $db->escape_string($lang->thx_antiflood_desc),
        'optionscode' 	=> 'select \n1='.$db->escape_string($lang->thx_antiflood_op1).' \n2='.$db->escape_string($lang->thx_antiflood_op2).' \n3='.$db->escape_string($lang->thx_antiflood_op3).' \n4='.$db->escape_string($lang->thx_antiflood_op4).' \n5='.$db->escape_string($lang->thx_antiflood_op5),
        'value' 		=> 3,
        'disporder' 	=> 110,
		"gid"			=> (int)$gid,
    );  
	
    $thx[] = array(
        'name' 			=> "thx_list_modal",
        'title' 		=> "Do you enable modal box on list load ?",
        'description' 	=> "If you enable on click load list on modal for every counter on posts to see who was thanked on post, else, list was displayed before post contents if any.",
        'optionscode' 	=> 'yesno',
        'value' 		=> 1,
        'disporder' 	=> 120,
		"gid"			=> (int)$gid,
    );  

    $thx[] = array(
        'name' 			=> "thx_avatar_modal",
        'title' 		=> "Do you enable avatar on thanks list ?",
        'description' 	=> "If you disable this, only usernames must be shown into thanks list.",
        'optionscode' 	=> 'yesno',
        'value' 		=> 1,
        'disporder' 	=> 130,
		"gid"			=> (int)$gid,
    );  
	
    $thx[] = array(
        'name' 			=> "thx_send_mail",
        'title' 		=> "Send and e-mail when someone thanks on threads ?",
        'description' 	=> "Do you want to users threads can add new checkfield to receive an e-mail when some user thanks to the post author.",
        'optionscode' 	=> 'yesno',
        'value' 		=> 1,
        'disporder' 	=> 140,
		"gid"			=> (int)$gid,
    );  

    $thx[] = array(
        'name' 			=> "thx_mark_better",
        'title' 		=> "Mark the most thanked post ?",
        'description' 	=> "Shows a marked color and an star to better responses(if there are more of one with the max ammount) on every thread.",
        'optionscode' 	=> 'yesno',
        'value' 		=> 1,
        'disporder' 	=> 150,
		"gid"			=> (int)$gid,
    ); 	

    $thx[] = array(
        'name' 			=> "thx_meter",
        'title' 		=> "Shows a thanks meter for users on postbit ?",
        'description' 	=> "Shows a percent meter for every user with their own thanks count based on total thanks received.",
        'optionscode' 	=> 'yesno',
        'value' 		=> 1,
        'disporder' 	=> 160,
		"gid"			=> (int)$gid,
    ); 	

    $thx[] = array(
        'name' 			=> "thx_newpoints",
        'title' 		=> "Use Newpoints bridge for this plugin? (You have to install Newpoints to use this feature)",
        'description' 	=> "If you enable this feature you have to install newpoints for mybb plugin and set own preferences on usergroups thankyou system values to earn points.",
        'optionscode' 	=> 'yesno',
        'value' 		=> 0,
        'disporder' 	=> 170,
		"gid"			=> (int)$gid,
    ); 	
	
	// Insert settings to db...
	foreach($thx as $t)
	{
		$db->insert_query("settings", $t);
	}

	// Load file to set template changes...
	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';	
	// Insert all template changes for templates that exist on MyBB...
	find_replace_templatesets("postbit", '#'.preg_quote('{$post[\'button_edit\']}').'#', '{$post[\'thanks\']}{$post[\'button_edit\']}');	
	find_replace_templatesets("postbit", '#'.preg_quote('{$post[\'message\']}').'#', '{$post[\'thx_counter\']}<div id="thxpid_{$post[\'pid\']}">{$post[\'message\']}</div>{$post[\'thx_list\']}');	
	find_replace_templatesets("postbit", '#'.preg_quote('{$post[\'user_details\']}').'#', '{$post[\'user_details\']}{$post[\'thanks_count\']}{$post[\'thanked_count\']}{$post[\'thx_meter\']}');
	find_replace_templatesets("postbit_classic", '#'.preg_quote('{$post[\'message\']}').'#', '{$post[\'thx_counter\']}<div id="thxpid_{$post[\'pid\']}">{$post[\'message\']}</div>{$post[\'thx_list\']}');		
	find_replace_templatesets("postbit_classic", '#'.preg_quote('{$post[\'button_edit\']}').'#', '{$post[\'thanks\']}{$post[\'button_edit\']}');		
	find_replace_templatesets("postbit_classic", '#'.preg_quote('{$post[\'user_details\']}').'#', '{$post[\'user_details\']}{$post[\'thanks_count\']}{$post[\'thanked_count\']}{$post[\'thx_meter\']}');	
	find_replace_templatesets("showthread", "#".preg_quote('{$headerinclude}').'#','{$headerinclude}'."\r\n".'{$thx_script}');
	find_replace_templatesets('member_profile', '#{\$profilefields}#', '{\$profilefields}'."\r\n".'{\$memprofile[\'thx_details\']}');
	find_replace_templatesets("newthread", "#".preg_quote('{$disablesmilies}').'#','{$disablesmilies}{$thx_send_mail}');
	find_replace_templatesets("newreply", "#".preg_quote('{$disablesmilies}').'#','{$disablesmilies}{$thx_send_mail}');
	find_replace_templatesets("newpoints_home", "#".preg_quote('{$lang->newpoints_home_desc}').'#','{$lang->newpoints_home_desc}{$thx_newpoints}');
	if($mybb->version_code >= 1808)
	{ 	
    find_replace_templatesets("codebuttons", '#'.preg_quote('<script type="text/javascript" src="{$mybb->asset_url}/jscripts/bbcodes_sceditor.js?ver=1808"></script>').'#', '<script type="text/javascript" src="{$mybb->asset_url}/jscripts/bbcodes_sceditor.js?ver=1808"></script>
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/oculto.js?ver=1805"></script>	
<script type="text/javascript">
    var hide_tag = "{$mybb->settings[\'thx_hidesystem_tag\']}";
    var hide_tag_title = "{$lang->thx_hide_tag_title}";
    var hide_tag_content = "{$lang->thx_hide_tag_content}";	
</script>');
	}
	else if($mybb->version_code <= 1807 && $mybb->version_code >= 1804)
	{
    find_replace_templatesets("codebuttons", '#'.preg_quote('<script type="text/javascript" src="{$mybb->asset_url}/jscripts/bbcodes_sceditor.js?ver=1804"></script>').'#', '<script type="text/javascript" src="{$mybb->asset_url}/jscripts/bbcodes_sceditor.js?ver=1804"></script>
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/oculto.js?ver=1805"></script>	
<script type="text/javascript">
    var hide_tag = "{$mybb->settings[\'thx_hidesystem_tag\']}";
    var hide_tag_title = "{$lang->thx_hide_tag_title}";
    var hide_tag_content = "{$lang->thx_hide_tag_content}";	
</script>');		
	}
	find_replace_templatesets("codebuttons", "#".preg_quote('|maximize').'#',',{$mybb->settings[\'thx_hidesystem_tag\']}|maximize');
	
	// Update cache for usergroups to set permissions of max_ammount and rep vals for every usergroup...
	$cache->update_usergroups();
	// Update forum info is not necesary but anyway update cache...
   	$cache->update_forums();
	// Update tasks values to set enabled and running everyday new task... 
	$cache->update_tasks();	

	// Rebuild settings, this load all necesary info used by this plugin (New settings used by this plugin like enabled, groups used, hide tags, an many more)...
	rebuild_settings();
}
// Verify if this plugin is activated or wont !!!
function thx_is_activated()
{
    global $cache, $plugins;

	// Read cache info about this plugin and say if this are enabled or wont
    $plugins = $cache->read('plugins');
	// Load from cache active value and set in a new var
    $activePlugins = $plugins['active'];
	//Create var to verify if active by default set value to false....
    $isActive = false;

	// Verify data exist by cache and then set value to true to mark plugin are enabled...
    if(in_array('thx', $activePlugins)) {
        $isActive = true;
    }

	// Return value and verify is installed
    return thx_is_installed() && $isActive;
}
// Run on plugin deactivation
function thx_deactivate()
{
	global $db, $cache;

	// Get the information available for this plugin to use it  
    $plugin_info = thx_info();

	// Load cache data and compare if version is the same or don't
    $dnt_plugins = $cache->read('dnt_plugins');

	if($plugin_info['version'] > $dnt_plugins['thx']['version']){
		thx_remove();
 	}
}
// Run on plugin deactivation if necesary....
function thx_remove(){
	global $mybb, $db, $cache;
	
  	$db->delete_query('themestylesheets', "name='thx_buttons.css'");
	$query = $db->simple_select('themes', 'tid');
	while($style = $db->fetch_array($query))
	{
		require_once MYBB_ADMIN_DIR.'inc/functions_themes.php';
		cache_stylesheet($style['tid'], $style['cachefile'], $style['stylesheet']);
		update_theme_stylesheet_list($style['tid'], false, true);	
	}

	$db->delete_query("settings", "name LIKE ('thx_%')");
	$db->delete_query("settinggroups", "name='Gracias'");
	$db->delete_query("templategroups", "prefix='thanks'");
	$db->delete_query("templates", "title LIKE ('thanks_%')");
	if(function_exists("myalerts_info")){
		// Load myalerts info into an array
		$my_alerts_info = myalerts_info();
		// Set version info to a new var
		$verify = $my_alerts_info['version'];
		// If MyAlerts 2.0 or better then do this !!!
		if($verify >= "2.0.0"){	
			if($db->table_exists("alert_types")){
				$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();
				$alertTypeManager->deleteByCode('thanks');
			}
		}
	}
	
	$db->delete_query('tasks', 'file=\'thx\'');

	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets("postbit", '#'.preg_quote('<div id="thxpid_{$post[\'pid\']}">{$post[\'message\']}</div>').'#', '{$post[\'message\']}', 0);	
	find_replace_templatesets("postbit", '#'.preg_quote('{$post[\'thx_list\']}').'#', '', 0);
	find_replace_templatesets("postbit", '#'.preg_quote('{$post[\'thanks_count\']}').'#', '', 0);
	find_replace_templatesets("postbit", '#'.preg_quote('{$post[\'thanked_count\']}').'#', '', 0);
	find_replace_templatesets("postbit", '#'.preg_quote('{$post[\'thanks\']}').'#', '', 0);
	find_replace_templatesets("postbit", '#'.preg_quote('{$post[\'thx_counter\']}').'#', '', 0);
	find_replace_templatesets("postbit", '#'.preg_quote('{$post[\'thx_meter\']}').'#', '', 0);
	find_replace_templatesets("postbit_classic", '#'.preg_quote('<div id="thxpid_{$post[\'pid\']}">{$post[\'message\']}</div>').'#', '{$post[\'message\']}', 0);	
	find_replace_templatesets("postbit_classic", '#'.preg_quote('{$post[\'thx_list\']}').'#', '', 0);
	find_replace_templatesets("postbit_classic", '#'.preg_quote('{$post[\'thanks_count\']}').'#', '', 0);
	find_replace_templatesets("postbit_classic", '#'.preg_quote('{$post[\'thanked_count\']}').'#', '', 0);	
	find_replace_templatesets("postbit_classic", '#'.preg_quote('{$post[\'thanks\']}').'#', '', 0);
	find_replace_templatesets("postbit_classic", '#'.preg_quote('{$post[\'thx_counter\']}').'#', '', 0);	
	find_replace_templatesets("postbit_classic", '#'.preg_quote('{$post[\'thx_meter\']}').'#', '', 0);
	find_replace_templatesets("showthread", "#".preg_quote('{$thx_script}').'#', '', 0);
	find_replace_templatesets("newthread", "#".preg_quote('{$thx_send_mail}').'#','', 0);
	find_replace_templatesets("newreply", "#".preg_quote('{$thx_send_mail}').'#','', 0);	
	find_replace_templatesets("newpoints_home", "#".preg_quote('{$thx_newpoints}').'#', '', 0);	
	find_replace_templatesets("showthread", "#".preg_quote('<script type="text/javascript" src="{$mybb->settings[\'bburl\']}/jscripts/thx.js"></script>').'#', '', 0);
	find_replace_templatesets("showthread", "#".preg_quote('<script type="text/javascript" src="{$mybb->settings[\'bburl\']}/jscripts/thx.js?ver=1804"></script>').'#', '', 0);	
	find_replace_templatesets("showthread", "#".preg_quote('<script type="text/javascript" src="{$mybb->settings[\'bburl\']}/jscripts/thx?ver=1805"></script>').'#', '', 0);	
	find_replace_templatesets("showthread", "#".preg_quote('<script type="text/javascript" src="{$mybb->asset_url}}/jscripts/thx.js"></script>').'#', '', 0);
	find_replace_templatesets("showthread", "#".preg_quote('<script type="text/javascript" src="{$mybb->asset_url}/jscripts/thx.js?ver=1804"></script>').'#', '', 0);	
	find_replace_templatesets("showthread", "#".preg_quote('<script type="text/javascript" src="{$mybb->asset_url}/jscripts/thx?ver=1805"></script>').'#', '', 0);	
	find_replace_templatesets("showthread", "#".preg_quote('<script type="text/javascript">
	var thx_msg_title = "{$lang->thx_msg_title}";	
	var thx_msg_add = "{$lang->thx_msg_add}";
	var thx_msg_remove = "{$lang->thx_msg_remove}";					
</script>').'#', '', 0);	
	find_replace_templatesets("showthread", "#".preg_quote('{$thx_js_codebuttons}').'#', '', 0);
	find_replace_templatesets("member_profile", "#".preg_quote('{$memprofile[\'thx_details\']}').'#', '', 0);
	find_replace_templatesets("codebuttons", "#".preg_quote('<script type="text/javascript" src="{$mybb->asset_url}/jscripts/oculto.js"></script>').'#', '', 0);
	find_replace_templatesets("codebuttons", "#".preg_quote('<script type="text/javascript" src="{$mybb->asset_url}/jscripts/oculto.js?ver=1804"></script>').'#', '', 0);
	find_replace_templatesets("codebuttons", "#".preg_quote('<script type="text/javascript" src="{$mybb->asset_url}/jscripts/oculto.js?ver=1805"></script>').'#', '', 0);
	find_replace_templatesets("codebuttons", "#".preg_quote(',{$mybb->settings[\'thx_hidesystem_tag\']}').'#','', 0);
	find_replace_templatesets("codebuttons", "#".preg_quote('<script type="text/javascript">
    var hide_tag = "{$mybb->settings[\'thx_hidesystem_tag\']}";
    var hide_tag_title = "{$lang->thx_hide_tag_title}";
    var hide_tag_content = "{$lang->thx_hide_tag_content}";	
</script>').'#','', 0);		

	$cache->update_usergroups();
   	$cache->update_forums();	
	$cache->update_tasks();	
	
	rebuild_settings();
}
// Run on plugin uninstallation.
function thx_uninstall()
{
	global $db, $mybb;
	ini_set('max_execution_time', 300);	

	if($mybb->request_method != 'post')
	{
		global $page;
		$page->output_confirm_action('index.php?module=config-plugins&action=deactivate&uninstall=1&plugin=thx', 'If you set Yes all data would be deleted and can not be recovered, otherwise you can reinstall it and recover all data, but you need to run rebuild task for thanks system', 'Do you want to delete all data');
	}
	
	if($db->field_exists("thx", "users"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."users DROP thx, DROP thxcount");
	}

	if($db->field_exists("thx_ammount", "users"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."users DROP thxpost, DROP thx_ammount");
	}

	if($db->field_exists("thx_antiflood", "users"))		
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."users DROP thx_antiflood");
	}
	
	if($db->field_exists("thx_max_ammount", "usergroups"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."usergroups DROP thx_max_ammount, DROP thx_rep_points");
	}

	if($db->field_exists("thx", "promotions"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."promotions DROP thx");
	}

	if($db->field_exists("thxtype", "promotions"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."promotions DROP thxtype");
	}
	
	if($db->field_exists("thx_newpoints_earn", "usergroups"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."usergroups DROP thx_newpoints_earn, DROP thx_newpoints_give");
	}
	
	if($db->field_exists("pthx", "posts"))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."posts DROP pthx");
	}

	if(!$db->field_exists("thx_send_mail", "posts"))
	{
		$sq[] = "ALTER TABLE ".TABLE_PREFIX."posts DROP `thx_send_mail`";
	}
	
	$db->query("DELETE FROM ".TABLE_PREFIX."datacache WHERE title='dnt_plugins'");

	if(!isset($mybb->input['no']))
	{
		$db->drop_table('thx');
	}
	else if($db->table_exists("thx"))
	{
		$db->query("RENAME TABLE ".TABLE_PREFIX."thx TO ".TABLE_PREFIX."thx_backup");
	}
	
	thx_remove();
}
// Load plugin information for cnfig link...
function thx_getdata(&$thx_config_link)
{
	global $mybb, $db, $lang, $thx_config_link; $alertas;

	$thx_config_link = '';

	$lang->load("thx", false, true);
	
	if ($mybb->settings['thx_active'] == 1)
	{
		$download = htmlspecialchars_uni("https://github.com/MyBBStuff/MyAlerts");
		$integrate = '<a href="index.php?module=config-plugins&amp;action=thx_myalerts_integrate">Integrate with MyAlerts</a>';
		if(function_exists("myalerts_info"))
		{
			$my_alerts_info = myalerts_info();
			$verify = $my_alerts_info['version'];
		}
		else
		{
			$verify = "";
		}
		$thx_config_link = '<div style="float: right;"><a href="index.php?module=config&action=change&search=Gracias" style="color:#035488; background: url(../images/thx/gear.png) no-repeat 0px 18px; padding: 21px; text-decoration: none;"> '. $db->escape_string($lang->thx_config) . '</a></div>';
		$thx_config_link .= '<br /><div><span style="color: rgba(34, 136, 3, 1); background: url(../images/thx/good.png) no-repeat 0px 18px; padding: 21px; text-decoration: none;">OK</span></div>';
		if(function_exists("myalerts_info"))
		{
			if(myalerts_is_activated() && thx_myalerts_status() && $verify >= 2.0 && $mybb->settings['thx_reputation'] == 2 || thx_myalerts_status() && $verify >= 2.0 && $mybb->settings['thx_reputation'] == 4){
				$thx_config_link .= '<div style="float: left;"><span style="color: rgba(34, 136, 3, 1); background: url(../images/thx/good.png) no-repeat 0px 18px; padding: 21px; text-decoration: none;"> ' . $db->escape_string($lang->thx_config_alerts_thx) . '</div><br />';		
			}
			else if(myalerts_is_activated() && $verify > 1.0 && $verify <= 1.05 || myalerts_is_activated() && $verify >= 2.0 && $mybb->settings['thx_reputation'] == 1 || myalerts_is_activated() && $verify >= 2.0 && $mybb->settings['thx_reputation'] == 3){
				$thx_config_link .= '<div style="float: left;"><span style="color: rgba(136, 17, 3, 1); background: url(../images/icons/exclamation.png) no-repeat 0px 18px; padding: 18px; text-decoration: none;">  ' . $db->escape_string($lang->thx_config_alerts_thx_config) . '</div><br />';		
			}
			else if(myalerts_is_activated() && $verify >= 2.0 && ($mybb->settings['thx_reputation'] == 2 || $mybb->settings['thx_reputation'] == 4)){
				$thx_config_link .= '<div style="float: left;"><span style="color: rgba(136, 17, 3, 1); background: url(../images/icons/exclamation.png) no-repeat 0px 18px; padding: 18px; text-decoration: none;">  ' . $lang->sprintf($lang->thx_config_alerts_missing, $integrate) . '</div><br />';	
			}
			else if(!myalerts_is_activated() && ($mybb->settings['thx_reputation'] == 4 || $mybb->settings['thx_reputation'] == 2))
			{
				$thx_config_link .= '<div style="float: left;"><span style="color: rgba(136, 17, 3, 1); background: url(../images/icons/exclamation.png) no-repeat 0px 18px; padding: 18px; text-decoration: none;">  You have to not use Myalerts setting due Myalerts is not installed on your boards.</div><br />';				
			}
		}
		else if(!thx_myalerts_status() || empty($verify))
		{
			$thx_config_link .= '<div style="float: left;"><span style="color: #899611; background: url(../images/icons/information.png) no-repeat 0px 18px; padding: 18px; text-decoration: none;">  ' . $lang->sprintf($lang->thx_config_alerts_none, $download) . '</div><br />';	
		}			
	}
	else if ($mybb->settings['thx_active'] == 0 && isset($mybb->settings['thx_active']))
	{
		$thx_config_link = '<div style="float: right; color: rgba(136, 17, 3, 1); background: url(../images/icons/exclamation.png) no-repeat 0px 18px; padding: 21px; text-decoration: none;"> '. $db->escape_string($lang->thx_disabled) . '</div>';
	}
		
	return $thx_config_link;
}
// Review if MyAlerts is available or not for new thanks alert...
function thx_myalerts_status()
{
	global $db, $cache;

	// Load cache data and compare if version is the same or don't
    $myalerts_plugins = $cache->read('mybbstuff_myalerts_alert_types');

	if($myalerts_plugins['thanks']['code'] == 'thanks' && $myalerts_plugins['thanks']['enabled'] == 1){
		return true;
	}
	return false;
}
// Run action of MyAlerts integretion...
function thx_admin_load()
{
	global $page, $mybb;
	if($mybb->input['action'] == 'thx_myalerts_integrate')
	{
		thx_myalerts_integrate();
		exit;
	}
}
// Integration of MyAlerts without uninstall or deactivate...
function thx_myalerts_integrate(){
	global $db, $cache;
	// Verify if myalerts exists and if compatible with 1.8.x then add alert type
	if(function_exists("myalerts_info")){
		// Load myalerts info into an array
		$my_alerts_info = myalerts_info();
		// Set version info to a new var
		$verify = $my_alerts_info['version'];
		// If MyAlerts 2.0 or better then do this !!!
		if($verify >= "2.0.0"){
			// Load cache data and compare if version is the same or don't
			$myalerts_plugins = $cache->read('mybbstuff_myalerts_alert_types');
			if($myalerts_plugins['thanks']['code'] != 'thanks'){
			//Adding alert type to db
			$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
				$alertType = new MybbStuff_MyAlerts_Entity_AlertType();
				$alertType->setCode('thanks');
				$alertType->setEnabled(true);
			$alertTypeManager->add($alertType);
			flash_message("MyAlerts and Thanks System were integrated succesfully", 'success');
			admin_redirect('index.php?module=config-plugins');			
			}
			else{
				flash_message("MyAlerts version is wrong and can not integrate with thanks system or already integrated", 'error');
				admin_redirect('index.php?module=config-plugins');			
			}
		}
		else{
			flash_message("MyAlerts is not working yet on your board, verify this and try again latter", 'error');
			admin_redirect('index.php?module=config-plugins');			
		}
	}	
}

// Load templates and script for thanks system...
function thx_global_intermediate()
{
	global $mybb, $theme, $templates, $session, $lang, $code, $thx_script, $GLOBALS;

    if (!$mybb->settings['thx_active'] || !empty($session->is_spider))
    {
        return false;
    }
	
	if(isset($GLOBALS['templatelist']))
	{
		if(THIS_SCRIPT == "showthread.php"){
			$GLOBALS['templatelist'] .= ",thanks_postbit_list, thanks_postbit_list_modal, thanks_postbit_list_entries, thanks_hide_tag, thanks_unhide_tag, thanks_guests_tag, thanks_admins_tag";
		}
		else if(THIS_SCRIPT == "thx.php"){
			$GLOBALS['templatelist'] .= ",thanks_results, thanks_results_none, thanks_content, thanks_page";
		}
		else if(THIS_SCRIPT == "member.php"){
			$GLOBALS['templatelist'] .= ",thanks_memprofile";
		}
	}
}

// Load templates and script for thanks system...
function thx_global_start()
{
	global $db, $mybb, $theme, $templates, $session, $lang, $code, $thx_script, $GLOBALS, $entries, $thx_list, $thx_sep;

    if (!$mybb->settings['thx_active'] || !empty($session->is_spider))
    {
        return false;
    }
			
	$lang->load("thx", false, true);

	$thx_list = "";	
	
	if($mybb->input['action'] == "thanks_list"){
	
	$pid = $mybb->get_input('pid', 1); 
	$post = get_post($pid);
	$lang->load("thx", false, true);	
	$thx_total = 0;
	$thx_sep = "";
	
	$query = $db->query("SELECT th.txid, th.uid, th.adduid, th.pid, th.time, u.username, u.usergroup, u.displaygroup, u.avatar
		FROM ".TABLE_PREFIX."thx th
		JOIN ".TABLE_PREFIX."users u
		ON th.adduid=u.uid
		WHERE th.pid='{$pid}'
		ORDER BY th.time DESC
		LIMIT 0, 12"
	);

	while($thx = $db->fetch_array($query))
	{
		if(isset($thx['username']))
		{
			$thx['profile_link'] = get_profile_link($thx['adduid']);
			$thx['user_name'] = format_name($thx['username'], $thx['usergroup'], $thx['displaygroup']);
			if($mybb->settings['thx_avatar_modal'] == 1)
			{
				$thx['avatar'] = htmlspecialchars_uni($thx['avatar']);				
				if($thx['avatar'] != '')
				{
					$thx['avatar'] = "<img src=\"{$thx['avatar']}\" class=\"thx_list_avatar\" alt=\"Avatar\">";
				}
				else
				{
					$thx['avatar'] = "<img src=\"images/default_avatar.png\" class=\"thx_list_avatar\" alt=\"Avatar\">";
				}
			}
			else
			{
				$thx['avatar'] = "";
			}
			if($mybb->settings['thx_hidemode'])
			{			
				$thx['date'] = "&nbsp;";
			}
			else
			{
				$thx['date'] = "<span class=\"smalltext\">(" . my_date('relative', $thx['time']) . ")</span>";
			}
			$thx_total++;
			if($thx_total == 3 || $thx_total == 6 || $thx_total == 9){
				$thx['sep'] = "<br />";
			}
				eval("\$entries .= \"".$templates->get("thanks_postbit_list_entries")."\";");			
		}
		
	}
	$lang->thx_latest_entries = $lang->sprintf($lang->thx_latest_entries,$thx_total);
	eval("\$thx_list = \"".$templates->get("thanks_postbit_list_modal", 1, 0)."\";");	
	echo $thx_list;
	exit;	
	}
		
	if(THIS_SCRIPT == "showthread.php"){
	$thx_script = '<script type="text/javascript" src="' . $mybb->settings['bburl'] . '/jscripts/thx.js?ver=1805"></script>
	<script type="text/javascript">
	var thx_msg_title = "' . $lang->thx_msg_title . '";	
	var thx_msg_add = "' . $lang->thx_msg_add . '";
	var thx_msg_remove = "' . $lang->thx_msg_remove . '";	
	var thx_load_list = "' . $lang->thx_load_list . '";		
	var thx_del = "' . (int)$mybb->settings['thx_hidesystem'] . '";		
</script>';
	}
	
	// Registering alert formatter
	if((function_exists('myalerts_is_activated') && myalerts_is_activated()) && $mybb->user['uid'] && ($mybb->settings['thx_reputation'] == 2 || $mybb->settings['thx_reputation'] == 4)){
		global $cache, $formatterManager;
		// Load cache data and compare if version is the same or don't
		$myalerts_plugins = $cache->read('mybbstuff_myalerts_alert_types');
		if($myalerts_plugins['thanks']['code'] == 'thanks' && $myalerts_plugins['thanks']['enabled'] == 1){
		thanks_alerts_formatter_load();	
			if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager') && class_exists('ThanksAlertFormatter')) {
				$code = 'thanks';
				$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();
				$formatterManager->registerFormatter(new ThanksAlertFormatter($mybb, $lang, $code));
			}
		}
	}
}

// run parse message to load or not hidden contents inside hide tags...
function thx_code(&$message)
{
    global $db, $post, $mybb, $lang, $session, $theme, $altbg, $templates, $thx_cache, $forum, $fid, $pid, $announcement, $postrow, $hide_tag;

    if (!$mybb->settings['thx_active'] || !empty($session->is_spider))
    {
        return false;
    }	
	
	$lang->load("thx", false, true);

	$url = $mybb->settings['bburl'];
    $hide_tag = $mybb->settings['thx_hidesystem_tag'];	

	if($mybb->input['highlight'] == "{$hide_tag}"){		
	   $msg = $lang->thx_hide_text; 	
 	   eval("\$caja = \"".$templates->get("thanks_guests_tag",1,0)."\";");		
	   $message = $caja;
	}			

    if ($mybb->settings['thx_active'] == 0 || $mybb->settings['thx_hidesystem'] == 0)
    {
        return false;
    }
		
	$thx_forum_gid = explode(',', $mybb->settings['thx_hidesystem_gid']);
	$thx_forum_notgid = explode(',', $mybb->settings['thx_hidesystem_notgid']);
		
	if(THIS_SCRIPT == "syndication.php"){
		$msg = $lang->thx_hide_sindycation; 
		eval("\$caja = \"".$templates->get("thanks_guests_tag",1,0)."\";");		  
		$message = preg_replace("#\[$hide_tag\](.*?)\[/$hide_tag\]#is",$caja,$message);	
	}		
	if($forum['fid'] == 0 || $forum['fid'] == ''){$forum['fid'] = $fid;}
		
	if(!isset($post['pid']) && THIS_SCRIPT == "newthread.php"){
        if($forum['rulestype'] > 0 && !empty($forum['rules']))
        return false;
    }	
    if($post['pid'] == 0 || $post['pid'] == ''){
        switch(THIS_SCRIPT)
        {
            case "printthread.php" : $post['pid'] = $postrow['pid'];$post['uid'] = $postrow['uid'];$forum_fid = $postrow['fid'];break;
            case "portal.php" : $post['uid'] = $announcement['uid'];$post['pid'] = $announcement['pid'];$forum_fid = $announcement['fid'];break;
        }
    }
	//if(is_member($thx_forum_gid))
	$thx_add_gid = explode(",", $mybb->user['additionalgroups']);
	if(!empty($mybb->settings['thx_hidesystem_gid']) && !empty($mybb->user['additionalgroups'])){
		foreach($thx_add_gid as $ag){
			 if(in_array($ag, $thx_forum_gid) && $thx_forum_gid != ""){
				$msg = "$1";
				eval("\$caja = \"".$templates->get("thanks_unhide_tag",1,0)."\";");		  
				$message = preg_replace("#\[$hide_tag\](.*?)\[/$hide_tag\]#is",$caja,$message); 
			}
		}
	}
		
    if(!empty($mybb->settings['thx_hidesystem_gid']) && in_array($mybb->user['usergroup'], $thx_forum_gid))
    {
		$msg = "$1";
		eval("\$caja = \"".$templates->get("thanks_admins_tag",1,0)."\";");		  
		$message = preg_replace("#\[$hide_tag\](.*?)\[/$hide_tag\]#is",$caja,$message);      
	}
    //else if(is_member($thx_forum_notgid) || $mybb->user['uid'] == 0)
    else if(!empty($mybb->settings['thx_hidesystem_gid']) && in_array($mybb->user['usergroup'], $thx_forum_notgid) || $mybb->user['uid'] == 0)
    {	 
	   $msg = $lang->thx_hide_register; 
	   eval("\$caja = \"".$templates->get("thanks_guests_tag",1,0)."\";");		  
	   $message = preg_replace("#\[$hide_tag\](.*?)\[/$hide_tag\]#is",$caja,$message);
    }
    else{
		if ($mybb->user['uid'] == $post['uid'] && !empty($mybb->settings['thx_hidesystem_gid']) || $mybb->settings['thx_hidesystem_gid'] < 0)
		{
		   $msg = "$1";
		   eval("\$caja = \"".$templates->get("thanks_unhide_tag",1,0)."\";");		  
		   $message = preg_replace("#\[$hide_tag\](.*?)\[/$hide_tag\]#is",$caja,$message);
		}
		if($mybb->user['uid'] != $post['uid'])
		{
			$thx_user = (int)$mybb->user['uid'];
			$query=$db->query("SELECT th.txid, th.uid, th.adduid, th.pid, th.time, u.username, u.usergroup, u.displaygroup, u.avatar
				FROM ".TABLE_PREFIX."thx th
				JOIN ".TABLE_PREFIX."users u
				ON th.adduid=u.uid
				WHERE th.pid='{$post['pid']}' AND th.adduid ='{$thx_user}'
				ORDER BY th.time ASC LIMIT 1"
			);

			while($record = $db->fetch_array($query))
			{
				if($record['adduid'] == $mybb->user['uid'])
				{
					$msg = "$1";
					eval("\$caja = \"".$templates->get("thanks_unhide_tag",1,0)."\";");		  
					$message = preg_replace("#\[$hide_tag\](.*?)\[/$hide_tag\]#is",$caja,$message);
				}
				else
				{
					$msg = $lang->thx_hide_text;  
					eval("\$caja = \"".$templates->get("thanks_hide_tag",1,0)."\";");		 
					$message = preg_replace("#\[$hide_tag\](.*?)\[/$hide_tag\]#is",$caja,$message);
				}
				$done = true;
			}
			$msg = $lang->thx_hide_text;  
			eval("\$caja = \"".$templates->get("thanks_hide_tag",1,0)."\";");		 
			$message = preg_replace("#\[$hide_tag\](.*?)\[/$hide_tag\]#is",$caja,$message);
		}
	}
}
// Wanna quote hidden contents ???
function thx_quote(&$quoted_post)
{
    global $mybb, $session, $templates, $lang, $hide_tag;

		if ($mybb->settings['thx_active'] == '0' || $mybb->settings['thx_hidesystem'] == '0')
        {
          return false;
        }

        else if ($mybb->settings['thx_hidesystem'] == 1)
		{
		  $hide_tag = $mybb->settings['thx_hidesystem_tag'];	
          $quoted_post['message'] = preg_replace("#\[$hide_tag\](.*?)\[/$hide_tag\]#is","", $quoted_post['message']);
        }
}

function thx_thread()
{
	global $db, $mybb, $mycount, $thxmax, $pids, $thread;

	if($mybb->settings['thx_mark_better'])
	{
		$query = $db->query("SELECT pthx FROM ".TABLE_PREFIX."posts WHERE {$pids} ORDER BY pthx DESC LIMIT 0, 1");		
		$mycount = $db->fetch_array($query);
		$thread['thx_mycount'] = (int)$mycount;
		$db->free_result($query);	
	}
	if($mybb->settings['thx_meter'])
	{
		$thxmax = 0;
		$query = $db->query("SELECT SUM(thxcount) as thx_meter FROM ".TABLE_PREFIX."users");
		while ($most_thanks = $db->fetch_array($query))
		{
			$thxmax = (int)$most_thanks['thx_meter'];
			$thread['thx_max'] = (int)$thxmax;
		}	
		$db->free_result($query);	
	}
}

// Data to load on postbit and postbit_classic
function thx(&$post)
{
	global $db, $cache, $mybb, $lang ,$session, $theme, $altbg, $templates, $thx_cache, $thx_counter, $forum, $message, $pids, $thread, $mycount,$thxmax;;
	
	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}

    $thx_forum_notgid = explode(',', $mybb->settings['thx_hidesystem_notgid']);
	if(in_array($mybb->user['usergroup'], $thx_forum_notgid) && THIS_SCRIPT == "showthread.php" && $mybb->user['uid'] != 0){	
		return false;
	}
	
	$lang->load("thx", false, true);					
	
	if(!isset($thx_cache['mycount']) || !isset($thx_cache['thxmax']))//if($thread['firstpost'] == $post['pid'])
	{	
		$thx_cache = $cache->read('thx_cache');
		if($mybb->settings['thx_mark_better'])
		{
			if(empty($pids))
			{
				$pids = "";
				$comma = '';
				$tid = $post['tid'];
				$query = $db->simple_select("posts p", "p.pid", "p.tid='$tid'", array('order_by' => 'p.dateline', 'limit_start' => $start, 'limit' => $perpage));
				while($getid = $db->fetch_array($query))
				{
					if(empty($pid))
					{
						$pid = $getid['pid'];
					}
					$pids .= "$comma'{$getid['pid']}'";
					$comma = ",";
				}
				if($pids)
				{
					$pids = "pid IN($pids)";
				}		
			}
			if(!empty($pids) && $pids != "")
			{
				$query = $db->query("SELECT pthx FROM ".TABLE_PREFIX."posts WHERE {$pids} ORDER BY pthx DESC LIMIT 1");		
				$mycount = $db->fetch_field($query,'pthx');
				$thx_cache['mycount'] = $mycount;
				$db->free_result($query);								
			}			
		}	
		$mycount = $thx_cache['mycount'];		
		if($mybb->settings['thx_meter'])
		{
			$thxmax = 0;
			$query = $db->query("SELECT SUM(thxcount) as thx_meter FROM ".TABLE_PREFIX."users");
			while ($most_thanks = $db->fetch_array($query))
			{
				$thxmax = (int)$most_thanks['thx_meter'];
			}	
			$thx_cache['thxmax'] = $thxmax;							
			$db->free_result($query);	
		}		
		$thxmax = $thx_cache['thxmax'];	
	}

	if($mybb->settings['thx_meter'] == 1)
	{
		if($post['thxcount'] > 0 && $thxmax > 0)
		{
			$thxmed = round(($post['thxcount'] * 100) / $thxmax);
		}
		else if ($post['thxcount'] <= 0)
		{
			$thxmed = 0;
		}
		else if(($mybb->user['thxcount'] == $thx_max))
		{
			$thxmed = 100;
		}			
		$post['thx_meter'] = "<div class=\"thx_meter\" style=\"display:block;\"><span class=\"thx_meter_text\" style=\"display:block;\">{$lang->thx_meter} {$thxmed}%</span><img src=\"images/thx/green.gif\" width=\"{$thxmed}%\" height=\"10\" align=\"left\" valign=\"middle\" alt=\"{$lang->thx_meter}\" /></div>";
	}
	if($b = $post['pthx'])
	{
		$thx_list = build_thank($post['pid'], $b);
	}
	else
	{
		$thx_list = '';
	}
	
	if($mybb->settings['thx_mark_better'] == 1)
	{
		if((int)$post['pthx'] == (int)$mycount && (int)$mycount > 0)
		{
			$post['subject'] = htmlspecialchars_uni($post['subject']);
			$lang->thx_better_response = $lang->sprintf($lang->thx_better_response,$post['subject']);
			$post['message'] = "<div class=\"thx_thanked_post\"><img src='images/thx/star.png' alt='{$lang->thx_better_response}' class=\"thx_thanked_post_img\" />" . $post['message'] . "</div>";
		}
	}
	
	if($mybb->settings['thx_counter'] == 1)
	{
		$count = (int)$post['pthx'];
		$thx_link = get_post_link($post['pid'],$post['tid']);				
		if($mybb->settings['thx_list_modal'] == 1)
		{
			if($mybb->settings['seourls'] == "no" || function_exists("google_seo_url_profile"))
			{			
				if ($count == 0){$count="<a style=\"background: none;border:none;\" id=\"thanks_list{$post['pid']}\" href=\"javascript:void(0)\" onclick=\"MyBB.popupWindow('{$thx_link}&amp;action=thanks_list&amp;my_post_key={$mybb->post_code}', null, true); return false;\"><div id=\"counter{$post['pid']}\" class=\"neutral_thx\">".my_number_format($count)."</div></a>";}
				else if ($count >= 1){$count="<a style=\"background: none;border:none;\" id=\"thanks_list{$post['pid']}\" href=\"javascript:void(0)\" onclick=\"MyBB.popupWindow('{$thx_link}&amp;action=thanks_list&amp;my_post_key={$mybb->post_code}', null, true); return false;\"><div id=\"counter{$post['pid']}\" class=\"good_thx\"> ".my_number_format($count)." </div></a>";}
				else {$count="<a style=\"background: none;border:none;\" id=\"thanks_list{$post['pid']}\" href=\"javascript:void(0)\" onclick=\"MyBB.popupWindow('{$thx_link}&amp;action=thanks_list&amp;my_post_key={$mybb->post_code}', null, true); return false;\"><div id=\"counter{$post['pid']}\" class=\"bad_thx\"> ".my_number_format($count)." </div></a>";}		
			}
			else if($mybb->settings['seourls'] == "yes" || $mybb->settings['seourls'] == "yes" && !function_exists("google_seo_url_profile"))
			{
				if ($count == 0){$count="<a style=\"background: none;border:none;\" id=\"thanks_list{$post['pid']}\" href=\"javascript:void(0)\" onclick=\"MyBB.popupWindow('{$thx_link}?action=thanks_list&amp;my_post_key={$mybb->post_code}', null, true); return false;\"><div id=\"counter{$post['pid']}\" class=\"neutral_thx\">".my_number_format($count)."</div></a>";}
				else if ($count >= 1){$count="<a style=\"background: none;border:none;\" id=\"thanks_list{$post['pid']}\" href=\"javascript:void(0)\" onclick=\"MyBB.popupWindow('{$thx_link}?action=thanks_list&amp;my_post_key={$mybb->post_code}', null, true); return false;\"><div id=\"counter{$post['pid']}\" class=\"good_thx\"> ".my_number_format($count)." </div></a>";}
				else {$count="<a style=\"background: none;border:none;\" id=\"thanks_list{$post['pid']}\" href=\"javascript:void(0)\" onclick=\"MyBB.popupWindow('{$thx_link}?action=thanks_list&amp;my_post_key={$mybb->post_code}', null, true); return false;\"><div id=\"counter{$post['pid']}\" class=\"bad_thx\"> ".my_number_format($count)." </div></a>";}				
			}
			else
			{
				if ($count == 0){$count="<a style=\"background: none;border:none;\" id=\"thanks_list{$post['pid']}\" href=\"javascript:void(0)\" onclick=\"MyBB.popupWindow('{$thx_link}&amp;action=thanks_list&amp;my_post_key={$mybb->post_code}', null, true); return false;\"><div id=\"counter{$post['pid']}\" class=\"neutral_thx\">".my_number_format($count)."</div></a>";}
				else if ($count >= 1){$count="<a style=\"background: none;border:none;\" id=\"thanks_list{$post['pid']}\" href=\"javascript:void(0)\" onclick=\"MyBB.popupWindow('{$thx_link}&amp;action=thanks_list&amp;my_post_key={$mybb->post_code}', null, true); return false;\"><div id=\"counter{$post['pid']}\" class=\"good_thx\"> ".my_number_format($count)." </div></a>";}
				else {$count="<a style=\"background: none;border:none;\" id=\"thanks_list{$post['pid']}\" href=\"javascript:void(0)\" onclick=\"MyBB.popupWindow('{$thx_link}&amp;action=thanks_list&amp;my_post_key={$mybb->post_code}', null, true); return false;\"><div id=\"counter{$post['pid']}\" class=\"bad_thx\"> ".my_number_format($count)." </div></a>";}						
			}
		}
		else
		{
			$thx_total = (int)$post['pthx'];
			$lang->thx_latest_entries = $lang->sprintf($lang->thx_latest_entries,$thx_total);
			if($b = $post['pthx'])
			{
				$entries = build_thank($post['pid'], $b);
			}
			else
			{
				$entries = "";
			}
			if($mybb->user['uid'])
			{
				if ($count == 0){$count="<a style=\"background: none;border:none;\" href=\"{$mybb->settings['bburl']}/thx.php?action=search&pid={$post['pid']}&amp;my_post_key={$mybb->post_code}\"><div id=\"counter{$post['pid']}\" class=\"neutral_thx\">".my_number_format($count)."</div></a>";}
				else if ($count >= 1){$count="<a style=\"background: none;border:none;\" href=\"{$mybb->settings['bburl']}/thx.php?action=search&pid={$post['pid']}&amp;my_post_key={$mybb->post_code}\"><div id=\"counter{$post['pid']}\" class=\"good_thx\"> ".my_number_format($count)." </div></a>";}
				else {$count="<a style=\"background: none;border:none;\" href=\"{$mybb->settings['bburl']}/thx.php?action=search&pid={$post['pid']}&amp;my_post_key={$mybb->post_code}\"><div id=\"counter{$post['pid']}\" class=\"bad_thx\"> ".my_number_format($count)." </div></a>";}											
			}
			else
			{
				if ($count == 0){$count="<a style=\"background: none;border:none;\" href=\"javascript:void(0);\"><div id=\"counter{$post['pid']}\" class=\"neutral_thx\">".my_number_format($count)."</div></a>";}
				else if ($count >= 1){$count="<a style=\"background: none;border:none;\" href=\"javascript:void(0);\"><div id=\"counter{$post['pid']}\" class=\"good_thx\"> ".my_number_format($count)." </div></a>";}
				else {$count="<a style=\"background: none;border:none;\" href=\"javascript:void(0);\"><div id=\"counter{$post['pid']}\" class=\"bad_thx\"> ".my_number_format($count)." </div></a>";}											
			}
			if($mybb->settings['thx_list_modal'] == 0)
			{
				$thx_list_style = "";
				if(empty($entries))
				{
					$thx_list_style = " style=\"display: none;\"";
				}
				eval("\$post['thx_list'] .= \"".$templates->get("thanks_postbit_list")."\";");
			}
			else
			{
				eval("\$post['thx_list'] .= \"".$templates->get("thanks_postbit_list_modal")."\";");
			}
		}
	}
	else
	{
		$count="<div id=\"counter{$post['pid']}\"></div>";
	}
	$post['thx_counter'] = $count;
	if($mybb->user['uid'] == $post['uid']){
		$post['thanks'] = "";
	}
 	if($mybb->user['uid'] != 0 && $mybb->user['uid'] != $post['uid'])
	{
		$ammount = (int)$mybb->user['thx_ammount'];
		$max_ammount = (int)$mybb->usergroup['thx_max_ammount'];		
		if($mybb->settings['thx_reputation'] == 3 || $mybb->settings['thx_reputation'] == 4){
			$post['button_rep'] = "";
		}
		if(!$b){
			// Verify if AJAX enabled for MyBB
			if($mybb->settings['use_xmlhttprequest'] == 1)
			{
				$post['thanks'] = "<a id=\"add_thx{$post['pid']}\" href=\"showthread.php?action=thank&amp;tid={$post['tid']}&amp;pid={$post['pid']}\">
			<div class=\"thx_buttons\" id=\"sp_{$post['pid']}\"><span class=\"gracias\"> {$lang->thx_button_add}</span></div></a>";
				if($mybb->settings['thx_del'] == 1)
				{
					$post['thanks'] .= "<a style=\"display: none;\" id=\"del_thx{$post['pid']}\" href=\"showthread.php?action=remove_thank&amp;tid={$post['tid']}&amp;pid={$post['pid']}\">					
				<div class=\"thx_buttons\" id=\"sp_{$post['pid']}\"><span class=\"egracias\"> {$lang->thx_button_del}</span></div></a>";
				}
			}
			else
			{
				$post['thanks'] = "<a id=\"a{$post['pid']}\" href=\"showthread.php?action=thank&amp;tid={$post['tid']}&amp;pid={$post['pid']}\">
			<div class=\"thx_buttons\" id=\"sp_{$post['pid']}\"><span class=\"gracias\"> {$lang->thx_button_add}</span></div></a>";
			}	
		}
		else if($mybb->settings['thx_del'] == 1)
		{
			// Verify if AJAX enabled for MyBB
			if($mybb->settings['use_xmlhttprequest'] == 1)
			{		
				$post['thanks'] = "<a id=\"del_thx{$post['pid']}\" href=\"showthread.php?action=remove_thank&amp;tid={$post['tid']}&amp;pid={$post['pid']}\">					
			<div class=\"thx_buttons\" id=\"sp_{$post['pid']}\"><span class=\"egracias\"> {$lang->thx_button_del}</span></div></a>";	 		
				$post['thanks'] .= "<a style=\"display: none;\" id=\"add_thx{$post['pid']}\" href=\"showthread.php?action=thank&amp;tid={$post['tid']}&amp;pid={$post['pid']}\">
			<div class=\"thx_buttons\" id=\"sp_{$post['pid']}\"><span class=\"gracias\"> {$lang->thx_button_add}</span></div></a>";		
			}
			else
			{
				$post['thanks'] = "<a id=\"a{$post['pid']}\" href=\"showthread.php?action=remove_thank&amp;tid={$post['tid']}&amp;pid={$post['pid']}\">
			<div class=\"thx_buttons\" id=\"sp_{$post['pid']}\"><span class=\"egracias\"> {$lang->thx_button_del}</span></div></a>";	 
			}			
		}		
		else
		{
			$post['thanks'] = "";
		}
		if($mybb->settings['thx_limit'] == 1)
		{
			if($ammount >= $max_ammount)
			{		
				$post['thanks'] = "<img src=\"images/thx/mal.png\" width=\"20\" height=\"20\" alt=\"{$lang->thx_exceed}\" />";
			}
		}			
	}

	$thx_pid = $post['pid'];
	if($mybb->settings['thx_count'] == "1")
	{
		$post['thx'] = my_number_format($post['thx']);
		$post['thxcount'] = my_number_format($post['thxcount']);		
		$protect = "&amp;my_post_key={$mybb->post_code}";	
		$post['thanks_count'] = $lang->sprintf($lang->thx_thank_count, $post['thx'], $post['uid'].$protect, $post['pid']);
		$post['thanked_count'] = $lang->sprintf($lang->thx_thanked_count, $post['thxcount'], $post['uid'].$protect, $post['pid']);
		$lang->thx_thank_count_mob = $lang->sprintf($lang->thx_thank_count_mob, $post['thx']);
		$lang->thx_thanked_count_mob = $lang->sprintf($lang->thx_thanked_count_mob, $post['thxcount']);
		
	}
	else if ($mybb->settings['thx_count'] == "0")
	{
		$post['thanks_count'] = "<div id=\"thx_thanked_{$post['pid']}\" style=\"display:none\"></div>";
		$post['thanked_count'] = "<div id=\"thx_thanks_{$post['pid']}\" style=\"display:none\"></div>";
	}
}
// Load memprofile data of thanks system...
function thx_memprofile()
{
	global $db, $mybb, $lang, $session, $memprofile, $cache, $theme, $templates, $ammount, $max_ammount;
	
	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}
	
    //$thx_forum_notgid = $mybb->settings['thx_hidesystem_notgid'];
	//if(is_member($thx_forum_notgid)){
    $thx_forum_notgid = explode(',', $mybb->settings['thx_hidesystem_notgid']);
	if(in_array($mybb->user['usergroup'], $thx_forum_notgid)){	
		return false;
	}
	$lang->load("thx", false, true);
	$memprofile['thx_info2'] = "";
	$protect = "&amp;my_post_key={$mybb->post_code}";	
	$memprofile['thx']= my_number_format($memprofile['thx']);
	$memprofile['thxcount'] = my_number_format($memprofile['thxcount']);		
	$lang->thx_thank_count = $lang->sprintf($lang->thx_thank_count, $memprofile['thx'], $memprofile['uid'].$protect, $memprofile['pid']);
	$lang->thx_thanked_count = $lang->sprintf($lang->thx_thanked_count, $memprofile['thxcount'], $memprofile['uid'].$protect, $memprofile['pid']);
	$memprofile['thx_info'] = "<br />" .$lang->thx_thank_count . "<br />" . $lang->thx_thanked_count;
	$lang->thx_thank_details = $lang->sprintf($lang->thx_thank_details, $memprofile['thxcount'], $memprofile['thxpost'],$memprofile['thx']);	
	$memprofile['thx_detailed_info'] = $lang->thx_thank_details;
	$ammount = (int)$mybb->user['thx_ammount'];
	$max_ammount = (int)$mybb->usergroup['thx_max_ammount'];		
	if($mybb->settings['thx_limit'] == 1 && $memprofile['uid'] == $mybb->user['uid'])
	{
		$memprofile['thx_info2'] = $lang->sprintf($lang->thx_thank_details_extra, $ammount, $max_ammount);
	}		
	
	eval("\$memprofile['thx_details'] .= \"".$templates->get("thanks_memprofile")."\";");				
}
// Adding new field to threads checkfields for send mail purposes...
function thx_send_mail_button_thread()
{
	global $mybb, $lang, $templates, $theme, $thx_send_mail;
	if($mybb->settings['thx_send_mail'] == 0)
	{
		return false;
	}
	$lang->load('thx', false, true);
	$thx_send_mail = " checked=\"checked\"";
	eval("\$thx_send_mail = \"".$templates->get("thanks_send_mail_thread")."\";");
}
// Update data if send mail available...
function thx_send_mail_button_thread_update()
{
	global $mybb, $db, $tid;
	if($mybb->settings['thx_send_mail'] == 0)
	{
		return false;
	}	
	$thx_mail = (int)$mybb->input['thx_send_mail'];
	$uid = (int)$mybb->user['uid'];
	if($thx_mail == 1)
	{
		$db->update_query('posts', array('thx_send_mail' => $thx_mail), "uid='{$uid}' AND tid='{$tid}'");
	}
}
// Send mail function for post thanked if enabled...
function thx_send_mail()
{
	global $mybb, $lang, $post, $pm;

	if($mybb->settings['thx_send_mail'] == 0 || $mybb->user['uid'] == 0){return false;}

	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}
	
	$lang->load("thx", false, true);	
	$user = get_user($post['uid']);
	$subject = htmlspecialchars_uni($post['subject']);
	if(isset($post['username']) && $post['username'] != $mybb->user['username'])
	{
		$thx_user['email'] = $mybb->user['email'];
		$thx_subject['email'] = $lang->sprintf($lang->thx_mail_subject, $subject, $mybb->settings['bbname']);
		$thx_message['email'] = $lang->sprintf($lang->thx_mail_message,$post['username'], $post['subject'], $mybb->settings['bbname'], $mybb->settings['bburl'], $post['pid']);
		my_mail($user['email'], $thx_subject['email'], $thx_message['email'], $thx_user['email']);
	}
}
// Sending the alert to db
function thx_recordAlertThanks()
{
	global $db, $mybb, $alert, $post;
	if(!$mybb->settings['thx_reputation'] == 2 || !$mybb->settings['thx_reputation'] == 4 || !$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}

	$uid = (int)$post['uid'];
	$tid = (int)$post['tid'];
	$pid = (int)$post['pid'];
	$subject = htmlspecialchars_uni($post['subject']);
	$fid = (int)$post['fid'];
	if(function_exists('myalerts_is_activated') && myalerts_is_activated())
	{
		myalerts_create_instances();
		$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();
		$alertType = $alertTypeManager->getByCode('thanks');
	
		if(isset($alertType) && $alertType->getEnabled())
		{
			//check if already alerted
			$query = $db->simple_select(
				'alerts',
				'id',
				'object_id = ' .$pid . ' AND uid = ' . $uid . ' AND unread = 1 AND alert_type_id = ' . $alertType->getId() . ''
			);

			if ($db->num_rows($query) == 0) 
			{
				$alert = new MybbStuff_MyAlerts_Entity_Alert($uid, $alertType, $pid, $mybb->user['uid']);
				$alert->setExtraDetails(
					array(
						'tid' 		=> $tid,
						'pid'		=> $pid,
						't_subject' => $subject,
						'fid'		=> $fid
					)); 
				MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
			}
		}
	}
	else
	{
		thx_send_mp();
	}
}
// Send an mp to user if he have not installed MyAlerts...
function thx_send_mp()
{
	global $lang, $mybb, $pm, $post;
	$lang->load('thx', false, true);
	$from = (int)$mybb->user['uid'];
	$uid = (int)$post['uid'];
	$tid = (int)$post['tid'];
	$pid = (int)$post['pid'];
	$subject = htmlspecialchars_uni($post['subject']);
	$fid = (int)$post['fid'];
	$sender = htmlspecialchars_uni($mybb->user['username']);
	$user = htmlspecialchars_uni($post['username']);
	$url = "[url={$mybb->asset_url}/showthread.php?tid={$tid}&pid={$pid}#pid{$pid}]{$subject}[/url]";	
	$body = $lang->sprintf($lang->thx_send_mp, $sender, $user, $url,$mybb->settings['bbname']);
	$subject_pm = $lang->sprintf($lang->thx_send_mp_subject,$subject);
   	// Enviar Mensaje Privado
   	require_once MYBB_ROOT."inc/datahandlers/pm.php";
   	$pmhandler = new PMDataHandler();

   	$pm = array(
   		'subject' => $subject_pm,
   		'message' => $body,
   		'icon' => 19,
   		'toid' => array($uid),
   		'fromid' => $from,
   		"do" => '',
   		"pmid" => '',
   	);

   	$pm['options'] = array(
		'signature' => '0',
		'savecopy' => '0',
		'disablesmilies' => '0',
		'readreceipt' => '0',
   	);

   	$pmhandler->set_data($pm);
  	$valid_pm = $pmhandler->validate_pm();

   	if($valid_pm)
   	{
   		$pmhandler->insert_pm();
   	}
}
// Alert formatter for my custom alerts.
function thanks_alerts_formatter_load()
{
	global $mybb;
	if($mybb->settings['thx_reputation'] == 2 || $mybb->settings['thx_reputation'] == 4)
	{
		class ThanksAlertFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
		{
			public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
			{
			$alertContent = $alert->getExtraDetails();
			$postLink = $this->buildShowLink($alert);
			
				return $this->lang->sprintf(
					$this->lang->thanks_alert,
					$outputAlert['from_user'],
					$alertContent['t_subject']
				);
			}
			public function init()
			{
				if (!$this->lang->thx) 
				{
					$this->lang->load('thx');
				}
			}
			public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
			{
				$alertContent = $alert->getExtraDetails();
				$postLink = $this->mybb->settings['bburl'] . '/' . get_post_link((int)$alertContent['pid'], (int)$alertContent['tid']).'#pid'.(int)$alertContent['pid'];              

				return $postLink;
			}
		}
	}
}	
// Load location for user...
function thx_wol_activity($user_activity)
{
	global $mybb, $user, $session;

	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}
	
	$split_loc = explode(".php", $user_activity['location']);
	if($split_loc[0] == $user['location'])
	{
		$filename = '';
	}
	else
	{
		$filename = my_substr($split_loc[0], -my_strpos(strrev($split_loc[0]), "/"));
	}
	
	if ($filename == "thx")
	{
		$user_activity['activity'] = "thx";
	}
	
	return $user_activity;
}
// Set location for user and then show it ...
function thx_friendly_wol_activity($plugin_array)
{
	global $mybb, $lang, $session;

	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}
	
	$lang->load('thx', false, true);
	
	if ($plugin_array['user_activity']['activity'] == "thx")
	{
		$plugin_array['location_name'] = $lang->sprintf($lang->thx_wol, "thx.php?thanks={$mybb->user['uid']}&amp;my_post_key={$mybb->post_code}", $lang->thx_title);
	}
	
	return $plugin_array;
}
function thx_clear(&$post)
{
	global $mybb,$attachcache,$templates, $lang, $fid;
	
	if($fid==''){$fid=$post['fid'];}
	$lang->load("thx", false, true);
	
	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}
	
	if($mybb->settings['thx_attach_enabled'] && is_array($attachcache[$post['pid']]) && $mybb->user['uid'] != $post['uid'])
	{
		if(!thx_code($post['mesage']))
		{
			$msg = $lang->thx_hide_text; 	
			eval("\$caja = \"".$templates->get("thanks_guests_tag",1,0)."\";");		
			$post['message'] = preg_replace("#\[attachment=(.*?)\]#is",$post['attachments'],$caja);               
		}
	}
}

function thx_newpoints_home(){
	global $mybb, $lang, $theme, $templates, $thx_newpoints;

	$lang->load("thx", false, true);
	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}
	// Agregando valores nuevos al newpoints:
	if($mybb->settings['thx_newpoints'] == 1)
	{	
		$points = (int)$mybb->usergroup['thx_newpoints_earn'];
		$earnpoints = (int)$mybb->usergroup['thx_newpoints_give'];
		$points = newpoints_format_points($points);			
		$earnpoints = newpoints_format_points($earnpoints);		
		$thx_newpoints = $lang->sprintf($lang->thx_newpoints,$points,$earnpoints);
	}	
}

// Click on thanks button with AJAX.
function do_action()
{
	global $mybb, $db, $lang, $theme, $templates, $count, $forum, $thread, $post, $attachcache, $parser, $pid,$tid,$ammount, $max_ammount, $charset;

	if(!$mybb->settings['thx_active'] || !empty($session->is_spider) || !$mybb->user['uid'])
	{
		return false;
	}

	if(($mybb->input['action'] != "thankyou"  &&  $mybb->input['action'] != "remove_thankyou" &&  $mybb->input['action'] != "thanks_list") || $mybb->request_method != "post")
	{
		return false;
	}

    $thx_forum_notgid = explode(',', $mybb->settings['thx_hidesystem_notgid']);
	if(in_array($mybb->user['usergroup'], $thx_forum_notgid) && THIS_SCRIPT == "showthread.php"){
		return false;
	}
	
	$lang->load("thx", false, true);	
	
	$ammount = (int)$mybb->user['thx_ammount'];
	$max_ammount = (int)$mybb->usergroup['thx_max_ammount'];	
	if($mybb->settings['thx_limit'] == 1)
	{	
		// if you get max thanks per day get an error...
		if($ammount >= $max_ammount){
			$error = $lang->sprintf($lang->thx_exceed, $max_ammount);
			xmlhttp_error($error);
			return false;
		}
	}

	// Fetch the post from the database.
	$post = get_post($mybb->get_input('pid', 1));

	// No result, die.
	if(!$post)
	{
		xmlhttp_error($lang->post_doesnt_exist);
		return false;		
	}

	$pid = (int)$mybb->input['pid'];
	$tid = (int)$mybb->input['tid'];
	
    if(!verify_post_check($mybb->input['my_post_key']))
	{
		xmlhttp_error($lang>thx_cant_thank);
		return false;
	}

	$query = $db->query("SELECT * FROM ".TABLE_PREFIX."thx
		WHERE adduid='".(int)$mybb->user['uid']."'
		ORDER BY time DESC LIMIT 1"
	);

	if ($mybb->settings['thx_antiflood'] > 1)
	{
		$thx_req = (int)$mybb->settings['thx_antiflood'];
		switch($thx_req)
		{
			case 1 : $thx_antiflood = 0;break;
			case 2 : $thx_antiflood = 14;break;
			case 3 : $thx_antiflood = 29;break;
			case 4 : $thx_antiflood = 44;break;
			case 5 : $thx_antiflood = 59;break;		
			default: $thx_antiflood = 29;
		}
		
		$antiflood['time'] = $mybb->user['thx_antiflood'];
		if(isset($antiflood['time']) && !empty($antiflood['time']))
		{
			$timer = $antiflood['time'];
			$timer_rest = $antiflood['time'] + $thx_antiflood;
			$timer_act = time();                  
			$timer_txt = $timer_rest - $timer_act;
			$timer_uid = (int)$antiflood['uid'];
			$timer_pid = (int)$antiflood['pid'];
			$thx_antiflood = $thx_antiflood + 1;
			$lang->thx_antiflood = $lang->sprintf($lang->thx_antiflood,$timer_txt);
			if($timer_act < $timer_rest)
			{
				xmlhttp_error($lang->thx_antiflood);
				return false;
			}
		}
	}

	if ($mybb->input['action'] == "thankyou")
	{
		do_thank($pid);
		if($mybb->settings['thx_counter'] == "1")
		{
			$count = (int)$post['pthx'] + 1;
		}
		if($mybb->settings['thx_send_mail'] == "1" && $post['thx_send_mail'] == "1")
		{
			thx_send_mail();
		}
	}
	else if($mybb->settings['thx_del'] == "1")
	{
		del_thank($pid);
		if($mybb->settings['thx_counter'] == "1")
		{
			$count = (int)$post['pthx'] - 1;
		}
	}	

	if($mybb->settings['thx_count'] == 1)
	{
		$query = $db->query("
			SELECT uid, thxcount FROM ".TABLE_PREFIX."users
			WHERE uid='".(int)$post['uid']."'
			LIMIT 1"
		);
		while($thx = $db->fetch_array($query))
		{
			$thxcount = my_number_format($thx['thxcount']);
		}	
	}
	else if($mybb->settings['thx_count'] == "0")
	{
		$thxcount = 0;
	}

	$del = (int)$mybb->settings['thx_del'];
	
    if($mybb->settings['thx_hidesystem'] == 1)
	{
		require_once MYBB_ROOT."inc/class_parser.php";
		$parser = new postParser;

		$parser_options = array(
			"allow_html" => 0,
			"allow_mycode" => 1,
			"allow_smilies" => 1,
			"allow_imgcode" => 1,
			"allow_videocode" => 1,
			"me_username" => $post['username'],
			"filter_badwords" => 1
		);

		if($post['smilieoff'] == 1)
		{
			$parser_options['allow_smilies'] = 0;
		}
		if($mybb->user['showimages'] != 1 && $mybb->user['uid'] != 0 || $mybb->settings['guestimages'] != 1 && $mybb->user['uid'] == 0)
		{
		$parser_options['allow_imgcode'] = 0;
		}
		if($mybb->user['showvideos'] != 1 && $mybb->user['uid'] != 0 || $mybb->settings['guestvideos'] != 1 && $mybb->user['uid'] == 0)
		{
			$parser_options['allow_videocode'] = 0;
		}

		$post['message'] = $parser->parse_message($post['message'], $parser_options);	

		if($mybb->settings['enableattachments'] != 0)
		{
			$query = $db->simple_select("attachments", "*", "pid='{$post['pid']}'");
			while($attachment = $db->fetch_array($query))
			{
				$attachcache[$attachment['pid']][$attachment['aid']] = $attachment;
			}
				require_once MYBB_ROOT."inc/functions_post.php";
				get_post_attachments($post['pid'], $post);
		}
		
		$post =  $post['message'];	
	}

	$nonead = 0;
	$list = build_thank($pid, $nonead);

	
	if($mybb->input['action'] == "thankyou")
	{
		$buttons = "<span class=\"egracias\">{$lang->thx_button_del}</span>";
	}
	else if($mybb->settings['thx_del'] == "1")
	{
		$buttons = "<span class=\"gracias\">{$lang->thx_button_add}</span>";
	}	
	else
	{
		$buttons = "";		

	}
		
    if($mybb->settings['thx_hidesystem'] == 1)
	{
		$thankyou = array(
			'list' => $list,
			'thxcount' => $thxcount,
			'count' => $count,
			'buttons' => $buttons,
			'post' => $post,
			'del' => $del
		);
	}
	else
	{
		$thankyou = array(
			'list' => $list,
			'thxcount' => $thxcount,
			'count' => $count,
			'buttons' => $buttons,
			'del' => $del
		);		
	}
	header("Content-type: application/json; charset={$charset}");
	//echo json_encode($thankyou, JSON_UNESCAPED_UNICODE);
	echo json_encode($thankyou);
	exit;	
}

// Click on thanks button without AJAX.
function direct_action()
{
	global $mybb, $lang, $tid, $pid;

	if(!$mybb->settings['thx_active'] || !empty($session->is_spider)  || !$mybb->user['uid'])
	{
		return false;
	}

   $thx_forum_notgid = explode(',', $mybb->settings['thx_hidesystem_notgid']);
	if(in_array($mybb->user['usergroup'], $thx_forum_notgid) && THIS_SCRIPT == "showthread.php"){	
		return false;
	}
	
	if($mybb->input['action'] != "thank"  &&  $mybb->input['action'] != "remove_thank")
	{
		return false;
	}
	
	$lang->load("thx", false, true);	
	$ammount = (int)$mybb->user['thx_ammount'];
	$max_ammount = (int)$mybb->usergroup['thx_max_ammount'];	
	if($mybb->settings['thx_limit'] == 1)
	{	
		// if you get max thanks per day get an error...
		if($ammount >= $max_ammount)
		{
			$error = $lang->sprintf($lang->thx_exceed, $max_ammount);
			error($error);
			return false;
		}
	}
	
	$pid = (int)$mybb->input['pid'];

	if ($mybb->settings['thx_antiflood'] > 1)
	{
		$thx_req = (int)$mybb->settings['thx_antiflood'];
		switch($thx_req)
		{
			case 1 : $thx_antiflood = 0;break;
			case 2 : $thx_antiflood = 14;break;
			case 3 : $thx_antiflood = 29;break;
			case 4 : $thx_antiflood = 44;break;
			case 5 : $thx_antiflood = 59;break;		
			default: $thx_antiflood = 29;
		}
		
		$antiflood['time'] = $mybb->user['thx_antiflood'];
		if(isset($antiflood['time']) && !empty($antiflood['time']))
		{
			$timer = $antiflood['time'];
			$timer_rest = $antiflood['time'] + $thx_antiflood;
			$timer_act = time();                  
			$timer_txt = $timer_rest - $timer_act;
			$timer_uid = (int)$antiflood['uid'];
			$timer_pid = (int)$antiflood['pid'];
			$thx_antiflood = $thx_antiflood + 1;
			$lang->thx_antiflood = $lang->sprintf($lang->thx_antiflood,$timer_txt);
			if($timer_act < $timer_rest)
			{
				error($lang->thx_antiflood);
				return false;
			}
		}
	}
	
	if($mybb->input['action'] == "thank" )
	{
		do_thank($pid);
		if($mybb->settings['thx_send_mail'] == 1 )
		{
			if($post['thx_send_mail'] == 1)
			{
				thx_send_mail();
			}
		}		
	}
	else if($mybb->settings['thx_del'] == "1")
	{
		del_thank($pid);
	}	
	redirect(get_post_link($pid, $tid)."#pid{$pid}");
}

// Review who thanked on the post and load info if available, with more consume of querys....
function build_thank(&$pid, &$is_thx)
{
	global $db, $mybb, $lang, $thx_cache;

	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}

	$pid = (int)$pid; 
	$uid = (int)$mybb->user['uid'];
	$is_thx = 0;
	if ($pid == 0 || $pid == '')
	{
		$pid == (int)$mybb->input['pid'];
	}

	$lang->load("thx", false, true);
	
	if($mybb->settings['thx_list_modal'] == 1)
	{
		$query = $db->simple_select("thx", "*", "pid='{$pid}' AND adduid='{$uid}'", array("limit"=>1));	
		if($db->num_rows($query) == 1)
		{
			$is_thx = 1;	
			return true;
			exit;
		}
		$db->free_result($query);
		return false;
		exit;
	}
	else if($mybb->settings['thx_list_modal'] == 0)
	{
		$query=$db->query("SELECT th.txid, th.uid, th.adduid, th.pid, th.time, u.username, u.usergroup, u.displaygroup, u.avatar
			FROM ".TABLE_PREFIX."thx th
			JOIN ".TABLE_PREFIX."users u
			ON th.adduid=u.uid
			WHERE th.pid='{$pid}'
			ORDER BY th.time DESC"
		);

		$thx_total = "";

		while($thx = $db->fetch_array($query))
		{
			if($thx['adduid'] == $mybb->user['uid'])
			{
				$is_thx++;
			}
			$date = my_date('relative', $thx['time']);
			if(!isset($thx_cache['showname'][$thx['username']]))
			{
				$url = get_profile_link($thx['adduid']);
				$name = format_name($thx['username'], $thx['usergroup'], $thx['displaygroup']);
				$avatar = htmlspecialchars_uni($thx['avatar']);
				$alt = $lang->thx_title;
				if($mybb->settings['thx_avatar_modal'] == 1)
				{
					if($avatar != '')
					{
						$thx_cache['showname'][$thx['username']] = "<a href=\"{$url}\" title=\"{$alt}\"><img src=\"$avatar\" class=\"thx_list_avatar\"> {$name}</a>,&nbsp;";
					}
					else
					{
						$thx_cache['showname'][$thx['username']] = "<a href=\"{$url}\" title=\"{$alt}\"><img src=\"images/default_avatar.png\" class=\"thx_list_avatar\">{$name}</a>,&nbsp;";
					}
				}
				else
				{
					$thx_cache['showname'][$thx['username']] = "<a href=\"{$url}\" title=\"{$alt}\">{$name}</a>,&nbsp;";
				}				
			}

			if($mybb->settings['thx_hidemode'])
			{
				$entries .= "<span title=\"{$date}\">".$thx_cache['showname'][$thx['username']]."</span>&nbsp;";
			}
			else
			{
				$entries .= $thx_cache['showname'][$thx['username']]." <span class=\"smalltext\">({$date})</span>&nbsp;";
			}
		}			
		$lang->thx_latest_entries = $lang->sprintf($lang->thx_latest_entries,$thx_total);		
		return $entries;
	}
}

// Add thanks to a post
function do_thank(&$pid)
{
	global $db, $mybb, $lang;
	
	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}

    $thx_forum_notgid = explode(',', $mybb->settings['thx_hidesystem_notgid']);
	if(in_array($mybb->user['usergroup'], $thx_forum_notgid) && THIS_SCRIPT == "showthread.php"){
		return false;
	}
	
	$pid = (int)$pid;
	$lang->load("thx", false, true);	

	$check_query = $db->simple_select("thx", "count(*) as c" ,"adduid='{$mybb->user['uid']}' AND pid='{$pid}'", array("limit"=>"1"));
			
	$tmp=$db->fetch_array($check_query);
	if($tmp['c'] != 0)
	{
		return false;
	}
		
	$check_query = $db->simple_select("posts", "uid", "pid='{$pid}'", array("limit"=>1));
	if($db->num_rows($check_query) == 1)
	{
		
		$tmp=$db->fetch_array($check_query);
		
		if((int)$tmp['uid'] == (int)$mybb->user['uid'])
		{
			return false;
		}		
			
		$database = array (
			"uid" => (int)$tmp['uid'],
			"adduid" => (int)$mybb->user['uid'],
			"pid" => (int)$pid,
			"time" => time()
		);
		$thx_rep = (int)$mybb->usergroup['thx_rep_points'];
		$thx_rep = $db->escape_string($thx_rep);
		$lang->thx_thankyou = $db->escape_string($lang->thx_thankyou);
		$mybb->user['uid'] = (int)$mybb->user['uid'];
		$time = time();
		// check if NewPoints is installed
		if($mybb->settings['thx_newpoints'] == 1)
		{
			global $cache;
			$plugins_cache = $cache->read("plugins");
			if(isset($plugins_cache['active']['newpoints']))
			{
				$newpoints_installed = true;
			}
			else
			{
				$newpoints_installed = false;
			}
			if($newpoints_installed == 1)
			{
				$earnpoints = (int)$mybb->usergroup['thx_newpoints_earn'];
				$points = (int)$mybb->usergroup['thx_newpoints_give'];
				$thx_newpoints = ", newpoints=newpoints+".$db->escape_string($points);
				$thx_newpointsearn = ", newpoints=newpoints+".$db->escape_string($earnpoints);
			}
			else
			{
				$thx_newpoints = "";
				$thx_newpointsearn = "";
			}			
		}
		else
		{
			$thx_newpoints = "";
			$thx_newpointsearn = "";
		}
		if($mybb->settings['thx_reputation'] == 1 || $mybb->settings['thx_reputation'] == 2)
		{
			$sq = array (
				"UPDATE ".TABLE_PREFIX."users SET thx_antiflood={$time}, thx_ammount=thx_ammount+1,thx=thx+1{$thx_newpointsearn} WHERE uid='{$mybb->user['uid']}' LIMIT 1",
				"UPDATE ".TABLE_PREFIX."users SET thxcount=thxcount+1{$thx_newpoints},thxpost=CASE( SELECT COUNT(*) FROM ".TABLE_PREFIX."thx WHERE pid='{$pid}' LIMIT 1) WHEN 0 THEN thxpost+1 ELSE thxpost END WHERE uid='{$database['uid']}' LIMIT 1",					
				"UPDATE ".TABLE_PREFIX."posts SET pthx=pthx+1 WHERE pid='{$pid}' LIMIT 1",
			);
		}
		else if($mybb->settings['thx_reputation'] == 3 || $mybb->settings['thx_reputation'] == 4)
		{
			$sq = array (
				"UPDATE ".TABLE_PREFIX."users SET thx_antiflood={$time}, thx_ammount=thx_ammount+1,thx=thx+1{$thx_newpointsearn} WHERE uid='{$mybb->user['uid']}' LIMIT 1",
				"UPDATE ".TABLE_PREFIX."users SET thxcount=thxcount+1{$thx_newpoints}, reputation=reputation+{$thx_rep},thxpost=CASE( SELECT COUNT(*) FROM ".TABLE_PREFIX."thx WHERE pid='{$pid}' LIMIT 1) WHEN 0 THEN thxpost+1 ELSE thxpost END WHERE uid='{$database['uid']}' LIMIT 1",					
				"UPDATE ".TABLE_PREFIX."posts SET pthx=pthx+1 WHERE pid='{$pid}' LIMIT 1",
				"INSERT INTO ".TABLE_PREFIX."reputation (uid, adduid, pid, reputation, dateline, comments) VALUES ('{$tmp['uid']}', '{$mybb->user['uid']}', '{$pid}', '{$thx_rep}', '{$time}', '{$lang->thx_thankyou}')"
			);
		}
		else
		{
			$sq = array (
				"UPDATE ".TABLE_PREFIX."users SET thx_antiflood={$time}, thx_ammount=thx_ammount+1,thx=thx+1{$thx_newpointsearn} WHERE uid='{$mybb->user['uid']}' LIMIT 1",
				"UPDATE ".TABLE_PREFIX."users SET thxcount=thxcount+1{$thx_newpoints}, thxpost=CASE( SELECT COUNT(*) FROM ".TABLE_PREFIX."thx WHERE pid='{$pid}' LIMIT 1) WHEN 0 THEN thxpost+1 ELSE thxpost END WHERE uid='{$database['uid']}' LIMIT 1",					
				"UPDATE ".TABLE_PREFIX."posts SET pthx=pthx+1 WHERE pid='{$pid}' LIMIT 1"
			);		
		}				
		
	    unset($tmp);
				  
		foreach($sq as $q)
		{
			$db->query($q);
		}
		$db->insert_query("thx", $database);
	}	
	if($mybb->settings['thx_reputation'] == 2 || $mybb->settings['thx_reputation'] == 4)
	{
		thx_recordAlertThanks();
	}	
}
// Renome thanks gived on a post...
function del_thank(&$pid)
{
	global $mybb, $db;

	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}

    $thx_forum_notgid = explode(',', $mybb->settings['thx_hidesystem_notgid']);
	if(in_array($mybb->user['usergroup'], $thx_forum_notgid) && THIS_SCRIPT == "showthread.php"){
		return false;
	}
	
	$pid = (int)$pid;
	if($mybb->settings['thx_del'] != "1")
	{
		return false;
	}

	$check_query = $db->simple_select("thx", "`uid`, `txid`" ,"adduid='{$mybb->user['uid']}' AND pid='{$pid}'", array("limit"=>"1"));		
	
	if($db->num_rows($check_query))
	{
		$data = $db->fetch_array($check_query);
		$uid = (int)$data['uid'];
		$thxid = (int)$data['txid'];
		unset($data);		
		$time = time();
		$thx_rep = (int)$mybb->usergroup['thx_rep_points'];
		$mybb->user['uid'] = (int)$mybb->user['uid'];
		// check if NewPoints is installed
		if($mybb->settings['thx_newpoints'] == 1)
		{
			global $cache;
			$plugins_cache = $cache->read("plugins");
			if(isset($plugins_cache['active']['newpoints']))
			{
				$newpoints_installed = true;
			}
			else
			{
				$newpoints_installed = false;
			}
			if($newpoints_installed == 1)
			{
				$earnpoints = (int)$mybb->usergroup['thx_newpoints_earn'];
				$points = (int)$mybb->usergroup['thx_newpoints_give'];
				$thx_newpoints = ", newpoints=newpoints-".$db->escape_string($points);
				$thx_newpointsearn = ", newpoints=newpoints-".$db->escape_string($earnpoints);
			}
			else
			{
				$thx_newpoints = "";
				$thx_newpointsearn = "";
			}			
		}
		else
		{
			$thx_newpoints = "";
			$thx_newpointsearn = "";
		}		
		if($mybb->settings['thx_reputation'] == 1)
		{
			$sq = array (
				"UPDATE ".TABLE_PREFIX."users SET thx_antiflood={$time}, thx=thx-1{$thx_newpointsearn} WHERE uid='{$mybb->user['uid']}' LIMIT 1",
				"UPDATE ".TABLE_PREFIX."users SET thxcount=thxcount-1{$thx_newpoints}, thxpost=CASE(SELECT COUNT(*) FROM ".TABLE_PREFIX."thx WHERE pid='{$pid}' LIMIT 1) WHEN 0 THEN thxpost-1 ELSE thxpost END WHERE uid='{$uid}' LIMIT 1",
				"UPDATE ".TABLE_PREFIX."posts SET pthx=pthx-1 WHERE pid='{$pid}' LIMIT 1"
			);
			$db->delete_query("thx", "txid='{$thxid}'", "1");
	    }
		else if($mybb->settings['thx_reputation'] == 2)
		{
			$sq = array (
				"UPDATE ".TABLE_PREFIX."users SET thx_antiflood={$time}, thx=thx-1{$thx_newpointsearn} WHERE uid='{$mybb->user['uid']}' LIMIT 1",
				"UPDATE ".TABLE_PREFIX."users SET thxcount=thxcount-1{$thx_newpoints}, thxpost=CASE(SELECT COUNT(*) FROM ".TABLE_PREFIX."thx WHERE pid='{$pid}' LIMIT 1) WHEN 0 THEN thxpost-1 ELSE thxpost END WHERE uid='{$uid}' LIMIT 1",
				"UPDATE ".TABLE_PREFIX."posts SET pthx=pthx-1 WHERE pid='{$pid}' LIMIT 1",
				"DELETE FROM ".TABLE_PREFIX."alerts WHERE from_user_id={$mybb->user['uid']} AND object_id='{$pid}' AND unread=1 LIMIT 1"			
			);
			$db->delete_query("thx", "txid='{$thxid}'", "1");		
	    }
		else if($mybb->settings['thx_reputation'] == 3)
		{
			$sq = array (
				"UPDATE ".TABLE_PREFIX."users SET thx_antiflood={$time}, thx=thx-1{$thx_newpointsearn} WHERE uid='{$mybb->user['uid']}' LIMIT 1",
				"UPDATE ".TABLE_PREFIX."users SET thxcount=thxcount-1{$thx_newpoints}, reputation=reputation-{$thx_rep}, thxpost=CASE(SELECT COUNT(*) FROM ".TABLE_PREFIX."thx WHERE pid='{$pid}' LIMIT 1) WHEN 0 THEN thxpost-1 ELSE thxpost END WHERE uid='{$uid}' LIMIT 1",
				"UPDATE ".TABLE_PREFIX."posts SET pthx=pthx-1 WHERE pid='{$pid}' LIMIT 1"
			);
			$db->delete_query("reputation", "adduid='{$mybb->user['uid']}' AND pid='{$pid}'");
			$db->delete_query("thx", "txid='{$thxid}'", "1");		
	    }
		else if($mybb->settings['thx_reputation'] == 4)
		{
			$sq = array (
				"UPDATE ".TABLE_PREFIX."users SET thx_antiflood={$time}, thx=thx-1{$thx_newpointsearn} WHERE uid='{$mybb->user['uid']}' LIMIT 1",
				"UPDATE ".TABLE_PREFIX."users SET thxcount=thxcount-1{$thx_newpoints}, reputation=reputation-{$thx_rep}, thxpost=CASE(SELECT COUNT(*) FROM ".TABLE_PREFIX."thx WHERE pid='{$pid}' LIMIT 1) WHEN 0 THEN thxpost-1 ELSE thxpost END WHERE uid='{$uid}' LIMIT 1",
				"UPDATE ".TABLE_PREFIX."posts SET pthx=pthx-1 WHERE pid='{$pid}' LIMIT 1",
				"DELETE FROM ".TABLE_PREFIX."alerts WHERE from_user_id={$mybb->user['uid']} AND object_id='{$pid}' AND unread=1 LIMIT 1"			
			);
			$db->delete_query("reputation", "adduid='{$mybb->user['uid']}' AND pid='{$pid}'");
			$db->delete_query("thx", "txid='{$thxid}'", "1");		
	    }
		else
		{
			$sq = array (
				"UPDATE ".TABLE_PREFIX."users SET thx_antiflood={$time}, thx=thx-1{$thx_newpointsearn} WHERE uid='{$mybb->user['uid']}' LIMIT 1",
				"UPDATE ".TABLE_PREFIX."users SET thxcount=thxcount-1{$thx_newpoints}, thxpost=CASE(SELECT COUNT(*) FROM ".TABLE_PREFIX."thx WHERE pid='{$pid}' LIMIT 1) WHEN 0 THEN thxpost-1 ELSE thxpost END WHERE uid='{$uid}' LIMIT 1",
				"UPDATE ".TABLE_PREFIX."posts SET pthx=pthx-1 WHERE pid='{$pid}' LIMIT 1"
			);
			$db->delete_query("thx", "txid='{$thxid}'", "1");
		}
		
		foreach($sq as $q)
		{
			$db->query($q);
		}
	}
}
// Removing thanks on post by post deletion...
function deletepost_edit($pid)
{
	global $mybb, $db;

	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}
		
	$pid = (int)$pid;
	$q = $db->simple_select("thx", "uid, adduid", "pid='{$pid}'");
	
	$postnum = $db->num_rows($q);
	if($postnum <= 0)
	{
		return false;
	}
	
	$adduids = array();
	
	while($r = $db->fetch_array($q))
	{
		$uid = (int)$r['uid'];
		$adduids[] = (int)$r['adduid'];
	}
	
	$adduids = implode(", ", $adduids);
	
	$sq = array();
	$sq[] = "UPDATE ".TABLE_PREFIX."users SET thxcount=thxcount-1, thxpost=thxpost-1 WHERE uid='{$uid}'";
	$sq[] = "UPDATE ".TABLE_PREFIX."users SET thx=thx-1 WHERE uid IN ({$adduids})";
	
	foreach($sq as $q)
	{
		$db->query($q);
	}
	
	$db->delete_query("thx", "pid={$pid}", $postnum);	
}
// Run action to recount_thanks...
function thx_admin_action(&$action)
{
	global $mybb;

	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}
	
	$action['recount_thanks'] = array ('active'=>'recount_thanks');
}
// Load new option on admin cp maintenance, for thanks recount...
function thx_admin_menu(&$sub_menu)
{
    global $mybb, $db, $lang;

	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}
	
	$lang->load("thx", false, true);	
	
	$sub_menu['45'] = array	(
		'id'	=> 'recount_thanks',
		'title'	=> $db->escape_string($lang->thx_recount),
		'link'	=> 'index.php?module=tools/recount_thanks'
	);
}
// Set admin permissions for recount thanks, who can do this task ?
function thx_admin_permissions(&$admin_permissions)
{
    global $mybb, $db,$lang;

	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}
	
	$lang->load("thx", false, true);	
	
	$admin_permissions['recount_thanks'] = $db->escape_string($lang->thx_can_recount);
}
// Load recount thanks tool on maintenance admin cp...
function thx_admin()
{
	global $mybb, $page, $db, $lang;

	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}
	
	require_once MYBB_ROOT.'inc/functions_rebuild.php';
	if($page->active_action != 'recount_thanks')
	{
		return false;
	}

	$lang->load("thx", false, true);	
	
	if($mybb->request_method == "post")
	{
		if(!isset($mybb->input['page']) || (int)$mybb->input['page'] < 1)
		{
			$mybb->input['page'] = 1;
		}
		if(isset($mybb->input['do_recountthanks']))
		{
			if(!(int)$mybb->input['thx_chunk_size'])
			{
				$mybb->input['thx_chunk_size'] = 500;
			}

			do_recount();
		}
		else if(isset($mybb->input['do_recountposts']))
		{
			if(!(int)$mybb->input['post_chunk_size'])
			{
				$mybb->input['post_chunk_size'] = 500;
			}

			do_recount_post();
		}
	}

	$page->add_breadcrumb_item($db->escape_string($lang->thx_recount), "index.php?module=tools/recount_thanks");
	$page->output_header($db->escape_string($lang->thx_recount));

	$sub_tabs['thankyoulike_recount'] = array(
		'title'			=> $db->escape_string($lang->thx_recount_do),
		'link'			=> "index.php?module=tools/recount_thanks",
		'description'	=> $db->escape_string($lang->thx_upgrade_do)
	);

	$page->output_nav_tabs($sub_tabs, 'thankyoulike_recount');

	$form = new Form("index.php?module=tools/recount_thanks", "post");

	$form_container = new FormContainer($db->escape_string($lang->thx_recount));
	$form_container->output_row_header($db->escape_string($lang->thx_recount_task_desc));
	$form_container->output_row_header($db->escape_string($lang->thx_recount_send), array('width' => 50));
	$form_container->output_row_header("&nbsp;");

	$form_container->output_cell("<label>".$db->escape_string($lang->thx_recount_update)."</label>
	<div class=\"description\">".$db->escape_string($lang->thx_recount_update_desc)."</div>");
	$form_container->output_cell($form->generate_text_box("thx_chunk_size", 100, array('style' => 'width: 150px;')));
	$form_container->output_cell($form->generate_submit_button($db->escape_string($lang->thx_recount_update_button), array("name" => "do_recountthanks")));
	$form_container->construct_row();

	$form_container->output_cell("<label>".$db->escape_string($lang->thx_counter_update)."</label>
	<div class=\"description\">".$db->escape_string($lang->thx_counter_update_desc).".</div>");
	$form_container->output_cell($form->generate_text_box("post_chunk_size", 500, array('style' => 'width: 150px;')));
	$form_container->output_cell($form->generate_submit_button($db->escape_string($lang->thx_recount_update_button), array("name" => "do_recountposts")));
	$form_container->construct_row();

	$form_container->end();

	$form->end();

	$page->output_footer();

	exit;
}
// Recount user thanks ammount...
function do_recount()
{
	global $db, $mybb, $lang;

	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}
	
	$lang->load("thx", false, true);	
	$cur_page = (int)$mybb->input['page'];
	$per_page = (int)$mybb->input['thx_chunk_size'];
	$start = ($cur_page-1) * $per_page;
	$end = $start + $per_page;

	if ($cur_page == 1)
	{
		$db->write_query("UPDATE ".TABLE_PREFIX."users SET thx='0', thxcount='0'");
		$db->write_query("UPDATE ".TABLE_PREFIX."posts SET pthx='0'");
	}

	$query = $db->simple_select("thx", "COUNT(txid) AS thx_count");
	$thx_count = $db->fetch_field($query, 'thx_count');

	$query = $db->query("SELECT uid, adduid, pid
		FROM ".TABLE_PREFIX."thx
		ORDER BY time ASC
		LIMIT {$start}, {$per_page}"
	);

	$post_thx = array();
	$user_thx = array();
	$user_thx_to = array();

	while($thx = $db->fetch_array($query))
	{
		if($post_thx[$thx['pid']])
		{
			$post_thx[$thx['pid']]++;
		}
		else
		{
			$post_thx[$thx['pid']] = 1;
		}
		if($user_thx[$thx['adduid']])
		{
			$user_thx[$thx['adduid']]++;
		}
		else
		{
			$user_thx[$thx['adduid']] = 1;
		}
		if($user_thx_to[$thx['uid']])
		{
			$user_thx_to[$thx['uid']]++;
		}
		else
		{
			$user_thx_to[$thx['uid']] = 1;
		}
	}

	if(is_array($post_thx))
	{
		foreach($post_thx as $pid => $change)
		{
				$db->write_query("UPDATE ".TABLE_PREFIX."posts SET pthx=pthx+$change WHERE pid='$pid'");
		}
	}
	if(is_array($user_thx))
	{
		foreach($user_thx as $adduid => $change)
		{
			$db->write_query("UPDATE ".TABLE_PREFIX."users SET thx=thx+$change WHERE uid='$adduid'");
		}
	}
	if(is_array($user_thx_to))
	{
		foreach($user_thx_to as $uid => $change)
		{
			$db->write_query("UPDATE ".TABLE_PREFIX."users SET thxcount=thxcount+$change WHERE uid='$uid'");
		}
	}
	my_check_proceed($thx_count, $end, $cur_page+1, $per_page, "thx_chunk_size", "do_recountthanks", $db->escape_string($lang->thx_update_psuccess));
}
// Recount post thanks information...
function do_recount_post()
{
	global $db, $mybb, $lang;

	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}	

	$cur_page = (int)$mybb->input['page'];
	$per_page = (int)$mybb->input['post_chunk_size'];
	$start = ($cur_page-1) * $per_page;
	$end = $start + $per_page;
	$lang->load("thx", false, true);
	
	if ($cur_page == 1)
	{
		$db->write_query("UPDATE ".TABLE_PREFIX."users SET thxpost='0'");
	}

	$query = $db->simple_select("thx", "COUNT(distinct pid) AS post_count");
	$post_count = $db->fetch_field($query, 'post_count');

	$query = $db->query("SELECT uid, pid
		FROM ".TABLE_PREFIX."thx
		ORDER BY pid ASC
		LIMIT {$start}, {$per_page}"
	);

	while($thx = $db->fetch_array($query))
	{
		$db->write_query("UPDATE ".TABLE_PREFIX."users SET thxpost=thxpost+1 WHERE uid='{$thx['uid']}'");

	}
	my_check_proceed($post_count, $end, $cur_page+1, $per_page, "post_chunk_size", "do_recountposts", $db->escape_string($lang->thx_update_tsuccess));
}
// Recount of thanks page...
function my_check_proceed($current, $finish, $next_page, $per_page, $name_chunk, $name_submit, $message)
{
	global $mybb, $db, $page, $lang;

	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}
	
	$lang->load("thx", false, true);	

	if($finish >= $current)
	{
		flash_message($message, 'success');
		admin_redirect("index.php?module=tools/recount_thanks");
	}
	else
	{
		$page->output_header();

		$form = new Form("index.php?module=tools/recount_thanks", 'post');
        $total = $current - $finish;
		echo $form->generate_hidden_field("page", $next_page);
		echo $form->generate_hidden_field($name_chunk, $per_page);
		echo $form->generate_hidden_field($name_submit, "Actualizar");
		echo "<div class=\"confirm_action\">\n";
		echo $db->escape_string($lang->thx_confirm_next);
		echo "<br />\n";
		echo "<br />\n";
		echo "<script type=\"text/javascript\">$(function() { var button = $(\"#submit_button\"); if(button.length > 0) { button.val(\"Loading data...\"); button.attr(\"disabled\", true); button.css(\"color\", \"#aaa\"); button.css(\"borderColor\", \"#aaa\"); document.forms[0].submit(); }})</script>";
		echo "<p class=\"buttons\">\n";
		echo $form->generate_submit_button($db->escape_string($lang->thx_confirm_button), array('class' => 'button_yes', 'id' => 'submit_button'));
		echo "</p>\n";
		echo "<div style=\"float: right; color: #424242;\">".$db->escape_string($lang->thx_confirm_page)." {$next_page}\n";
		echo "<br />\n";
		echo $db->escape_string($lang->thx_confirm_elements)." {$total}</div>";
		echo "<br />\n";
	    echo "<br />\n";
		echo "</div>\n";		
		$form->end();
		$page->output_footer();
		exit;
	}
}
// If exist some option load it on new tab for thanks system on usergroups tabs...
function thx_edit_group_tabs(&$tabs)
{
	global $run_module, $tabs, $mybb, $lang;
	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}	
	$lang->load("thx", false, true);	
	if($mybb->settings['thx_limit'] == 1 || $mybb->settings['thx_reputation'] == 3 || $mybb->settings['thx_reputation'] == 4)
	{
		$tabs['thanks'] = $lang->thx_title;
	}
}
// Load form to set values for reputation poins earned on thanks and max ammount per day...
function thx_edit_group()
{
	global $run_module, $form_container, $form, $table, $mybb, $lang;

	$lang->load("thx", false, true);	
	
	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}		
	if($mybb->settings['thx_limit'] == 1 || $mybb->settings['thx_reputation'] == 3 || $mybb->settings['thx_reputation'] == 4 || $mybb->settings['thx_newpoints'] == 1)
	{	
		echo "<div id=\"tab_thanks\">";		
			$form_container = new FormContainer($lang->thx_admin_thx_group);
			$thx_options = array();
			if($mybb->settings['thx_limit'] == 1)
			{
				$thx_options[] = $lang->thx_admin_thx_group_opt1 . $form->generate_numeric_field('thx_max_ammount', $mybb->input['thx_max_ammount'], array('id' => 'max_thx_ammount', 'class' => 'field50'));
			}
			if($mybb->settings['thx_reputation'] == 3 || $mybb->settings['thx_reputation'] == 4)
			{
				$thx_options[] = $lang->thx_admin_thx_group_opt2 . $form->generate_numeric_field('thx_rep_points', $mybb->input['thx_rep_points'], array('id' => 'rep_thx_points', 'class' => 'field50'));		
			}
			if($mybb->settings['thx_newpoints'] == 1)
			{
				$thx_options[] = $lang->thx_admin_thx_group_opt3 . $form->generate_numeric_field('thx_newpoints_earn', $mybb->input['thx_newpoints_earn'], array('id' => 'earn_thx_newpoints', 'class' => 'field50'));		
				$thx_options[] = $lang->thx_admin_thx_group_opt4 . $form->generate_numeric_field('thx_newpoints_give', $mybb->input['thx_newpoints_give'], array('id' => 'give_thx_newpoints', 'class' => 'field50'));		
			}
			$form_container->output_row($lang->thx_system_dnt, '', '<div class="group_settings_bit">'.implode('</div><div class="group_settings_bit">', $thx_options).'</div>');
			$form_container->end();
		echo "</div>";
	}		
	else
	{
		return false;
	}
}

function thx_promotion_formcontainer_output_row(&$args)
{
	global $run_module, $form_container, $mybb, $db, $lang, $form, $options, $options_type, $promotion;

	if(!($run_module == 'user' && !empty($form_container->_title) && $mybb->get_input('module') == 'user-group_promotions' && in_array($mybb->get_input('action'), array('add', 'edit'))))
	{
		return;
	}
	
	$lang->load('thx', false,true);

	if($args['label_for'] == 'requirements')
	{
		$options['thx'] = $lang->setting_thx_promotion;
		$args['content'] = $form->generate_select_box('requirements[]', $options, $mybb->input['requirements'], array('id' => 'requirements', 'multiple' => true, 'size' => 5));
	}

	if($args['label_for'] == 'timeregistered')
	{
		if($mybb->get_input('pid', 1) && !isset($mybb->input['thx']))
		{
			$thx = $promotion['the'];
			$thxtype = $promotion['thxtype'];
		}
		else
		{
			$thx = $mybb->get_input('thx');
			$thxtype = $mybb->get_input('thxtype');
		}

		$form_container->output_row($lang->setting_thx_promotion, $lang->setting_thx_promotion_desc, $form->generate_numeric_field('thx', (int)$thx, array('id' => 'thx'))." ".$form->generate_select_box("thxtype", $options_type, $thxtype, array('id' => 'thxtype')), 'thx');
	}
}

function thx_promotion_commit()
{
	global $db, $mybb, $pid, $update_promotion, $pid;

	is_array($update_promotion) or $update_promotion = array();

	$update_promotion['thx'] = $mybb->get_input('thx', 1);
	$update_promotion['thxtype'] = $db->escape_string($mybb->get_input('thxtype'));

	if($mybb->get_input('action') == 'add')
	{
		$db->update_query('promotions', $update_promotion, "pid='{$pid}'");
	}
}

function thx_promotion_task(&$args)
{
	if(in_array('thx', explode(',', $args['promotion']['requirements'])) && (int)$args['promotion']['thx'] >= 0 && !empty($args['promotion']['thxtype']))
	{
		$args['sql_where'] .= "{$args['and']}thxcount{$args['promotion']['thxtype']}'{$args['promotion']['thx']}'";
		$args['and'] = ' AND ';
	}
}

// Update values for reputation points on thanks and max thanks ammount per day...
function thx_edit_group_do()
{
	global $db, $updated_group, $mybb;

	if(!$mybb->settings['thx_active'] || !empty($session->is_spider))
	{
		return false;
	}
	
	if($mybb->settings['thx_limit'] == 1 || $mybb->settings['thx_reputation'] == 3 || $mybb->settings['thx_reputation'] == 4 || $mybb->settings['thx_newpoints'] == 1){
		if($mybb->input['gid'] != 1)
		{
			if($mybb->settings['thx_limit'] == 1)
			{
				$updated_group['thx_max_ammount'] = $db->escape_string((int)$mybb->input['thx_max_ammount']);
			}
			if($mybb->settings['thx_reputation'] == 3 || $mybb->settings['thx_reputation'] == 4)
			{
				$updated_group['thx_rep_points'] = $db->escape_string((int)$mybb->input['thx_rep_points']);	
			}
			if($mybb->settings['thx_newpoints'] == 1)
			{
				$updated_group['thx_newpoints_give'] = $db->escape_string((int)$mybb->input['thx_newpoints_give']);	
				$updated_group['thx_newpoints_earn'] = $db->escape_string((int)$mybb->input['thx_newpoints_earn']);	
			}
		}
	}
	else
	{
		return false;
	}	
}