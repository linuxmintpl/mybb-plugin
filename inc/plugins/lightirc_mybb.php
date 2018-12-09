<?php
/*
 *
 * Plugin lightIRC
 * (c) 2012 - 2016 by CrazyCat
 * Website: http://ab-plugin.cc/ABP-lightIRC-chat-t-1.html
 * For more infos about lightIRC : http://www.lightirc.com
 */

define('CN_ABPLIM', 'lightirc_mybb');

function lightirc_mybb_info()
{
	global $lang;
	$lang->load(CN_ABPLIM);
	return array(
		"name"          => "MyBB lightIRC Chat",
		"description"   => $lang->lightirc_desc,
		"website"       => "http://ab-plugin.cc/ABP-lightIRC-chat-t-1.html",
		"author"        => "CrazyCat",
		"authorsite"    => "http://ab-plugin.cc",
		"version"       => "2.2",
		"compatibility" => '18*',
		"codename"	=> CN_ABPLIM,
	);
}

$plugins->add_hook('global_start', 'lightirc_mybb');

function lightirc_mybb_install()
{
	global $db, $lang;
	$settinggroups = array(
		'name' => CN_ABPLIM,
		'title' => 'lightIRC Chat',
		'description' => $lang->lightirc_sgtdesc,
		'disporder' => 1,
		'isdefault' => 0,
		);
	$db->insert_query('settinggroups', $settinggroups);
	$gid = $db->insert_id();

	$settings[] = array(
		'name' => CN_ABPLIM.'_path',
		'title' => $lang->lightirc_pathtitle,
		'description' => $lang->lightirc_pathdesc,
		'optionscode' => 'text',
		'value' => 'lightIRC',
		'disporder' => 1,
		'gid' => $gid,
	);
	
        $settings[] = array(
		'name' => CN_ABPLIM.'_onlymembers',
		'title' => $lang->lightirc_omtitle,
		'description' => $lang->lightirc_omdesc,
		'optionscode' => 'yesno',
		'value' => 'yes',
		'disporder' => 2,
		'gid' => $gid,
	);
        
	$settings[] = array(
		'name' => CN_ABPLIM.'_server',
		'title' => $lang->lightirc_servertitle,
		'description' => $lang->lightirc_serverdesc,
		'optionscode' => 'text',
		'value' => 'irc.freenode.net',
		'disporder' => 3,
		'gid' => $gid,
	);
	
	$settings[] = array(
		'name' => CN_ABPLIM.'_port',
		'title' => $lang->lightirc_porttitle,
		'description' => $lang->lightirc_portdesc,
		'optionscode' => 'numeric',
		'value' => '6667',
		'disporder' => 4,
		'gid' => $gid,
	);
	
	$settings[] = array(
		'name' => CN_ABPLIM.'_channel',
		'title' => $lang->lightirc_chantitle,
		'description' => $lang->lightirc_chandesc,
		'optionscode' => 'text',
		'value' => '#mybb',
		'disporder' => 5,
		'gid' => $gid,
	);
	
	$settings[] = array(
		'name' => CN_ABPLIM.'_language',
		'title' => CN_ABPLIM.'_langtitle',
		'description' => CN_ABPLIM.'_langdesc',
		'optionscode' => 'select
ar='.$lang->lightirc_ar.'
bd='.$lang->lightirc_bd.'
bg='.$lang->lightirc_bg.'
br='.$lang->lightirc_br.'
cz='.$lang->lightirc_cz.'
da='.$lang->lightirc_da.'
de='.$lang->lightirc_de.'
el='.$lang->lightirc_el.'
en='.$lang->lightirc_en.'
es='.$lang->lightirc_es.'
et='.$lang->lightirc_et.'
fi='.$lang->lightirc_fi.'
fr='.$lang->lightirc_fr.'
hu='.$lang->lightirc_hu.'
hr='.$lang->lightirc_hr.'
id='.$lang->lightirc_id.'
it='.$lang->lightirc_it.'
ja='.$lang->lightirc_ja.'
lv='.$lang->lightirc_lv.'
nl='.$lang->lightirc_nl.'
pl='.$lang->lightirc_pl.'
pt='.$lang->lightirc_pt.'
ro='.$lang->lightirc_ro.'
ru='.$lang->lightirc_ru.'
sk='.$lang->lightirc_sk.'
sl='.$lang->lightirc_sl.'
sq='.$lang->lightirc_sq.'
sr_cyr='.$lang->lightirc_sr_cyr.'
sr_lat='.$lang->lightirc_sr_lat.'
sv='.$lang->lightirc_sv.'
th='.$lang->lightirc_th.'
tr='.$lang->lightirc_tr.'
uk='.$lang->lightirc_uk,
		'value' => 'en',
		'disporder' => 6,
		'gid' => $gid
	);
	
	$settings[] = array(
		'name' => CN_ABPLIM.'_style',
		'title' => $lang->lightirc_coltitle,
		'description' => $lang->lightirc_coldesc,
		'optionscode' => 'select
default='.$lang->lightirc_default.'
black='.$lang->lightirc_black.'
blue='.$lang->lightirc_blue.'
darkorange='.$lang->lightirc_darkorange.'
green='.$lang->lightirc_green.'
lightblue='.$lang->lightirc_lightblue.'
yellow='.$lang->lightirc_yellow,
		'value' => 'default',
		'disporder' => 7,
		'gid' => $gid
	);
	$db->insert_query_multiple('settings', $settings);
	rebuild_settings();
}

function lightirc_mybb_uninstall()
{
	global $db;
	lightirc_mybb_deactivate();
	$db->delete_query('settings', "name like '".CN_ABPLIM."_%'");
	$db->delete_query('settinggroups', "name = '".CN_ABPLIM."'");
	rebuild_settings();
}

function lightirc_mybb_is_installed()
{
	global $db;
	$installtest = $db->simple_select("settinggroups", "count(*) as nb", "name = '".CN_ABPLIM."'");
	$nb = $db->fetch_field($installtest, 'nb');
	if ($nb > 0)
	{
		return true;
	}
	return false;
}

function lightirc_mybb_activate()
{
    global $db;
    require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';
	
    find_replace_templatesets("header", '#menu_calendar}#', "menu_calendar}
{\$lightirc_menu}");

	$templatepage = array(
		'title' => CN_ABPLIM.'_chat',
		'template' => "<html>
<head>
<title>\$settings[bbname] - Chat</title>
\$headerinclude
<script language=\"javascript\" src=\"http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\" src=\"./\$settings[lightirc_mybb_path]/config.js\"></script>
</head>
<body>
\$header
<!-- Applet Start -->
<br /><div style=\"text-align: center;\">

<div id=\"lightIRC\" style=\"height:500px; text-align:center;\"><p><a href=\"http://www.adobe.com/go/getflashplayer\" title=\"Get flash player\"><img src=\"http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif\" alt=\"Get Adobe Flash player\" /></a></p></div>
	<script type=\"text/javascript\">
    var params = {
		host:\"\$ircserver\",
		port:\"\$ircport\",
		languagePath:\"\$settings[bburl]/\$settings[lightirc_mybb_path]/language/\",
		language:\"\$irclang\",
		nickselect:\"no\",
		nick:\"\$username\",
		rememberNickname:\"yes\",
		ident:\"\$username\",
		nickServAuth:\"yes\",
		autojoin:\"\$ircchannel\",
		emoticonPath:\"\$settings[bburl]/\$settings[lightirc_mybb_path]/emoticons/\"
		\$ircstyle};
	swfobject.embedSWF(\"./\$settings[lightirc_mybb_path]/lightIRC.swf\", \"lightIRC\", \"100%\", \"500\", \"10.0.0\", \"expressInstall.swf\", params, null);
	</script>
<!-- Applet End -->
<div class=\"smalltext\">lightIRC Plugin ".$info['version']." by <a href=\"".$info['website']."\" target=\"_blank\">CrazyCat</a></div>
</div>
\$footer
</body>
</html>",
		'sid' => -1,
		'version' => $info['intver'],
		'dateline' => TIME_NOW
	);
	$db->insert_query('templates', $templatepage);
		
	$templatemenu = array(
		'title' => CN_ABPLIM.'_menu',
		'template' => "
		<li><a href=\"{\$mybb->settings[''bburl'']}/chat.php\" style=\"background:url({\$mybb->settings[''bburl'']}/images/chat.gif);background-repeat:no-repeat;display:inline-block;padding-left:20px;\">Chat</a></li>",
		'sid' => -1,
		'version' => $info['intver'],
		'dateline' => TIME_NOW
	);
	$db->insert_query("templates", $templatemenu);
}

function lightirc_mybb_deactivate()
{
    global $db;
    require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("header", '#\{\$lightirc_menu\}#', "", 0);
	$db->delete_query('templates', "title LIKE '".CN_ABPLIM."_%'");
}

function lightirc_mybb()
{
	global $db, $mybb, $templates, $templatelist,$lightirc_menu;
	$templatelist .= ',lightirc_mybb_menu';
	eval("\$lightirc_menu = \"".$templates->get("lightirc_mybb_menu")."\";"); 
}

$plugins->add_hook('fetch_wol_activity_end', 'lightirc_mybb_wol');
$plugins->add_hook('build_friendly_wol_location_end', 'lightirc_mybb_build_wol');

function lightirc_mybb_wol(&$user_activity)
{
	global $user, $mybb;
	if(my_strpos($user['location'], 'chat.php') !== false)
	{
		$user_activity['activity'] = 'lightirc';
	}
}

function lightirc_mybb_build_wol(&$plugin_array)
{
	global $mybb, $lang;
	if($plugin_array['user_activity']['activity'] == 'lightirc')
	{
		$lang->load('admin/'.CN_ABPLIM);
		$plugin_array['location_name'] = $lang->sprintf($lang->lightirc_wol, 'chat.php');
	}
}


?>
