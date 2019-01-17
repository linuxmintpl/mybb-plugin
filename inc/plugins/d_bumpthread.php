<?php
// Disallow direct access to this file for security reasons
if (!defined('IN_MYBB')) {
	die('Direct initialization of this file is not allowed.');
}

$plugins->add_hook('datahandler_post_insert_post', 'd_bumpthread_newpost');
$plugins->add_hook('datahandler_post_insert_thread', 'd_bumpthread_newthread');
$plugins->add_hook('showthread_start', 'd_bumpthread');

function d_bumpthread_info()
{
	global $lang;
	$lang->load('d_bumpthread');

	return [
		'name' => $lang->bumpthread,
		'description' => sprintf('%s<br />This plugin is based on old \'Bump Thread\' plugin by ZiNgA BuRgA.', $lang->bumpthread_description),
		'website' => '',
		'author' => '-n3veR',
		'authorsite' => 'http://dziubczynski.pl/',
		'version' => '2.0',
		'compatibility' => '18*',
	];
}

function d_bumpthread_activate()
{
	global $db, $lang;
	$lang->load('d_bumpthread');

	require MYBB_ROOT . "/inc/adminfunctions_templates.php";
	find_replace_templatesets('showthread', sprintf('#%s#', preg_quote('{$newreply}')), '{$newreply}{$bumpthread}');

	$new_template = '<a href="showthread.php?tid={$tid}&amp;action=bump" title="{$lang->bumpthread_bump_title}" class="button button-bump"><span>{$lang->bumpthread_bump}</span></a>';

	$template = [
		'title' => 'd_bumpthread_template',
		'template' => $db->escape_string($new_template),
		'sid' => -1,
		'version' => 120,
	];
	$db->insert_query('templates', $template);
	
	// add settings
	$disporder = $db->fetch_field($db->simple_select('settinggroups', 'MAX(disporder) AS disporder'), 'disporder') + 1;
	$settinggroup = [
		'name' => 'd_bumpthread',
		'title' => $db->escape_string($lang->bumpthread_settinggroups_title),
		'disporder' => $disporder,
	];
	$db->insert_query('settinggroups', $settinggroup);

	$settings = [];

	array_push($settings, [
		'name' => 'd_bumpthread_time',
		'optionscode' => 'numeric',
		'value' => 30,
		'title' => $db->escape_string($lang->bumpthread_setting_time_title),
		'description' => $db->escape_string($lang->bumpthread_setting_time_description),
		'disporder' => 1,
		'gid' => $db->insert_id(),
	]);

	array_push($settings, [
		'name' => 'd_bumpthread_time_type',
		'optionscode' => sprintf('select %s', interval_types_acp()),
		'value' => 60,
		'title' => $db->escape_string($lang->bumpthread_setting_time_type_title),
		'description' => $db->escape_string($lang->bumpthread_setting_time_type_description),
		'disporder' => 2,
		'gid' => $db->insert_id(),
	]);

	array_push($settings, [
		'name' => 'd_bumpthread_forums',
		'optionscode' => 'forumselect',
		'value' => '-1',
		'title' => $db->escape_string($lang->bumpthread_setting_forums_title),
		'description' => $db->escape_string($lang->bumpthread_setting_forums_description),
		'disporder' => 3,
		'gid' => $db->insert_id(),
	]);

	$db->insert_query_multiple('settings', $settings);

	rebuild_settings();
}

function d_bumpthread_deactivate()
{
	global $db;

	require MYBB_ROOT . "/inc/adminfunctions_templates.php";
	find_replace_templatesets('showthread', sprintf('#%s#', preg_quote('{$bumpthread}')), '', 0);

	$db->delete_query('templates', 'title = "d_bumpthread_template"');

	$gid = $db->fetch_field($db->simple_select('settinggroups', 'gid', 'name = "d_bumpthread"'), 'gid');

	if ($gid) {
		$db->delete_query('settings', "gid = '{$gid}'");
		$db->delete_query('settinggroups', "gid = '{$gid}'");
	}

	rebuild_settings();
}

function d_bumpthread_newpost(&$ph)
{
	global $db;

	$db->update_query('threads', ['lastpost' => time()], "tid = '{$ph->data['tid']}'");
}

function d_bumpthread_newthread(&$ph)
{
	$ph->thread_insert_data['lastpost'] = $ph->data['dateline'];
}

function interval_types()
{
	global $db, $lang;
	$lang->load('d_bumpthread');

	return [
		60 => $db->escape_string($lang->bumpthread_interval_type_minute),
		3600 => $db->escape_string($lang->bumpthread_interval_type_hour),
		86400 => $db->escape_string($lang->bumpthread_interval_type_day),
	];
}

function interval_types_acp()
{
	$interval_types = interval_types();
	$arr = [];

	foreach ($interval_types as $key => $val) {
		array_push($arr, sprintf('\n%s=%s', $key, $val));
	}

	return implode('', $arr);
}

function d_bumpthread()
{
	global $mybb, $thread, $lang;
	$lang->load('d_bumpthread');

	$forums_allowed = $mybb->settings['d_bumpthread_forums'];

	if (!empty($forums_allowed)) {
		$fid = $thread['fid'];

		if ($forums_allowed === '-1') {
			$forums_allowed = $fid;
		}

		if (in_array($fid, explode(',', $forums_allowed))) {
			$interval = intval($mybb->settings['d_bumpthread_time']);
			$interval_type = intval($mybb->settings['d_bumpthread_time_type']);
			$bump = $thread['lastpost'] + ($interval * $interval_type);

			switch ($mybb->input['action']) {
				case 'bump':
					$interval_types = interval_types();
					d_bumpthread_run($bump, $interval, $interval_types[$interval_type]);
					break;
				default:
					d_bumpthread_show_button($bump);
			}
		} else {
			error('');
		}
	} else {
		error('');
	}
}

function d_bumpthread_run($bump, $interval, $interval_type)
{
	global $mybb, $db, $thread, $lang;
	$lang->load('d_bumpthread');

	$time = time();
	$tid = $thread['tid'];

	if (!$mybb->usergroup['cancp'] or !$mybb->usergroup['issupermod'] or $thread['uid'] != $mybb->user['uid']) {
		error_no_permission();
	}

	if ($bump > $time) {
		error($lang->sprintf($lang->bumpthread_interval_error, $interval, $interval_type));
	}

	$db->update_query('threads', ['lastpost' => $time], "tid={$tid}");
	redirect("showthread.php?tid={$tid}", $lang->bumpthread_bumped);
}

function d_bumpthread_show_button($bump)
{
	global $bumpthread, $templates, $tid, $lang;
	$lang->load('d_bumpthread');

	$time = time();

	if ($bump <= $time) {
		if ($mybb->usergroup['cancp'] or $mybb->usergroup['issupermod'] or $thread['uid'] == $mybb->user['uid']) {
			eval('$bumpthread = "' . $templates->get('d_bumpthread_template') . '";');
		}
	}
} 
