<?php
/*
* MyBB: [AJAX] ChangUonDyU - Advanced Statistics
*
* File: changstats.php
*
* Authors: ChangUonDyU, Vintagedaddyo
*
* MyBB Version: 1.8
*
* Plugin Version: 1.0.2
*
*/

if (!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function changstats_info()
{
	global $lang;
	$lang->load("changstats");
	$lang->changstats_desc = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float:right;">' . '<input type="hidden" name="cmd" value="_s-xclick">' . '<input type="hidden" name="hosted_button_id" value="AZE6ZNZPBPVUL">' . '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">' . '<img alt="" border="0" src="https://www.paypalobjects.com/pl_PL/i/scr/pixel.gif" width="1" height="1">' . '</form>' . $lang->changstats_desc;
	return Array(
		'name' => $lang->changstats_name,
		'description' => $lang->changstats_desc,
		'website' => $lang->changstats_web,
		'author' => $lang->changstats_auth,
		'authorsite' => $lang->changstats_authsite,
		'version' => $lang->changstats_ver,
		'compatibility' => $lang->changstats_compat
	);
}

$plugins->add_hook("global_end", "changstats_maindisplay");
$plugins->add_hook("xmlhttp", "changstats_getdata");

function changstats_activate()
{
	global $settings, $mybb, $db, $lang;
	$lang->load("changstats");

	// /// Insert Setting Group //////

	$group = array(
		'name' => 'chang_stats',
		'title' => $lang->changstats_title_setting_group,
		'description' => $lang->changstats_description_setting_group,
	);
	$db->insert_query("settinggroups", $group);
	$gid = $db->insert_id();

	// Insert Settings

	$s[] = array(
		'name' => 'changstats_turn',
		'title' => $lang->changstats_title_setting_1,
		'description' => $lang->changstats_description_setting_1,
		'optionscode' => 'yesno',
		'value' => 1,
		'disporder' => 10,
		'gid' => intval($gid)
	);
	$s[] = array(
		'name' => 'changstats_result',
		'title' => $lang->changstats_title_setting_2,
		'description' => $lang->changstats_description_setting_2,
		'optionscode' => 'text',
		'value' => '10,20,30,40,50',
		'disporder' => 20,
		'gid' => intval($gid)
	);
	$s[] = array(
		'name' => 'changstats_refreshtime',
		'title' => $lang->changstats_title_setting_3,
		'description' => $lang->changstats_description_setting_3,
		'optionscode' => 'text',
		'value' => 20,
		'disporder' => 30,
		'gid' => intval($gid)
	);
	$s[] = array(
		'name' => 'changstats_disforguest',
		'title' => $lang->changstats_title_setting_4,
		'description' => $lang->changstats_description_setting_4,
		'optionscode' => 'yesno',
		'value' => 1,
		'disporder' => 35,
		'gid' => intval($gid)
	);
	$s[] = array(
		'name' => 'changstats_disbutton',
		'title' => $lang->changstats_title_setting_5,
		'description' => $lang->changstats_description_setting_5,
		'optionscode' => 'yesno',
		'value' => 1,
		'disporder' => 36,
		'gid' => intval($gid)
	);

	// // LATESTPOST SETTINGS /////

	$s[] = array(
		'name' => 'changstats_customtab',
		'title' => $lang->changstats_title_setting_6,
		'description' => $db->escape_string($lang->changstats_description_setting_6) ,
		'optionscode' => 'textarea',
		'value' => 'Order1 title|1,2,5
Order2 title|21,15,7
Order3 title|14',
		'disporder' => 40,
		'gid' => intval($gid)
	);
	$s[] = array(
		'name' => 'changstats_exclforum',
		'title' => $lang->changstats_title_setting_7,
		'description' => $lang->changstats_description_setting_7,
		'optionscode' => 'text',
		'value' => '',
		'disporder' => 45,
		'gid' => intval($gid)
	);
	/*
	$s[] = array(
	'name' 			=> 'changstats_showdate',
	'title' 		=> 'Show DateTime ?',
	'description' 	=> '',
	'optionscode' 	=> 'yesno',
	'value' 		=> 1,
	'disporder' 	=> 50,
	'gid' 			=> intval($gid)
	);
	$s[] = array(
	'name' 			=> 'changstats_showlastposter',
	'title' 		=> 'Show LastPoster ?',
	'description' 	=> '',
	'optionscode' 	=> 'yesno',
	'value' 		=> 1,
	'disporder' 	=> 60,
	'gid' 			=> intval($gid)
	);
	$s[] = array(
	'name' 			=> 'changstats_showreply',
	'title' 		=> 'Show Replies ?',
	'description' 	=> '',
	'optionscode' 	=> 'yesno',
	'value' 		=> 1,
	'disporder' 	=> 70,
	'gid' 			=> intval($gid)
	);
	$s[] = array(
	'name' 			=> 'changstats_showview',
	'title' 		=> 'Show Views ?',
	'description' 	=> '',
	'optionscode' 	=> 'yesno',
	'value' 		=> 1,
	'disporder' 	=> 80,
	'gid' 			=> intval($gid)
	);
	$s[] = array(
	'name' 			=> 'changstats_showforum',
	'title' 		=> 'Show Forum ?',
	'description' 	=> '',
	'optionscode' 	=> 'yesno',
	'value' 		=> 1,
	'disporder' 	=> 90,
	'gid' 			=> intval($gid)
	);
	*/
	$s[] = array(
		'name' => 'changstats_dateformat',
		'title' => $lang->changstats_title_setting_8,
		'description' => $lang->changstats_description_setting_8,
		'optionscode' => 'text',
		'value' => 'm-d, h:i A',
		'disporder' => 100,
		'gid' => intval($gid)
	);
	$s[] = array(
		'name' => 'changstats_trim_threadtitle',
		'title' => $lang->changstats_title_setting_9,
		'description' => $lang->changstats_description_setting_9,
		'optionscode' => 'text',
		'value' => '35',
		'disporder' => 110,
		'gid' => intval($gid)
	);
	$s[] = array(
		'name' => 'changstats_trim_forumtitle',
		'title' => $lang->changstats_title_setting_10,
		'description' => $lang->changstats_description_setting_10,
		'optionscode' => 'text',
		'value' => '21',
		'disporder' => 120,
		'gid' => intval($gid)
	);
	$s[] = array(
		'name' => 'changstats_trim_username',
		'title' => $lang->changstats_title_setting_11,
		'description' => $lang->changstats_description_setting_11,
		'optionscode' => 'text',
		'value' => '14',
		'disporder' => 130,
		'gid' => intval($gid)
	);

	// /// TOP SETTINGS /////

	$s[] = array(
		'name' => 'changstats_topcol_width',
		'title' => $lang->changstats_title_setting_12,
		'description' => $db->escape_string($lang->changstats_description_setting_12) ,
		'optionscode' => 'text',
		'value' => '150',
		'disporder' => 200,
		'gid' => intval($gid)
	);
	$s[] = array(
		'name' => 'changstats_top_trim_threadtitle',
		'title' => $lang->changstats_title_setting_13,
		'description' => $lang->changstats_description_setting_13,
		'optionscode' => 'text',
		'value' => '21',
		'disporder' => 210,
		'gid' => intval($gid)
	);
	$s[] = array(
		'name' => 'changstats_top_trim_forumtitle',
		'title' => $lang->changstats_title_setting_14,
		'description' => $lang->changstats_description_setting_14,
		'optionscode' => 'text',
		'value' => '21',
		'disporder' => 220,
		'gid' => intval($gid)
	);
	$s[] = array(
		'name' => 'changstats_top_trim_username',
		'title' => $lang->changstats_title_setting_15,
		'description' => $lang->changstats_description_setting_15,
		'optionscode' => 'text',
		'value' => '21',
		'disporder' => 230,
		'gid' => intval($gid)
	);
	$s[] = array(
		'name' => 'changstats_joindate',
		'title' => $lang->changstats_title_setting_16,
		'description' => $lang->changstats_description_setting_16,
		'optionscode' => 'text',
		'value' => 'm-d',
		'disporder' => 240,
		'gid' => intval($gid)
	);
	foreach($s as $ones)
	{
		$db->insert_query("settings", $ones);
	}

	rebuild_settings();

	// Create template

	$templates['changuondyu_stats_main'] = <<<EOT
	<form action="" name="getmenu">
<table class="tborder" cellpadding="\$theme[tablespace]" cellspacing="\$theme[borderwidth]" border="0" width="100%">
<tr>
<td class="thead" colspan="\$ordert3">
<span style="float: right;">
{\$lang->changstats_result}
<select name="choosekq" onchange='changstats_post();changstats_top_user();changstats_top_forum();'>
  \$choosekq
</select>
\$refreshbutton
</span>

<b>{\$mybb->settings['bbname']} {\$lang->changstats_stats}</b>
<span id="cprogress_post" style="display: none;" class="smalltext">&nbsp;{\$lang->changstats_loadpost}</span>
<span id="cprogress_top" style="display: none;" class="smalltext">&nbsp;{\$lang->changstats_loadtop}</span>

</td>
</tr>

<tr align="center">
<td class="tcat" id="ct0" nowrap="nowrap" style="padding: 3px; cursor: pointer;" onclick="cswitch(0);"><a href="javascript:cswitch(0);">{\$lang->changstats_allforum}</a></td>
\$chooselatestposts
<td class="tcat" nowrap="nowrap" width="{\$mybb->settings['changstats_topcol_width']}" align="left">
	<select name="choosetop_user" onchange='changstats_top_user();'>
		\$choosetop_user
	</select>
</td>
<td class="tcat" nowrap="nowrap" width="{\$mybb->settings['changstats_topcol_width']}" align="left">
	<select name="choosetop_forum" onchange='changstats_top_forum();'>
		\$choosetop_forum
	</select>
</td>
</tr>

<tr>
<td class="trow1" colspan="\$ordert2" valign="top">
<div id="chang_latestposts"></div>
</td>
<td class="trow1" valign="top">
	<div id="chang_top_user"></div>
</td>
<td class="trow1" valign="top">
	<div id="chang_top_forum"></div>
</td>
</tr>

</table>
</form>
{\$changstats_script}
EOT;
	$templates['changuondyu_stats_script'] = <<<EOT
<style>
.thead2 {
    background: #0066a2 url('images/thead.png') top left repeat-x;
    color: #ffffff;
    border-bottom: 1px solid #263c30;
    padding: 8px;
}
.thead2 a:link {
    color: #ffffff;
    text-decoration: none;
}
</style>
<script language="JavaScript" type="text/javascript">
var fcmenu;
var listtab = new Array();
listtab[0] = "allforum";
\$listtab

function cswitch(taborder)
{
fcmenu = listtab[taborder];
document.getElementById('ct'+taborder).className = 'thead2';
for (i = 0; i <= \$ordert; i++)
{
 if (i != taborder)
   {
    document.getElementById('ct'+i).className = 'tcat';
   }
}
changstats_post();
}

function hshowpost(request)
{
  if (request.readyState == 4 && request.status == 200)
	{
	document.getElementById('chang_latestposts').innerHTML = request.responseText;
	document.getElementById('cprogress_post').style.display="none";
	}
}
function hshowtop_user(request)
{
  if (request.readyState == 4 && request.status == 200)
	{
	document.getElementById('chang_top_user').innerHTML = request.responseText;
	document.getElementById('cprogress_top').style.display="none";
	}
}
function hshowtop_forum(request)
{
  if (request.readyState == 4 && request.status == 200)
	{
	document.getElementById('chang_top_forum').innerHTML = request.responseText;
	document.getElementById('cprogress_top').style.display="none";
	}
}


function changstats_post()
{
	document.getElementById('cprogress_post').style.display="inline";
	fcresult = document.getmenu.choosekq.value;
	new Ajax.Request('xmlhttp.php?do='+fcmenu+'&result='+fcresult, {method: 'GET', postBody: null, onComplete: function(request) { hshowpost(request); }});
}
function changstats_top_user()
{
	document.getElementById('cprogress_top').style.display="inline";
	fcmenu_top_user = document.getmenu.choosetop_user.value;
	fcresult = document.getmenu.choosekq.value;
	new Ajax.Request('xmlhttp.php?do='+fcmenu_top_user+'&result='+fcresult, {method: 'GET', postBody: null, onComplete: function(request) { hshowtop_user(request); }});
}
function changstats_top_forum()
{
	document.getElementById('cprogress_top').style.display="inline";
	fcmenu_top_forum = document.getmenu.choosetop_forum.value;
	fcresult = document.getmenu.choosekq.value;
	new Ajax.Request('xmlhttp.php?do='+fcmenu_top_forum+'&result='+fcresult, {method: 'GET', postBody: null, onComplete: function(request) { hshowtop_forum(request); }});
}

cswitch(0);
changstats_top_user();
changstats_top_forum();

\$autorefresh
</script>
EOT;
	$templates['changuondyu_stats_refreshbutton'] = <<<EOT
	<input type="button" class="button" value="{\$lang->changstats_refresh}" onclick="changstats_post();" />
EOT;
	$templates['changuondyu_stats_topuser'] = <<<EOT
	\$topposter
	\$newmember
	\$topthank
EOT;
	$templates['changuondyu_stats_topforum'] = <<<EOT
	\$mostviewthread
	\$hotthread
	\$mostpopularforum
EOT;
	$templates['changuondyu_latestpost'] = <<<EOT
	<table cellpadding="1" cellspacing="0" border="0" width="100%">
<tr>
<td class="smalltext" nowrap="nowrap">{\$lang->changstats_thread}</td>
<td class="smalltext" nowrap="nowrap">{\$lang->changstats_date}, {\$lang->changstats_time}&nbsp;</td>
<td class="smalltext" nowrap="nowrap">{\$lang->changstats_postby}&nbsp;</td>
<td class="smalltext" nowrap="nowrap">{\$lang->changstats_reply}&nbsp;</td>
<td class="smalltext" nowrap="nowrap">{\$lang->changstats_views}&nbsp;</td>
<td class="smalltext" nowrap="nowrap">{\$lang->changstats_forum}</td>
</tr>
\$changtop_lastpost_bit
</table>
EOT;
	$templates['changuondyu_latestpost_bit'] = <<<EOT
<tr>
<td width="100%" nowrap="nowrap"><span class="smalltext"><a href="showthread.php?tid=\$latestpost[tid]&action=lastpost" title="\$latestpost[fulltitle]">\$latestpost[subject]</span></td>
<td nowrap="nowrap"><span class="smalltext"><if condition="\$pstatus=='old'"><font color="#C0C0C0"></if>\$latestpost[lastpost]&nbsp;<if condition="\$pstatus=='old'"></font></if>&nbsp;</span></td>
<td  nowrap="nowrap" title="\$latestpost[fulllastposter]"><span class="smalltext"><a href="member.php?action=profile&uid=\$latestpost[lastposteruid]">\$latestpost[lastposter]</a>&nbsp;</span></td>
<td nowrap="nowrap" align="right"><span class="smalltext">\$latestpost[replies]&nbsp;</span></td>
<td nowrap="nowrap" align="right"><span class="smalltext">\$latestpost[views]&nbsp;</span></td>
<td nowrap="nowrap" title="\$latestpost[forumnamefull]"><span class="smalltext"><a href="forumdisplay.php?fid=\$latestpost[fid]">\$latestpost[forumname]</a></span></td>
</tr>
EOT;
	$templates['changuondyu_top_bit'] = <<<EOT
	<tr>
<td nowrap="nowrap" title="\$title"><span class="smalltext">\$colum1</span></td>
<td nowrap="nowrap" align="right"><span class="smalltext">\$colum2</span></td>
</tr>
EOT;
	$templates['changuondyu_top_head'] = <<<EOT
	<table cellpadding="1" cellspacing="0" border="0" width="100%">
	<tr><td class="smalltext" align="left">\$h1</td><td class="smalltext" align="right">\$h2</td></tr>
			\$chang_top_element
		</table>
EOT;
	$templates['changuondyu_chooselatestposts'] = <<<EOT
	<td class="thead" nowrap="nowrap" id="ct{\$ordert}" style="padding: 3px; cursor: pointer;" onclick="cswitch(\$ordert);">
	<a href="javascript:cswitch(\$ordert);">\$menuname</a>
	</td>
EOT;
	foreach($templates as $title => $template)
	{
		$insert_template = array(
			'title' => $title,
			'template' => $db->escape_string($template) ,
			'sid' => '-1',
		);
		$db->insert_query("templates", $insert_template);
	}
}

function changstats_deactivate()
{
	global $db;
	$setting_groupname = 'chang_stats';

	// Delete settings

	$query = $db->query("SELECT gid FROM " . TABLE_PREFIX . "settinggroups WHERE name='$setting_groupname' LIMIT 1");
	$qinfo = $db->fetch_array($query);
	$db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE gid='$qinfo[gid]'");

	// Delete settings group

	$db->query("DELETE FROM " . TABLE_PREFIX . "settinggroups WHERE name='$setting_groupname'");

	// Delete templates

	$deletetemplates = array(
		'changuondyu_stats_main',
		'changuondyu_stats_refreshbutton',
		'changuondyu_stats_topuser',
		'changuondyu_stats_topforum',
		'changuondyu_stats_script',
		'changuondyu_latestpost',
		'changuondyu_latestpost_bit',
		'changuondyu_chooselatestposts',
		'changuondyu_top_bit',
		'changuondyu_top_head'
	);
	foreach($deletetemplates as $title)
	{
		$db->query("DELETE FROM " . TABLE_PREFIX . "templates WHERE title='" . $title . "'");
	}
}

function changstats_getdata()
{
	global $db, $mybb, $templates, $theme, $cache, $lang;
	$lang->load('changstats');
	if ($mybb->settings['changstats_turn'])
	{
		$hiddenforum = '0';
		$forumpermissions = forum_permissions();
		foreach($forumpermissions as $forumid => $permiss)
		{
			if ($permiss['canview'] != 1)
			{
				$hiddenforum.= ',' . $forumid;
			}
		}

		if ($mybb->settings['changstats_exclforum'])
		{
			$hiddenforum.= "," . $mybb->settings['changstats_exclforum'];
		}

		// get result value

		$cresult = $_REQUEST['result'];

		// AJAX GET NEW POST

		$listr2 = explode(",", $mybb->settings['changstats_result']);
		if ($cresult <= $listr2[sizeof($listr2) - 1])
		{

			// tat ca cac bai viet moi

			if ($_REQUEST['do'] == 'allforum' || $_REQUEST['do'] == 'inforum')
			{
				$threadclimit = $mybb->settings['changstats_trim_threadtitle']; // thread title trim
				$forumclimit = $mybb->settings['changstats_trim_forumtitle']; // forum title trim
				$userclimit = $mybb->settings['changstats_trim_username']; // username trim
				$queryfield = "tid,fid,subject,dateline,lastpost,replies,views,lastposter,lastposteruid";
				if ($_REQUEST['do'] == 'allforum')
				{
					$latestpostq = $db->query("SELECT $queryfield FROM " . TABLE_PREFIX . "threads WHERE fid NOT IN ($hiddenforum) AND visible = 1 ORDER BY lastpost DESC LIMIT $cresult");
				}
				else
				{
					$foruminid = $_REQUEST['listforumid'];
					$latestpostq = $db->query("SELECT $queryfield FROM " . TABLE_PREFIX . "threads WHERE fid IN ($foruminid) AND fid NOT IN ($hiddenforum) AND visible = 1 ORDER BY lastpost DESC LIMIT $cresult");
				}

				while ($latestpost = $db->fetch_array($latestpostq))
				{
					$latestpost[fulltitle] = $latestpost[subject];
					$latestpost[fullposter] = $latestpost[lastposter];
					$clastpost = $latestpost[lastpost];
					$latestpost[lastpost] = my_date($mybb->settings['changstats_dateformat'], $latestpost[lastpost]);

					// trim thread title

					$latestpost[subject] = htmlspecialchars_uni($latestpost[subject]);
					if ($threadclimit > 0 && my_strlen($latestpost[subject]) > $threadclimit)
					{
						$latestpost[subject] = my_substr($latestpost[subject], 0, $threadclimit) . '...';
					}

					// get forum title store thread and trim

					$query = $db->query("SELECT name FROM " . TABLE_PREFIX . "forums WHERE fid = '$latestpost[fid]' LIMIT 1");
					$qinfo = $db->fetch_array($query);
					$latestpost[forumname] = $qinfo[name];
					$latestpost[forumname] = strip_tags($latestpost[forumname]);
					$latestpost[forumnamefull] = $latestpost[forumname];
					if ($forumclimit > 0 && my_strlen($latestpost[forumname]) > $forumclimit)
					{
						$latestpost[forumname] = my_substr($latestpost[forumname], 0, $forumclimit) . '...';
					}

					// lastposter markup and trim

					$latestpost[fulllastposter] = $latestpost[lastposter];
					if ($userclimit > 0 && my_strlen($latestpost[lastposter]) > $userclimit)
					{
						$latestpost[lastposter] = my_substr($latestpost[lastposter], 0, $userclimit) . '...';
					}

					$query = $db->query("SELECT usergroup,displaygroup FROM " . TABLE_PREFIX . "users WHERE uid = '$latestpost[lastposteruid]' LIMIT 1");
					$qinfo = $db->fetch_array($query);
					$latestpost[lastposter] = format_name($latestpost[lastposter], $qinfo['usergroup'], $qinfo['displaygroup']);
					$vuserid = $mybb->user['uid'];
					$query = $db->query("SELECT lastvisit FROM " . TABLE_PREFIX . "users WHERE uid = '$vuserid' LIMIT 1");
					$qinfo = $db->fetch_array($query);
					$vlastvisit = $qinfo['lastvisit'];
					$pstatus = '';
					if ($vlastvisit > $clastpost)
					{
						$pstatus = 'old';
					}
					else
					{
						$pstatus = 'new';
					}

					eval("\$changtop_lastpost_bit .= \"" . $templates->get("changuondyu_latestpost_bit") . "\";");
				}

				eval("\$changuondyu_latestpost = \"" . $templates->get("changuondyu_latestpost") . "\";");
				echo $changuondyu_latestpost;
			}

			// ///////////////////////////////////////////////// top ///////////////////////////////////////////////////
			// Trim Value

			$topuserclimit = $mybb->settings['changstats_top_trim_username'];
			$topthreadclimit = $mybb->settings['changstats_top_trim_threadtitle'];
			$topforumclimit = $mybb->settings['changstats_top_trim_forumtitle'];

			// ///////////// Newest Member /////////////////

			if ($_REQUEST['do'] == 'newmember')
			{
				$top_query = $db->query("SELECT uid,username,regdate,usergroup,displaygroup FROM " . TABLE_PREFIX . "users ORDER BY regdate DESC LIMIT $cresult");
				while ($top = $db->fetch_array($top_query))
				{
					$title = $top[username];
					if ($topuserclimit > 0 && my_strlen($top[username]) > $topuserclimit)
					{
						$top[username] = my_substr($top[username], 0, $topuserclimit) . '...';
					}

					$top[username] = format_name($top[username], $top[usergroup], $top[displaygroup]);
					$colum1 = "<a href='member.php?action=profile&uid=" . $top[uid] . "'>" . $top[username] . "</a>";
					$colum2 = my_date($mybb->settings['changstats_joindate'], $top[regdate]);
					eval("\$chang_top_element .= \"" . $templates->get("changuondyu_top_bit") . "\";");
				}

				$h1 = $lang->changstats_username;
				$h2 = $lang->changstats_date;
				eval("\$changuondyu_topoutput = \"" . $templates->get("changuondyu_top_head") . "\";");
				echo $changuondyu_topoutput;
			}

			// ///////////// Top Poster //////////////////

			if ($_REQUEST['do'] == 'topposter')
			{
				$top_query = $db->query("SELECT uid,username,postnum,usergroup,displaygroup FROM " . TABLE_PREFIX . "users ORDER BY postnum DESC LIMIT $cresult");
				while ($top = $db->fetch_array($top_query))
				{
					$title = $top[username];
					if ($topuserclimit > 0 && my_strlen($top[username]) > $topuserclimit)
					{
						$top[username] = my_substr($top[username], 0, $topuserclimit) . '...';
					}

					$top[username] = format_name($top[username], $top[usergroup], $top[displaygroup]);
					$colum1 = "<a href='member.php?action=profile&uid=" . $top[uid] . "'>" . $top[username] . "</a>";
					$colum2 = $top[postnum];
					eval("\$chang_top_element .= \"" . $templates->get("changuondyu_top_bit") . "\";");
				}

				$h1 = $lang->changstats_username;
				$h2 = $lang->changstats_posts;
				eval("\$changuondyu_topoutput = \"" . $templates->get("changuondyu_top_head") . "\";");
				echo $changuondyu_topoutput;
			}

			// ///////////// Top Thanked //////////////////

			if ($_REQUEST['do'] == 'topthank' && $db->field_exists("thxcount", "users"))
			{
				$top_query = $db->query("SELECT uid,username,thxcount,usergroup,displaygroup FROM " . TABLE_PREFIX . "users ORDER BY thxcount DESC LIMIT $cresult");
				while ($top = $db->fetch_array($top_query))
				{
					$title = $top[username];
					if ($topuserclimit > 0 && my_strlen($top[username]) > $topuserclimit)
					{
						$top[username] = my_substr($top[username], 0, $topuserclimit) . '...';
					}

					$top[username] = format_name($top[username], $top[usergroup], $top[displaygroup]);
					$colum1 = "<a href='member.php?action=profile&uid=" . $top[uid] . "'>" . $top[username] . "</a>";
					$colum2 = $top[thxcount];
					eval("\$chang_top_element .= \"" . $templates->get("changuondyu_top_bit") . "\";");
				}

				$h1 = $lang->changstats_username;
				$h2 = '';
				eval("\$changuondyu_topoutput = \"" . $templates->get("changuondyu_top_head") . "\";");
				echo $changuondyu_topoutput;
			}

			// /////////// Most view thread ///////////////////

			if ($_REQUEST['do'] == 'mostview')
			{
				$top_query = $db->query("SELECT tid,subject,views FROM " . TABLE_PREFIX . "threads ORDER BY views DESC LIMIT $cresult");
				while ($top = $db->fetch_array($top_query))
				{
					$title = $top[subject];
					if ($topthreadclimit > 0 && my_strlen($top[subject]) > $topthreadclimit)
					{
						$top[subject] = my_substr($top[subject], 0, $topthreadclimit) . '...';
					}

					$colum1 = "<a href='showthread.php?tid=" . $top[tid] . "'>" . htmlspecialchars_uni($top[subject]) . "</a>";
					$colum2 = $top[views];
					eval("\$chang_top_element .= \"" . $templates->get("changuondyu_top_bit") . "\";");
				}

				$h1 = $lang->changstats_thread;
				$h2 = $lang->changstats_views;
				eval("\$changuondyu_topoutput = \"" . $templates->get("changuondyu_top_head") . "\";");
				echo $changuondyu_topoutput;
			}

			// /////////// hot thread ///////////////////

			if ($_REQUEST['do'] == 'hotthread')
			{
				$top_query = $db->query("SELECT tid,subject,replies FROM " . TABLE_PREFIX . "threads ORDER BY replies DESC LIMIT $cresult");
				while ($top = $db->fetch_array($top_query))
				{
					$title = $top[subject];
					if ($topthreadclimit > 0 && my_strlen($top[subject]) > $topthreadclimit)
					{
						$top[subject] = my_substr($top[subject], 0, $topthreadclimit) . '...';
					}

					$colum1 = "<a href='showthread.php?tid=" . $top[tid] . "'>" . htmlspecialchars_uni($top[subject]) . "</a>";
					$colum2 = $top[replies];
					eval("\$chang_top_element .= \"" . $templates->get("changuondyu_top_bit") . "\";");
				}

				$h1 = $lang->changstats_thread;
				$h2 = $lang->changstats_reply;
				eval("\$changuondyu_topoutput = \"" . $templates->get("changuondyu_top_head") . "\";");
				echo $changuondyu_topoutput;
			}

			// /////////// most popular forum ///////////////////

			if ($_REQUEST['do'] == 'mostpopular')
			{
				$top_query = $db->query("SELECT fid,name,posts FROM " . TABLE_PREFIX . "forums ORDER BY posts DESC LIMIT $cresult");
				while ($top = $db->fetch_array($top_query))
				{
					$title = $top[name];
					if ($topforumclimit > 0 && my_strlen($top[name]) > $topforumclimit)
					{
						$top[name] = my_substr($top[name], 0, $topforumclimit) . '...';
					}

					$colum1 = "<a href='forumdisplay.php?fid=" . $top[tid] . "'>" . $top[name] . "</a>";
					$colum2 = $top[posts];
					eval("\$chang_top_element .= \"" . $templates->get("changuondyu_top_bit") . "\";");
				}

				$h1 = $lang->changstats_forum;
				$h2 = $lang->changstats_posts;
				eval("\$changuondyu_topoutput = \"" . $templates->get("changuondyu_top_head") . "\";");
				echo $changuondyu_topoutput;
			}
		} // check result
	} // changstats turn
}

function changstats_maindisplay()
{
	global $mybb, $templates, $theme, $lang, $changstats;
	$lang->load('changstats');
	if ($mybb->settings['changstats_turn'])
	{

		// Create List of result

		$listresult = explode(",", $mybb->settings['changstats_result']);
		foreach($listresult as $result)
		{
			$choosekq.= "<option value='$result'>$result</option>";
		}

		// Gen Sp Tab

		$listorder = preg_replace("#(\r\n|\r|\n)#s", "+#+", $mybb->settings['changstats_customtab']);
		$listorder = explode("+#+", $listorder);
		$ordert = 0;
		foreach($listorder as $listmenu)
		{
			if ($listmenu)
			{
				$ordert++;
				$tg = explode("|", $listmenu);
				$menuname = $tg[0];
				$listforumid = $tg[1];
				$listtab.= "listtab[$ordert] = \"inforum&listforumid=$listforumid\";\n";
				eval("\$chooselatestposts .= \"" . $templates->get("changuondyu_chooselatestposts") . "\";");
			}
		}

		$ordert2 = $ordert + 1;
		$ordert3 = $ordert + 3;

		// Refresh button

		if ($mybb->settings['changstats_disbutton'])
		{
			eval("\$refreshbutton = \"" . $templates->get("changuondyu_stats_refreshbutton") . "\";");
		}

		// Gen element of top

		$topposter = "<option value='topposter'>$lang->changstats_topposter</option>";
		$newmember = "<option value='newmember'>$lang->changstats_newmember</option>";
		$topthank = "<option value='topthank'>$lang->changstats_topthank</option>";
		$mostviewthread = "<option value='mostview'>$lang->changstats_mostviewthread</option>";
		$hotthread = "<option value='hotthread'>$lang->changstats_hotthread</option>";
		$mostpopularforum = "<option value='mostpopular'>$lang->changstats_mostpopularforum</option>";
		eval("\$choosetop_user .= \"" . $templates->get("changuondyu_stats_topuser") . "\";");
		eval("\$choosetop_forum .= \"" . $templates->get("changuondyu_stats_topforum") . "\";");

		// AUTO REFRESH

		if ($mybb->user['uid'] == 0)
		{
			if (!$mybb->settings['changstats_disforguest'] && $mybb->settings['changstats_refreshtime'] > 0)
			{
				$autorefresh = "setInterval('changstats_post()', 1000*{$mybb->settings['changstats_refreshtime']});";
			}
		}
		else
		{
			if ($mybb->settings['changstats_refreshtime'] > 0)
			{
				$autorefresh = "setInterval('changstats_post()', 1000*{$mybb->settings['changstats_refreshtime']});";
			}
		}

		// MAIN SHOW

		eval("\$changstats_script = \"" . $templates->get("changuondyu_stats_script") . "\";");
		eval("\$changstats = \"" . $templates->get("changuondyu_stats_main") . "\";");
	} // changstats turn
}

?>