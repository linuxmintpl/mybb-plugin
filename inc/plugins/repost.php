<?php
/*
 * MyBB: Last Posts Plugin for MyBB
 *
 * File: repost.php
 * 
 * Authors: Hamed & updated by Vintagedaddyo
 *
 * MyBB Version: 1.8
 *
 * Plugin Version: 1.2
 * 
 */

// Disallow direct access to this file for security reasons

if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook('global_start','repost');

function repost_info(){
	global $lang;

    $lang->load("last");
    
    $lang->repost_Desc = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float:right;">' .
        '<input type="hidden" name="cmd" value="_s-xclick">' . 
        '<input type="hidden" name="hosted_button_id" value="AZE6ZNZPBPVUL">' .
        '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">' .
        '<img alt="" border="0" src="https://www.paypalobjects.com/pl_PL/i/scr/pixel.gif" width="1" height="1">' .
        '</form>' . $lang->repost_Desc;

	return array(
        'name' => $lang->repost_Name,
        'description' => $lang->repost_Desc,
        'website' => $lang->repost_Web,
        'author' => $lang->repost_Auth,
        'authorsite' => $lang->repost_AuthSite,
        'version' => $lang->repost_Ver,
        'compatibility' => $lang->repost_Compat
	);
}

function repost_activate() {
				require MYBB_ROOT.'/inc/adminfunctions_templates.php';
				
				global $db, $lang;

                $lang->load("last");

				$query = $db->simple_select("settinggroups","COUNT(*) as rows");
				$rows = $db->fetch_field($query,"rows");

				$repost_group = array(
 					'name' => $lang->repost_name_0,
					'title' => $lang->repost_title_0,
					'description' => $lang->repost_desc_0,
					'disporder' => $rows + '1',
					'isdefault' => '0',
					);

				$db->insert_query('settinggroups',$repost_group);
				$gid = $db->insert_id();

				$repost_setting_1 = array(
 					'name' => $lang->repost_name_1,
					'title' => $lang->repost_title_1,
					'description' => $lang->repost_desc_1,
					'optionscode' => 'onoff',
					'value' => '1',
					'disporder' => '1',
					'gid' => intval($gid)
					);

				$repost_setting_2 = array(
 					'name' => $lang->repost_name_2,
					'title' => $lang->repost_title_2,
					'description' => $lang->repost_desc_2,
					'optionscode' => 'text',
					'value' => '10',
					'disporder' => '2',
					'gid' => intval($gid)
					);

				$repost_setting_3 = array(
 					'name' => $lang->repost_name_3,
					'title' => $lang->repost_title_3,
					'description' => $lang->repost_desc_3,
					'optionscode' => 'onoff',
					'value' => '1',
					'disporder' => '4',
					'gid' => intval($gid)
					);

				$repost_setting_4 = array(
 					'name' => $lang->repost_name_4,
					'title' => $lang->repost_title_4,
					'description' => $lang->repost_desc_4,
					'optionscode' => 'onoff',
					'value' => '1',
					'disporder' => '5',
					'gid' => intval($gid)
					);

				$repost_setting_5 = array(
 					'name' => $lang->repost_name_5,
					'title' => $lang->repost_title_5,
					'description' => $lang->repost_desc_5,
					'optionscode' => 'onoff',
					'value' => '1',
					'disporder' => '6',
					'gid' => intval($gid)
					);

				$repost_setting_6 = array(
 					'name' => $lang->repost_name_6,
					'title' => $lang->repost_title_6,
					'description' => $lang->repost_desc_6,
					'optionscode' => 'onoff',
					'value' => '1',
					'disporder' => '7',
					'gid' => intval($gid)
					);

				$repost_setting_7 = array(
 					'name' => $lang->repost_name_7,
					'title' => $lang->repost_title_7,
					'description' => $lang->repost_desc_7,
					'optionscode' => 'onoff',
					'value' => '1',
					'disporder' => '8',
					'gid' => intval($gid)
					);

				$repost_setting_8 = array(
 					'name' => $lang->repost_name_8,
					'title' => $lang->repost_title_8,
					'description' => $lang->repost_desc_8,
					'optionscode' => 'onoff',
					'value' => '1',
					'disporder' => '9',
					'gid' => intval($gid)
					);

				$repost_setting_9 = array(
 					'name' => $lang->repost_name_9,
					'title' => $lang->repost_title_9,
					'description' => $lang->repost_desc_9,
					'optionscode' => 'text',
					'value' => '',
					'disporder' => '3',
					'gid' => intval($gid)
					);

				$repost_setting_11 = array(
 					'name' => $lang->repost_name_11,
					'title' => $lang->repost_title_11,
					'description' => $lang->repost_desc_11,
					'optionscode' => 'onoff',
					'value' => '0',
					'disporder' => '11',
					'gid' => intval($gid)
					);

				$db->insert_query('settings',$repost_setting_1);
				$db->insert_query('settings',$repost_setting_2);
				$db->insert_query('settings',$repost_setting_3);
				$db->insert_query('settings',$repost_setting_4);
				$db->insert_query('settings',$repost_setting_5);
				$db->insert_query('settings',$repost_setting_6);
				$db->insert_query('settings',$repost_setting_7);
				$db->insert_query('settings',$repost_setting_8);
				$db->insert_query('settings',$repost_setting_9);
				$db->insert_query('settings',$repost_setting_11);
				rebuild_settings();

				$new_templates['repost'] = "<table class=\"tborder\" style=\"CLEAR: both\" cellSpacing=\"1\" cellPadding=\"4\" border=\"0\">
	<thead>
		<tr>
			<td class=\"thead\">
			<div class=\"expcolimage\">
				<img src=\"images/collapse.png\" id=\"last_post_img\" class=\"expander\" alt=\"[-]\" title=\"[-]\" /></div>
			<div>
				<strong>{\$lang->last_posts}</strong>
			</div>
			</td>
		</tr>
	</thead>
	<tbody style=\"{$expdisplay}\" id=\"last_post_e\">
	<tr>
		<td class=\"trow1\" style=\"padding-right: 60px;\">
		<marquee onmouseover=\"this.setAttribute(\'scrollamount\',0)\" onmouseout=\"this.setAttribute(\'scrollamount\',1)\" direction=\"up\" scrollamount=\"1\" scrolldelay=\"1\" height=\"120\"> 
		{\$threadlist}</marquee> </td>
	</tr>
	</tbody>
</table><br>";

				$new_templates['repost_threadsbits'] = "<table>
	<tr>
		<td id=\"subject\" colspan=\"6\"><strong>
		<a target=\"_blank\" href=\"{\$mybb->settings[\'bburl\']}/showthread.php?tid={\$thread[\'tid\']}\">
		{\$read} {\$thread[\'subject\']}</a></strong></td>
	</tr>
	<tr class=\"smalltext\" id=\"settingchange\">
		{\$starter}
		{\$lastposter}
		{\$replies}
		{\$forumname}
		{\$views_repost}
		{\$time_repost}
	</tr>
</table>";
				// Insert new templates
				foreach($new_templates as $title => $template) {
								$db->query("INSERT INTO `".TABLE_PREFIX."templates` VALUES (NULL, '$title', '$template', '-1', '120', '', '1157735635')");
				}

				//find_replace_templatesets('header','#<navigation>#',"{\$repost}\n\t\t\t<navigation>");

	            find_replace_templatesets("index", "#".preg_quote("{\$header}")."#i", "{\$header}\r\n{\$repost}");
	            find_replace_templatesets("portal", "#".preg_quote("{\$header}")."#i", "{\$header}\r\n{\$repost}");
}
function repost_deactivate() {
				require MYBB_ROOT.'/inc/adminfunctions_templates.php';

				global $db;

				$db->query("DELETE FROM ".TABLE_PREFIX.
								"settings WHERE name IN('showrepost', 'repost')");
				$db->query("DELETE FROM ".TABLE_PREFIX.
								"settings WHERE name IN('countreposts', 'repost')");
				$db->query("DELETE FROM ".TABLE_PREFIX.
								"settings WHERE name IN('show_starter_repost', 'repost')");
				$db->query("DELETE FROM ".TABLE_PREFIX.
								"settings WHERE name IN('show_lastposter_repost', 'repost')");
				$db->query("DELETE FROM ".TABLE_PREFIX.
								"settings WHERE name IN('show_replys_repost', 'repost')");
				$db->query("DELETE FROM ".TABLE_PREFIX.
								"settings WHERE name IN('show_forumname_repost', 'repost')");
				$db->query("DELETE FROM ".TABLE_PREFIX.
								"settings WHERE name IN('show_views_repost', 'repost')");
				$db->query("DELETE FROM ".TABLE_PREFIX.
								"settings WHERE name IN('show_time_repost', 'repost')");
				$db->query("DELETE FROM ".TABLE_PREFIX.
								"settings WHERE name IN('not_show_post', 'repost')");
				$db->query("DELETE FROM ".TABLE_PREFIX.
								"settings WHERE name IN('show_users', 'repost')");

				$db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='repost'");
				$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='repost'");
				$db->query("DELETE FROM ".TABLE_PREFIX.
								"templates WHERE title='repost_threadsbits'");

				//find_replace_templatesets('header','#{\$repost}\n\t\t\t#','',0);
				
			    find_replace_templatesets("index", "#".preg_quote("\r\n{\$repost}")."#i", "", 0);
			    find_replace_templatesets("portal", "#".preg_quote("\r\n{\$repost}")."#i", "", 0);
}

function repost() {
				global $mybb,$templates,$repost,$db,$lang,$expdisplay;
				$lang->load("last");
				if($mybb->settings['showrepost'] == '1') {
								$threadlist = '';
								$hiddenforums = explode(',',htmlspecialchars_uni($mybb->settings['not_show_post']));
								if(is_array($hiddenforums)) {
												foreach($hiddenforums as $fid) {
																$fid_array[] = intval($fid);
												}
												$hiddenforums = implode(',',$fid_array);
								}
								$query = $db->simple_select("forums","*","fid IN (".$hiddenforums.")");
								while($forumrow = $db->fetch_array($query)) {
												$forum[$forumrow['fid']] = $forumrow;
								}
								if($hiddenforums) {
												$query = $db->query("
		SELECT t.*, u.username
		FROM ".TABLE_PREFIX."threads t
		LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=t.uid)
		WHERE 1=1 $unviewwhere AND t.visible='1' AND t.closed NOT LIKE 'moved|%' AND  t.fid NOT IN(".
																$hiddenforums.")
		ORDER BY t.lastpost DESC 
		LIMIT 0, ".htmlspecialchars_uni($mybb->settings['countreposts']));
								}
								else {
												$query = $db->query("
		SELECT t.*, u.username
		FROM ".TABLE_PREFIX."threads t
		LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=t.uid)
		WHERE 1=1 $unviewwhere AND t.visible='1' AND t.closed NOT LIKE 'moved|%'
		ORDER BY t.lastpost DESC 
		LIMIT 0, ".htmlspecialchars_uni($mybb->settings['countreposts']));
								}
								while($thread = $db->fetch_array($query)) {
												if($mybb->user['uid']) {
																$read_query = $db->simple_select("threadsread","tid","uid=".$mybb->
																				user['uid']." AND tid=".$thread['tid']."");
																if($db->num_rows($read_query) != '0') {
																				$read =
																								'<img src="images/last/post.png" style="vertical-align: middle;padding-right: 2px;">';

																}
																else {
																				$read =
																								'<img src="images/last/GoToPost.png" style="vertical-align: middle;padding-right: 2px;" title="'.
																								$lang->Not_read.'" alt="'.$lang->Not_read.'">';
																}
												}
												$forum_query = $db->query("SELECT * FROM ".TABLE_PREFIX.
																"forums WHERE fid='".$thread['fid']."'");
												if($forum = $db->fetch_array($forum_query)) {
																$thread_forum = $forum['name'];
												}
												$lastpostdate = my_date($mybb->settings['dateformat'],$thread['lastpost']);
												$lastposttime = my_date($mybb->settings['timeformat'],$thread['lastpost']);
												if($thread['lastposteruid'] == 0) {
																$lastposterlink = $thread['lastposter'];
												}
												else {
																$lastposterlink =
																				'<td><img src="images/last/username.png" style="vertical-align: middle;padding-right: 2px;"><a target="_blank" href="'.
																				$mybb->settings['bburl'].'/member.php?action=profile&uid='.
																				$thread['lastposteruid'].'">'.$thread['lastposter'].
																				'</a></td>';
												}
												$thread['subject'] = htmlspecialchars_uni($thread['subject']);

												//checking which part do you want to display
												//check show_starter_repost
												if($mybb->settings['show_starter_repost'] == "1") {
																$starter = '<td>'.$lang->Starter.
																				':<img src="images/last/username.png" style="vertical-align: middle;padding-right: 2px;"><a target="_blank" href="'.
																				$mybb->settings['bburl'].'/member.php?action=profile&uid='.
																				$thread['uid'].'">'.$thread['username'].'</a></td>';
												}
												//check show_lastposter_repost
												if($mybb->settings['show_lastposter_repost'] == "1") {
																$lastposter = '<td>'.$lang->latest_threads_lastpost.''.$lastposterlink.
																				'</td>';
												}
												//check show_replys_repost
												if($mybb->settings['show_replys_repost'] == "1") {
																$replies = '<td>'.$lang->latest_threads_replies.''.$thread['replies'].
																				'</td>';
												}
												//check show_forumname_repost
												if($mybb->settings['show_forumname_repost'] == "1") {
																$forumname =
																				'<td><img src="images/last/forum.png" style="vertical-align: middle;padding-right: 2px;" title="'.
																				$lang->Forum_Name.'"><a target="_blank" href="'.$mybb->
																				settings['bburl'].'/forumdisplay.php?fid='.$thread['fid'].
																				'">'.$thread_forum.'</a></td>';
												}
												//check show_views_repost
												if($mybb->settings['show_views_repost'] == "1") {
																$views_repost = '<td>'.$lang->views.''.$thread['views'].'</td>';
												}
												//check show_time_repost
												if($mybb->settings['show_time_repost'] == "1") {
																$time_repost =
																				'<td><img src="images/last/date.png" style="vertical-align: middle;padding-right: 2px;">'.
																				$lang->re_time.''.$lastpostdate.'</td>';
												}
												eval("\$threadlist .= \"".$templates->get("repost_threadsbits")."\";");
								}
								if($mybb->settings['show_users'] == '1') {
												if($mybb->user['uid']) {
																eval("\$repost = \"".$templates->get("repost")."\";");
												}
												else {
																return false;
												}
								}
								else {
												eval("\$repost = \"".$templates->get("repost")."\";");
								}
				}
}
?>