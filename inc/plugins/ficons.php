<?php

if(!defined('IN_MYBB')) {
	die('This file cannot be accessed directly.');
}

if(defined('IN_ADMINCP')) {
    $plugins->add_hook("admin_forum_menu", "ficons_sub_menu");
    $plugins->add_hook("admin_forum_action_handler", "ficons_action");
} else {
    $plugins->add_hook("build_forumbits_forum", "ficons_show");
}

function ficons_info() {
    return array(
        'name'			=> 'Własne ikony działów',
        'description'	=> 'Plugin pozwala na ustawienie ikony działu w postaci obrazka.',
        'website'		=> '',
        'author' 		=> '<a href="http://chack1172.altervista.org/">chack1172</a>. Spolszczenie: <a href="https://www.pecetowicz.pl/profil/tomasz-36285/">Tomik</a>.',
        'authorsite' 	=> '',
        'version' 		=> '4.5',
        'compatibility' => '18*',
        'codename' 		=> 'ficons'
    );
}

function ficons_install() {
    global $db, $mybb;
    
    $collation = $db->build_create_table_collation();

	if(!$db->table_exists("forum_icons")) {
		$db->write_query("CREATE TABLE `".TABLE_PREFIX."forum_icons` (
			`id` int(10) UNSIGNED NOT NULL auto_increment,
			`fid` int(10) UNSIGNED NOT NULL,
            `image` varchar(500) NOT NULL default '',
			PRIMARY KEY  (`id`)
		) ENGINE=MyISAM{$collation}");
	}
    
    $template = '<img src="{$forum_icon}" alt="" style="float: left; max-width: 200px; max-height: 150px;padding-right: 10px">';

    $insert_array = array(
        'title' => 'forum_icons',
        'template' => $db->escape_string($template),
        'sid' => '-1',
        'version' => '',
        'dateline' => time()
    );

    $db->insert_query('templates', $insert_array);
    
    $setting_group = array(
        'name' => 'ficons',
        'title' => 'Własne ikony działów',
        'description' => 'Plugin pozwala na ustawienie ikony działu w postaci obrazka.',
        'disporder' => 5,
        'isdefault' => 0
    );

    $gid = $db->insert_query("settinggroups", $setting_group);
    
    $setting_array = array(
        'ficons_visible' => array(
            'title' => 'Pokazać ikony?',
            'description' => '',
            'optionscode' => 'yesno',
            'value' => 1,
            'disporder' => 1
        ),
    );
    
    
    foreach($setting_array as $name => $setting) {
        $setting['name'] = $name;
        $setting['gid'] = $gid;

        $db->insert_query('settings', $setting);
    }
    
    rebuild_settings();
}

function ficons_is_installed() {
    global $db;
    
    return $db->table_exists("forum_icons");
}

function ficons_uninstall() {
    global $db;

    $db->drop_table("forum_icons");
    $db->delete_query("templates", "title = 'forum_icons'");
    
    $db->delete_query('settings', "name = 'ficons_visible'");
    $db->delete_query('settinggroups', "name = 'ficons'");

    rebuild_settings();
}

function ficons_activate() {
    global $mybb;
    
    require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
    
    find_replace_templatesets(
        "forumbit_depth2_forum",
        "#" . preg_quote('{$forum[\'name\']}') . "#i",
        '{$forum[\'icon\']}{$forum[\'name\']}'
    );
}

function ficons_deactivate() {
    require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
    
    find_replace_templatesets(
        "forumbit_depth2_forum",
        "#" . preg_quote('{$forum[\'icon\']}') . "#i",
        ''
    );
}

function ficons_sub_menu(&$sub_menu) {
    global $lang;
    
    $lang->load("forum_icons");
    
    $sub_menu[] = array("id" => "icons", "title" => $lang->ficons_title, "link" => "index.php?module=forum-icons");   
}

function ficons_action(&$actions) {
    $actions['icons'] = array('active' => 'icons', 'file' => 'icons.php');
}

function ficons_show(&$forum) {
    global $db, $templates, $forum_url, $mybb;
    
    if($mybb->settings['ficons_visible'] == 1) {
        $forum_icons = $db->simple_select("forum_icons", "image", "fid={$forum['fid']}");
        $forum_icon = $db->fetch_field($forum_icons, "image");
        if(!empty($forum_icon)) {
            eval("\$forum['icon'] = \"".$templates->get("forum_icons")."\";");
        }
    }
}