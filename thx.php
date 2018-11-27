<?php

/**
 * Thank You MyBB System v2.5.6
 * Upgrade for MyBB 1.8.x
 * contact: neogeoman@gmail.com
 * Website: http://www.mybb.com
 * Author:  Dark Neo
 */
 
define("IN_MYBB", 1);
$filename = substr($_SERVER['SCRIPT_NAME'], -strpos(strrev($_SERVER['SCRIPT_NAME']), "/"));
define('THIS_SCRIPT', $filename);
$templatelist = "thanks_results, thanks_results_none,thanks_content,thanks_page,multipage_page_current, multipage_page, multipage_end, multipage_nextpage, multipage_jump_page, multipage";
require_once "./global.php";

$forum_notgid = explode(',', $mybb->settings['thx_hidesystem_notgid']);
if(!$mybb->user['uid'] || $mybb->settings['thx_active'] != 1 || !function_exists('thx_is_installed') || in_array($mybb->user['usergroup'], $forum_notgid))
{
	error_no_permission();
}

require_once MYBB_ROOT.'inc/plugins/thx.php';

$lang->load("thx", false, true);

$plugins->run_hooks("thx_start");

add_breadcrumb($lang->thx_title, THIS_SCRIPT);
if($mybb->user['uid']==0)
{
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		error($lang->thx_cant_see);
	}	
}

if($mybb->input['thanked'])
{
    if(!verify_post_check($mybb->input['my_post_key']))
	{
		error($lang->thx_cant_see);
	}
	// get forums user cannot view
	$unviewable = get_unviewable_forums(true);	
	if(isset($fids) && !empty($fids) && !empty($unviewable)){
	$unviewable .= "," . $fids;
	}	
	elseif(isset($fids) && empty($unviewable)){
	$unviewable .= $fids;
	}
	if($unviewable)
	{
		$unviewwhere = " AND p.fid NOT IN ($unviewable)";
	}	
	if(!$mybb->user['ismoderator'])
	{
		$unviewwhere .= " AND p.visible='1'";
	}

	$mybb->input['thanked'] = (int)$mybb->input['thanked'];
	$mybb->input['thanked'] = $db->escape_string($mybb->input['thanked']);
	
	$query = $db->query("SELECT t.*, p.fid, p.visible 
		FROM ".TABLE_PREFIX."thx t
		LEFT JOIN ".TABLE_PREFIX."posts p ON (t.pid=p.pid)	
		WHERE t.uid='{$mybb->input['thanked']}'{$unviewwhere} ORDER BY t.txid"
	);		

	$thx = $db->fetch_array($query);
	$db->free_result($query);

	if(!$thx['uid'])
	{
		error($lang->thx_not_received);
	}

	add_breadcrumb($lang->thx_view_uid, THIS_SCRIPT."?thanked={$thx['uid']}&amp;my_post_key={$mybb->post_code}");
	
	$page = (int)$mybb->input['page'];
	if($page < 1) $page = 1;
	$numtot = $db->fetch_field($db->simple_select('thx', 'COUNT(*) AS numtot', "uid='{$mybb->input['thanked']}'"), 'numtot');
	$perpage = 40;
	$likes_founded = (int)$numtot;			
	$multipage = multipage($numtot, $perpage, $page, $_SERVER['PHP_SELF']."?thanked={$thx['uid']}&amp;my_post_key={$mybb->post_code}");
	$query = $db->query("
		SELECT t.*, p.subject, p.fid, p.visible, u.uid, u.username, u.usergroup, u.displaygroup, u.avatar, u.thx, u.thxcount, ug.username as ugname, ug.usergroup as uguserg, ug.displaygroup as ugdisp, ug.avatar as ugavatar, ug.thx as uthx, ug.thxcount as uthxcount
		FROM ".TABLE_PREFIX."thx t
		LEFT JOIN ".TABLE_PREFIX."users u ON (t.uid=u.uid)
		LEFT JOIN ".TABLE_PREFIX."users ug ON (t.adduid=ug.uid)	
		LEFT JOIN ".TABLE_PREFIX."posts p ON (t.pid=p.pid)	
		WHERE t.uid='".(int)$thx['uid']."'{$unviewwhere}
		ORDER BY t.time DESC
		LIMIT ".(($page-1)*$perpage).", {$perpage}		
	");

	$users_list = '';
	while($gived = $db->fetch_array($query))
	{
		$trow = alt_trow();
		if($mybb->user['usergroup'] == 4 || $mybb->user['uid'] == $mybb->input['thanked']){
        $gived['txid'] = (int)$gived['txid'];
		}
		else{
		$gived['txid'] = " - ";
		}		
        $gived['pid'] = (int)$gived['pid'];
		$gived['url'] = htmlspecialchars_uni($mybb->settings['bburl'] . "/showthread.php?pid=" . $gived['pid'] . "#pid" . $gived['pid']);
		if($gived['avatar'] != ""){ 
		$gived['avatar'] = "<img src=".htmlspecialchars_uni($gived['avatar'])." class=\"thx_avatar\" alt=\"avatar\" />";
		}
		else{
		$gived['avatar'] = '<img src="images/default_avatar.png" class="thx_avatar" alt="no avatar" />';
		}
		if($gived['ugavatar'] != ""){ 		
		$gived['ugavatar'] = "<img src=".htmlspecialchars_uni($gived['ugavatar'])." class=\"thx_avatar\" alt=\"avatar\" />";		
		}
		else{
		$gived['ugavatar'] = '<img src="images/default_avatar.png" class="thx_avatar" alt="no avatar" />';
		}
		$gived['username'] = htmlspecialchars_uni($gived['username']);
		$gived['username'] = format_name($gived['username'], $gived['usergroup'], $gived['displaygroup']);
		$gived['username'] = build_profile_link($gived['username'], $gived['uid']);
		$gived['ugname'] = htmlspecialchars_uni($gived['ugname']);
		$gived['ugname'] = format_name($gived['ugname'], $gived['uguserg'], $gived['ugdisp']);
		$gived['ugname'] = build_profile_link($gived['ugname'], $gived['adduid']);		
		$gived['time'] = my_date('relative', $gived['time']);
		$gived['thx'] = my_number_format($gived['thx']);
		$gived['thxcount'] = my_number_format($gived['thxcount']);
		$gived['uthx'] = my_number_format($gived['uthx']);
		$gived['uthxcount'] = my_number_format($gived['uthxcount']);		
		$gived['subject'] = htmlspecialchars_uni($gived['subject']);
		if(my_strlen($gived['subject']) > 25)
		{
			$gived['subject'] = my_substr($gived['subject'], 0, 25)."...";
		}	

		eval("\$users_list .= \"".$templates->get("thanks_results")."\";");
	}

	$db->free_result($query);

	if(!$users_list)
	{
		eval("\$users_list = \"".$templates->get("thanks_results_none")."\";");
	}

	eval("\$content = \"".$templates->get("thanks_content")."\";");
	eval("\$page = \"".$templates->get("thanks_page")."\";");

	output_page($page);
	exit;
}

else if($mybb->input['thanks'])
{
    if(!verify_post_check($mybb->input['my_post_key'])){
		error($lang->thx_cant_see);
	}
	// get forums user cannot view
	$unviewable = get_unviewable_forums(true);	
	if(isset($fids) && !empty($fids) && !empty($unviewable)){
	$unviewable .= "," . $fids;
	}	
	elseif(isset($fids) && empty($unviewable)){
	$unviewable .= $fids;
	}
	if($unviewable)
	{
		$unviewwhere = " AND p.fid NOT IN ($unviewable)";
	}	
	if(!$mybb->user['ismoderator'])
	{
		$unviewwhere .= " AND p.visible='1'";
	}

	$mybb->input['thanks'] = (int)$mybb->input['thanks'];
	$mybb->input['thanks'] = $db->escape_string($mybb->input['thanks']);
	
	$query = $db->query("SELECT t.*, p.fid, p.visible 
		FROM ".TABLE_PREFIX."thx t
		LEFT JOIN ".TABLE_PREFIX."posts p ON (t.pid=p.pid)	
		WHERE t.adduid='{$mybb->input['thanks']}'{$unviewwhere} ORDER BY t.txid"
	);	

	$thx = $db->fetch_array($query);
	$db->free_result($query);

	if(!$thx['adduid'])
	{
		error($lang->thx_not_given);
	}

	add_breadcrumb($lang->thx_view_adduid, THIS_SCRIPT."?thanks={$thx['adduid']}&amp;my_post_key={$mybb->post_code}");
	
	$page = (int)$mybb->input['page'];
	if($page < 1) $page = 1;
	$numtot = $db->fetch_field($db->simple_select('thx', 'COUNT(*) AS numtot', "adduid='{$mybb->input['thanks']}'"), 'numtot');
	$perpage = 40;
	$likes_founded = (int)$numtot;		
	$multipage = multipage($numtot, $perpage, $page, $_SERVER['PHP_SELF']."?thanks={$thx['adduid']}&amp;my_post_key={$mybb->post_code}");

	$query = $db->query("
		SELECT t.*, p.subject, p.fid, p.visible, u.uid, u.username, u.usergroup, u.displaygroup, u.avatar, u.thx, u.thxcount, ug.username as ugname, ug.usergroup as uguserg, ug.displaygroup as ugdisp, ug.avatar as ugavatar, ug.thx as uthx, ug.thxcount as uthxcount
		FROM ".TABLE_PREFIX."thx t
		LEFT JOIN ".TABLE_PREFIX."users u ON (t.uid=u.uid)
		LEFT JOIN ".TABLE_PREFIX."users ug ON (t.adduid=ug.uid)	
		LEFT JOIN ".TABLE_PREFIX."posts p ON (t.pid=p.pid)	
		WHERE t.adduid='".(int)$thx['adduid']."'{$unviewwhere}
		ORDER BY t.time DESC
		LIMIT ".(($page-1)*$perpage).", {$perpage}		
	");
	
	$users_list = '';
	while($gived = $db->fetch_array($query))
	{
		$trow = alt_trow();
		if($mybb->user['usergroup'] == 4 || $mybb->user['uid'] == $mybb->input['thanks']){
        $gived['txid'] = (int)$gived['txid'];
		}
		else{
		$gived['txid'] = " - ";
		}
        $gived['pid'] = (int)$gived['pid'];
		$gived['url'] = htmlspecialchars_uni($mybb->settings['bburl'] . "/showthread.php?pid=" . $gived['pid'] . "#pid" . $gived['pid']);
		if($gived['avatar'] != ""){ 
		$gived['avatar'] = "<img src=".htmlspecialchars_uni($gived['avatar'])." class=\"thx_avatar\" alt=\"avatar\" />";
		}
		else{
		$gived['avatar'] = '<img src="images/default_avatar.png" class="thx_avatar" alt="no avatar" />';
		}
		if($gived['ugavatar'] != ""){ 		
		$gived['ugavatar'] = "<img src=".htmlspecialchars_uni($gived['ugavatar'])." class=\"thx_avatar\" alt=\"avatar\" />";		
		}
		else{
		$gived['ugavatar'] = '<img src="images/default_avatar.png" class="thx_avatar" alt="no avatar" />';
		}
		$gived['username'] = htmlspecialchars_uni($gived['username']);
		$gived['username'] = format_name($gived['username'], $gived['usergroup'], $gived['displaygroup']);
		$gived['username'] = build_profile_link($gived['username'], $gived['uid']);
		$gived['ugname'] = htmlspecialchars_uni($gived['ugname']);
		$gived['ugname'] = format_name($gived['ugname'], $gived['uguserg'], $gived['ugdisp']);
		$gived['ugname'] = build_profile_link($gived['ugname'], $gived['adduid']);		
		$gived['time'] = my_date('relative', $gived['time']);	
		$gived['thx'] = my_number_format($gived['thx']);
		$gived['thxcount'] = my_number_format($gived['thxcount']);
		$gived['uthx'] = my_number_format($gived['uthx']);
		$gived['uthxcount'] = my_number_format($gived['uthxcount']);		
		$gived['subject'] = htmlspecialchars_uni($gived['subject']);
		if(my_strlen($gived['subject']) > 25)
		{
			$gived['subject'] = my_substr($gived['subject'], 0, 25)."...";
		}

		eval("\$users_list .= \"".$templates->get("thanks_results")."\";");
	}
	$db->free_result($query);

	if(!$users_list)
	{
		eval("\$users_list = \"".$templates->get("thanks_results_none")."\";");
	}

	eval("\$content = \"".$templates->get("thanks_content")."\";");
	eval("\$page = \"".$templates->get("thanks_page")."\";");

	output_page($page);
	exit;
}

else if($mybb->input['thanked_pid'])
{
    if(!verify_post_check($mybb->input['my_post_key'])){
		error($lang->thx_cant_see);
	}

	// get forums user cannot view
	$unviewable = get_unviewable_forums(true);	
	if(isset($fids) && !empty($fids) && !empty($unviewable)){
	$unviewable .= "," . $fids;
	}	
	elseif(isset($fids) && empty($unviewable)){
	$unviewable .= $fids;
	}
	if($unviewable)
	{
		$unviewwhere = " AND p.fid NOT IN ($unviewable)";
	}	
	if(!$mybb->user['ismoderator'])
	{
		$unviewwhere .= " AND p.visible='1'";
	}

	$mybb->input['thanked_pid'] = (int)$mybb->input['thanked_pid'];
	$mybb->input['thanked_pid'] = $db->escape_string($mybb->input['thanked_pid']);
	
	$query = $db->query("SELECT t.*, p.fid, p.visible 
		FROM ".TABLE_PREFIX."thx t
		LEFT JOIN ".TABLE_PREFIX."posts p ON (t.pid=p.pid)	
		WHERE t.pid='{$mybb->input['thanked_pid']}'{$unviewwhere} ORDER BY t.txid"
	);	

	$thx = $db->fetch_array($query);
	$db->free_result($query);

	if(!$thx['uid'])
	{
		error($lang->thx_not_post);
	}

	add_breadcrumb($lang->thx_view_pid, THIS_SCRIPT."?thanked_pid={$thx['pid']}&amp;my_post_key={$mybb->post_code}");
	
	$page = (int)$mybb->input['page'];
	if($page < 1) $page = 1;
	$numtot = $db->fetch_field($db->simple_select('thx', 'COUNT(*) AS numtot', "pid='{$mybb->input['thanked_pid']}'"), 'numtot');
	$perpage = 40;
	$likes_founded = (int)$numtot;		
	$multipage = multipage($numtot, $perpage, $page, $_SERVER['PHP_SELF']."?thanked_pid={$thx['pid']}&amp;my_post_key={$mybb->post_code}");

	$query = $db->query("
		SELECT t.*, p.subject, p.fid, p.visible, u.uid, u.username, u.usergroup, u.displaygroup, u.avatar, u.thx, u.thxcount, ug.username as ugname, ug.usergroup as uguserg, ug.displaygroup as ugdisp, ug.avatar as ugavatar, ug.thx as uthx, ug.thxcount as uthxcount
		FROM ".TABLE_PREFIX."thx t
		LEFT JOIN ".TABLE_PREFIX."users u ON (t.uid=u.uid)
		LEFT JOIN ".TABLE_PREFIX."users ug ON (t.adduid=ug.uid)	
		LEFT JOIN ".TABLE_PREFIX."posts p ON (t.pid=p.pid)	
		WHERE t.pid='".(int)$thx['pid']."'{$unviewwhere}
		ORDER BY t.time DESC
		LIMIT ".(($page-1)*$perpage).", {$perpage}		
	");

	$users_list = '';
	while($gived = $db->fetch_array($query))
	{
		$trow = alt_trow();
		if($mybb->user['usergroup'] == 4){
        $gived['txid'] = (int)$gived['txid'];
		}
		else{
        $gived['txid'] = " - ";		
		}
        $gived['pid'] = (int)$gived['pid'];
		$gived['url'] = htmlspecialchars_uni($mybb->settings['bburl'] . "/showthread.php?pid=" . $gived['pid'] . "#pid" . $gived['pid']);
		if($gived['avatar'] != ""){ 
		$gived['avatar'] = "<img src=".htmlspecialchars_uni($gived['avatar'])." class=\"thx_avatar\" alt=\"avatar\" />";
		}
		else{
		$gived['avatar'] = '<img src="images/default_avatar.png" class="thx_avatar" alt="no avatar" />';
		}
		if($gived['ugavatar'] != ""){ 		
		$gived['ugavatar'] = "<img src=".htmlspecialchars_uni($gived['ugavatar'])." class=\"thx_avatar\" alt=\"avatar\" />";		
		}
		else{
		$gived['ugavatar'] = '<img src="images/default_avatar.png" class="thx_avatar" alt="no avatar" />';
		}
		$gived['username'] = htmlspecialchars_uni($gived['username']);
		$gived['username'] = format_name($gived['username'], $gived['usergroup'], $gived['displaygroup']);
		$gived['username'] = build_profile_link($gived['username'], $gived['uid']);
		$gived['ugname'] = htmlspecialchars_uni($gived['ugname']);
		$gived['ugname'] = format_name($gived['ugname'], $gived['uguserg'], $gived['ugdisp']);
		$gived['ugname'] = build_profile_link($gived['ugname'], $gived['adduid']);		
		$gived['time'] = my_date('relative', $gived['time']);	
		$gived['thx'] = my_number_format($gived['thx']);
		$gived['thxcount'] = my_number_format($gived['thxcount']);
		$gived['uthx'] = my_number_format($gived['uthx']);
		$gived['uthxcount'] = my_number_format($gived['uthxcount']);		
		$gived['subject'] = htmlspecialchars_uni($gived['subject']);
		if(my_strlen($gived['subject']) > 25)
		{
			$gived['subject'] = my_substr($gived['subject'], 0, 25)."...";
		}	

		eval("\$users_list .= \"".$templates->get("thanks_results")."\";");
	}
	$db->free_result($query);

	if(!$users_list)
	{
		eval("\$users_list = \"".$templates->get("thanks_results_none")."\";");
	}

	eval("\$content = \"".$templates->get("thanks_content")."\";");
	eval("\$page = \"".$templates->get("thanks_page")."\";");

	output_page($page);
	exit;
}
else if($mybb->input['action'] == "search")
{
    if(!verify_post_check($mybb->input['my_post_key'])){
		error($lang->thx_cant_see);
	}
	
	if($mybb->input['fromid'] || $mybb->input['byid'] || $mybb->input['pid'])
	{
		// get forums user cannot view
		$unviewable = get_unviewable_forums(true);	
		if(isset($fids) && !empty($fids) && !empty($unviewable)){
		$unviewable .= "," . $fids;
		}	
		elseif(isset($fids) && empty($unviewable)){
		$unviewable .= $fids;
		}
		if($unviewable)
		{
			$unviewwhere = " AND p.fid NOT IN ($unviewable)";
		}	
		if(!$mybb->user['ismoderator'])
		{
			$unviewwhere .= " AND p.visible='1'";
		}
		
		$fromid = (int)$mybb->input['fromid'];
		$byid = (int)$mybb->input['byid'];
		$pid = (int)$mybb->input['pid'];
		if(!$mybb->input['page'])
		{
			if(empty($fromid) && empty($byid) && empty($pid))
			{
				error($lang->thx_error6);
			}
		}

		$extras = "";
		if(!empty($fromid))
		{
			$extras .= "&fromid=".(int)$fromid;
			$sql_wherex .= " AND t.adduid=".(int)$fromid;
		}	
		if(!empty($byid))
		{
			$extras .= "&byid=".(int)$byid;
			$sql_wherex .= " AND t.uid=".(int)$byid;
			
		}	
		if(!empty($pid))
		{
			$extras .= "&pid=".(int)$pid;
			$sql_wherex .= " AND t.pid=".(int)$pid;		
		}	

		$fromid = $db->escape_string($fromid);
		$byid = $db->escape_string($byid);
		$pid = $db->escape_string($pid);	
		
		$query = $db->query("SELECT t.*, p.fid, p.visible 
			FROM ".TABLE_PREFIX."thx t
			LEFT JOIN ".TABLE_PREFIX."posts p ON (t.pid=p.pid)	
			WHERE t.uid>0{$sql_wherex}{$unviewwhere} ORDER BY t.time"
		);

		$thx = $db->fetch_array($query);
		$db->free_result($query);

		$my_post_key = $mybb->input['my_post_key'];	
		add_breadcrumb($lang->thx_msg_title, THIS_SCRIPT."?action=search{$extras}&amp;my_post_key={$mybb->post_code}");
		
		$page = (int)$mybb->input['page'];
		if($page < 1) $page = 1;
		$numtot = $db->fetch_field($query = $db->query("SELECT COUNT(*) AS numtot
			FROM ".TABLE_PREFIX."thx t
			LEFT JOIN ".TABLE_PREFIX."posts p ON (t.pid=p.pid)	
			WHERE t.uid>0{$sql_wherex}{$unviewwhere} ORDER BY t.time"), "numtot");
		$perpage = 40;	
		$likes_founded = (int)$numtot;		
		$multipage = multipage($numtot, $perpage, $page, $_SERVER['PHP_SELF']."?action=search{$extras}&amp;my_post_key={$mybb->post_code}");
			
		$query = $db->query("
			SELECT t.*, p.subject, p.fid, p.visible, u.uid, u.username, u.usergroup, u.displaygroup, u.avatar, u.thx, u.thxcount, ug.username as ugname, ug.usergroup as uguserg, ug.displaygroup as ugdisp, ug.avatar as ugavatar, ug.thx as uthx, ug.thxcount as uthxcount
			FROM ".TABLE_PREFIX."thx t
			LEFT JOIN ".TABLE_PREFIX."users u ON (t.uid=u.uid)
			LEFT JOIN ".TABLE_PREFIX."users ug ON (t.adduid=ug.uid)	
			LEFT JOIN ".TABLE_PREFIX."posts p ON (t.pid=p.pid)	
			WHERE t.uid>0{$sql_wherex}{$unviewwhere} 
			ORDER BY t.time DESC
			LIMIT ".(($page-1)*$perpage).", {$perpage}		
		");
		$users_list = '';
		while($gived = $db->fetch_array($query))
		{
			$trow = alt_trow();
			if($mybb->user['usergroup'] == 4 || $mybb->user['uid'] == $mybb->input['thanked']){
			   $gived['txid'] = (int)$gived['txid'];
			}
			else{
			$gived['txid'] = " - ";
			}		
			$gived['pid'] = (int)$gived['pid'];
			$gived['url'] = htmlspecialchars_uni($mybb->settings['bburl'] . "/showthread.php?pid=" . $gived['pid'] . "#pid" . $gived['pid']);
			if($gived['avatar'] != ""){ 
				$gived['avatar'] = "<img src=".htmlspecialchars_uni($gived['avatar'])." class=\"thx_avatar\" alt=\"avatar\" />";
			}
			else{
				$gived['avatar'] = '<img src="images/default_avatar.png" class="thx_avatar" alt="no avatar" />';
			}
			if($gived['ugavatar'] != ""){ 		
				$gived['ugavatar'] = "<img src=".htmlspecialchars_uni($gived['ugavatar'])." class=\"thx_avatar\" alt=\"avatar\" />";		
			}
			else{
				$gived['ugavatar'] = '<img src="images/default_avatar.png" class="thx_avatar" alt="no avatar" />';
			}
			$gived['username'] = htmlspecialchars_uni($gived['username']);
			$gived['username'] = format_name($gived['username'], $gived['usergroup'], $gived['displaygroup']);
			$gived['username'] = build_profile_link($gived['username'], $gived['uid']);
			$gived['ugname'] = htmlspecialchars_uni($gived['ugname']);
			$gived['ugname'] = format_name($gived['ugname'], $gived['uguserg'], $gived['ugdisp']);
			$gived['ugname'] = build_profile_link($gived['ugname'], $gived['adduid']);		
			$gived['time'] = my_date('relative', $gived['time']);
			$gived['thx'] = my_number_format($gived['thx']);
			$gived['thxcount'] = my_number_format($gived['thxcount']);
			$gived['uthx'] = my_number_format($gived['uthx']);
			$gived['uthxcount'] = my_number_format($gived['uthxcount']);	
			$gived['subject'] = htmlspecialchars_uni($gived['subject']);
			if(my_strlen($gived['subject']) > 25)
			{
				$gived['subject'] = my_substr($gived['subject'], 0, 25)."...";
			}	
			
			eval("\$users_list .= \"".$templates->get("thanks_results")."\";");
		}
		$db->free_result($query);
		if(!$users_list)
		{
			$errors = "<div class=\"error\">{$lang->thx_error1}";
			if(empty($fromid))
				$errors .= "{$lang->thx_error2}";
			if(empty($byid))
				$errors .= "{$lang->thx_error3}";
			if(empty($pid))
				$errors .= "{$lang->thx_error4}";		
			$errors .= "{$lang->thx_error5}</div>";
			eval("\$users_list .= \"".$templates->get("thanks_results_none")."\";");
		}

		eval("\$content = \"".$templates->get("thanks_content")."\";");
		eval("\$page = \"".$templates->get("thanks_page")."\";");

		output_page($page);
		exit;		
	}
	else
	{
		// get forums user cannot view
		$unviewable = get_unviewable_forums(true);	
		if(isset($fids) && !empty($fids) && !empty($unviewable)){
		$unviewable .= "," . $fids;
		}	
		elseif(isset($fids) && empty($unviewable)){
		$unviewable .= $fids;
		}
		if($unviewable)
		{
			$unviewwhere = " AND p.fid NOT IN ($unviewable)";
		}	
		if(!$mybb->user['ismoderator'])
		{
			$unviewwhere .= " AND p.visible='1'";
		}
		
		$fromid = (int)$mybb->input['fromid'];
		$byid = (int)$mybb->input['byid'];
		$pid = (int)$mybb->input['pid'];
		if(!$mybb->input['page'])
		{
			if(empty($fromid) && empty($byid) && empty($pid))
			{
				error($lang->thx_error6);
			}
		}

		$extras = "";
		if(!empty($fromid))
		{
			$extras .= "&fromid={$fromid}";
			$sql_wherex .= " AND t.adduid=".(int)$fromid;
		}	
		if(!empty($byid))
		{
			$extras .= "&byid={$byid}";
			$sql_wherex .= " AND t.uid=".(int)$byid;
			
		}	
		if(!empty($pid))
		{
			$extras .= "&pid={$pid}";
			$sql_wherex .= " AND t.pid=".(int)$pid;		
		}	

		$fromid = $db->escape_string($mybb->input['fromid']);
		$byid = $db->escape_string($mybb->input['byid']);
		$pid = $db->escape_string($mybb->input['pid']);	
		
		$query = $db->query("SELECT t.*, p.fid, p.visible 
			FROM ".TABLE_PREFIX."thx t
			LEFT JOIN ".TABLE_PREFIX."posts p ON (t.pid=p.pid)	
			WHERE t.uid>0{$sql_wherex}{$unviewwhere} ORDER BY t.time"
		);

		$thx = $db->fetch_array($query);
		$db->free_result($query);

		$my_post_key = $mybb->input['my_post_key'];	
		add_breadcrumb($lang->thx_msg_title, THIS_SCRIPT."?action=search{$extras}&amp;my_post_key={$mybb->post_code}");
		
		$page = (int)$mybb->input['page'];
		if($page < 1) $page = 1;
		$numtot = $db->fetch_field($query = $db->query("SELECT COUNT(*) AS numtot
			FROM ".TABLE_PREFIX."thx t
			LEFT JOIN ".TABLE_PREFIX."posts p ON (t.pid=p.pid)	
			WHERE t.uid>0{$sql_wherex}{$unviewwhere} ORDER BY t.time"), "numtot");
		$perpage = 40;	
		$likes_founded = (int)$numtot;		
		$multipage = multipage($numtot, $perpage, $page, $_SERVER['PHP_SELF']."?action=search{$extras}&amp;my_post_key={$mybb->post_code}");
			
		$query = $db->query("
			SELECT t.*, p.subject, p.fid, p.visible, u.uid, u.username, u.usergroup, u.displaygroup, u.avatar, u.thx, u.thxcount, ug.username as ugname, ug.usergroup as uguserg, ug.displaygroup as ugdisp, ug.avatar as ugavatar, ug.thx as uthx, ug.thxcount as uthxcount
			FROM ".TABLE_PREFIX."thx t
			LEFT JOIN ".TABLE_PREFIX."users u ON (t.uid=u.uid)
			LEFT JOIN ".TABLE_PREFIX."users ug ON (t.adduid=ug.uid)	
			LEFT JOIN ".TABLE_PREFIX."posts p ON (t.pid=p.pid)	
			WHERE t.uid>0{$sql_wherex}{$unviewwhere} 
			ORDER BY t.time DESC
			LIMIT ".(($page-1)*$perpage).", {$perpage}		
		");
		$users_list = '';
		while($gived = $db->fetch_array($query))
		{
			$trow = alt_trow();
			if($mybb->user['usergroup'] == 4 || $mybb->user['uid'] == $mybb->input['thanked']){
			   $gived['txid'] = (int)$gived['txid'];
			}
			else{
			$gived['txid'] = " - ";
			}		
			$gived['pid'] = (int)$gived['pid'];
			$gived['url'] = htmlspecialchars_uni($mybb->settings['bburl'] . "/showthread.php?pid=" . $gived['pid'] . "#pid" . $gived['pid']);
			if($gived['avatar'] != ""){ 
				$gived['avatar'] = "<img src=".htmlspecialchars_uni($gived['avatar'])." class=\"thx_avatar\" alt=\"avatar\" />";
			}
			else{
				$gived['avatar'] = '<img src="images/default_avatar.png" class="thx_avatar" alt="no avatar" />';
			}
			if($gived['ugavatar'] != ""){ 		
				$gived['ugavatar'] = "<img src=".htmlspecialchars_uni($gived['ugavatar'])." class=\"thx_avatar\" alt=\"avatar\" />";		
			}
			else{
				$gived['ugavatar'] = '<img src="images/default_avatar.png" class="thx_avatar" alt="no avatar" />';
			}
			$gived['username'] = htmlspecialchars_uni($gived['username']);
			$gived['username'] = format_name($gived['username'], $gived['usergroup'], $gived['displaygroup']);
			$gived['username'] = build_profile_link($gived['username'], $gived['uid']);
			$gived['ugname'] = htmlspecialchars_uni($gived['ugname']);
			$gived['ugname'] = format_name($gived['ugname'], $gived['uguserg'], $gived['ugdisp']);
			$gived['ugname'] = build_profile_link($gived['ugname'], $gived['adduid']);		
			$gived['time'] = my_date('relative', $gived['time']);	
			$gived['thx'] = my_number_format($gived['thx']);
			$gived['thxcount'] = my_number_format($gived['thxcount']);
			$gived['uthx'] = my_number_format($gived['uthx']);
			$gived['uthxcount'] = my_number_format($gived['uthxcount']);	
			$gived['subject'] = htmlspecialchars_uni($gived['subject']);
			if(my_strlen($gived['subject']) > 25)
			{
				$gived['subject'] = my_substr($gived['subject'], 0, 25)."...";
			}	
			
			eval("\$users_list .= \"".$templates->get("thanks_results")."\";");
		}
		$db->free_result($query);
		if(!$users_list)
		{
			$errors = "<div class=\"error\">{$lang->thx_error1}";
			if(empty($fromid))
				$errors .= "{$lang->thx_error2}";
			if(empty($byid))
				$errors .= "{$lang->thx_error3}";
			if(empty($pid))
				$errors .= "{$lang->thx_error4}";		
			$errors .= "{$lang->thx_error5}</div>";
		}

		eval("\$content = \"".$templates->get("thanks_content")."\";");
		eval("\$page = \"".$templates->get("thanks_page")."\";");

		output_page($page);
		exit;			
	}
}
else
{
$unviewable = get_unviewable_forums(true);	
if(isset($fids) && !empty($fids) && !empty($unviewable)){
$unviewable .= "," . $fids;
}	
elseif(isset($fids) && empty($unviewable)){
$unviewable .= $fids;
}
if($unviewable)
{
	$unviewwhere = " AND p.fid NOT IN ($unviewable)";
}	
if(!$mybb->user['ismoderator'])
{
	$unviewwhere .= " AND p.visible='1'";
}

$query = $db->query("SELECT t.*,p.fid, p.visible 
	FROM ".TABLE_PREFIX."thx t
	LEFT JOIN ".TABLE_PREFIX."posts p ON (t.pid=p.pid)	
	WHERE t.uid>0{$unviewwhere} ORDER BY t.time"
);

$thx = $db->fetch_array($query);
$db->free_result($query);

$my_post_key = $mybb->input['my_post_key'];
add_breadcrumb($lang->thx_msg_title, THIS_SCRIPT);
	
$page = (int)$mybb->input['page'];
if($page < 1) $page = 1;
$numtot = $db->fetch_field($query = $db->query("SELECT COUNT(*) AS numtot
	FROM ".TABLE_PREFIX."thx t
	LEFT JOIN ".TABLE_PREFIX."posts p ON (t.pid=p.pid)	
	WHERE t.uid>0{$unviewwhere} ORDER BY t.time"), "numtot");
$perpage = 40;
$likes_founded = (int)$numtot;	
$multipage = multipage($numtot, $perpage, $page, $_SERVER['PHP_SELF']);
	
$query = $db->query("
	SELECT t.*, p.subject, p.fid, p.visible, u.uid, u.username, u.usergroup, u.displaygroup, u.avatar, u.thx, u.thxcount, ug.username as ugname, ug.usergroup as uguserg, ug.displaygroup as ugdisp, ug.avatar as ugavatar, ug.thx as uthx, ug.thxcount as uthxcount
	FROM ".TABLE_PREFIX."thx t
	LEFT JOIN ".TABLE_PREFIX."users u ON (t.uid=u.uid)
	LEFT JOIN ".TABLE_PREFIX."users ug ON (t.adduid=ug.uid)	
	LEFT JOIN ".TABLE_PREFIX."posts p ON (t.pid=p.pid)	
	WHERE t.uid>0{$unviewwhere} 
	ORDER BY t.time DESC
	LIMIT ".(($page-1)*$perpage).", {$perpage}		
");
$users_list = '';
while($gived = $db->fetch_array($query))
{
	$trow = alt_trow();
	if($mybb->user['usergroup'] == 4 || $mybb->user['uid'] == $mybb->input['thanked']){
       $gived['txid'] = (int)$gived['txid'];
	}
	else{
	$gived['txid'] = " - ";
	}		
    $gived['pid'] = (int)$gived['pid'];
	$gived['url'] = htmlspecialchars_uni($mybb->settings['bburl'] . "/showthread.php?pid=" . $gived['pid'] . "#pid" . $gived['pid']);
	if($gived['avatar'] != ""){ 
		$gived['avatar'] = "<img src=".htmlspecialchars_uni($gived['avatar'])." class=\"thx_avatar\" alt=\"avatar\" />";
	}
	else{
		$gived['avatar'] = '<img src="images/default_avatar.png" class="thx_avatar" alt="no avatar" />';
	}
	if($gived['ugavatar'] != ""){ 		
		$gived['ugavatar'] = "<img src=".htmlspecialchars_uni($gived['ugavatar'])." class=\"thx_avatar\" alt=\"avatar\" />";		
	}
	else{
		$gived['ugavatar'] = '<img src="images/default_avatar.png" class="thx_avatar" alt="no avatar" />';
	}
	$gived['username'] = htmlspecialchars_uni($gived['username']);
	$gived['username'] = format_name($gived['username'], $gived['usergroup'], $gived['displaygroup']);
	$gived['username'] = build_profile_link($gived['username'], $gived['uid']);
	$gived['ugname'] = htmlspecialchars_uni($gived['ugname']);
	$gived['ugname'] = format_name($gived['ugname'], $gived['uguserg'], $gived['ugdisp']);
	$gived['ugname'] = build_profile_link($gived['ugname'], $gived['adduid']);		
	$gived['time'] = my_date('relative', $gived['time']);
	$gived['thx'] = my_number_format($gived['thx']);
	$gived['thxcount'] = my_number_format($gived['thxcount']);
	$gived['uthx'] = my_number_format($gived['uthx']);
	$gived['uthxcount'] = my_number_format($gived['uthxcount']);	
	$gived['subject'] = htmlspecialchars_uni($gived['subject']);
	if(my_strlen($gived['subject']) > 25)
	{
		$gived['subject'] = my_substr($gived['subject'], 0, 25)."...";
	}	
	
	eval("\$users_list .= \"".$templates->get("thanks_results")."\";");
}
$db->free_result($query);
if(!$users_list)
{
	eval("\$users_list = \"".$templates->get("thanks_results_none")."\";");
}

eval("\$content = \"".$templates->get("thanks_content")."\";");
eval("\$page = \"".$templates->get("thanks_page")."\";");

output_page($page);
exit;	
}