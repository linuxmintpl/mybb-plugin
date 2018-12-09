<?php
/*
 *
 * Plugin lightIRC
 * (c) 2012 - 2015 by CrazyCat
 * Website: http://ab-plugin.cc/ABP-lightIRC-chat-t-1.html
 * For more infos about lightIRC : http://www.lightirc.com
 */

define("IN_MYBB", 1);
define("KILL_GLOBALS", 1);

require "global.php";

if(!isset($mybb->settings['lightirc_mybb_onlymembers']))
{
	error("You have to activate the plugin!");
}

if(empty($mybb->settings['lightirc_mybb_server']) || empty($mybb->settings['lightirc_mybb_channel']))
{
	error("You have to enter the server and the channel you want to connect with!");
}

// Navigation
add_breadcrumb("Chat");

if($mybb->settings['lightirc_mybb_onlymembers'] == "yes")
{
    if ($mybb->user['uid'] == "0")
    {
		error_no_permission();
    }
}

if($mybb->user['uid'] == "0")
{
	$rand = rand(1000,9999);
	$username = "Guest".$rand."";
	$altusername = "Guest".$rand."";
}
else
{
	$username = $mybb->user['username'];
	$altusername = "Member".$mybb->user['uid']."";
}

$lircpath = $mybb->settings['lightirc_mybb_path'];
$ircserver = $mybb->settings['lightirc_mybb_server'];
$ircport = $mybb->settings['lightirc_mybb_port'];
$ircchannel = $mybb->settings['lightirc_mybb_channel'];
$irclang = $mybb->settings['lightirc_mybb_language'];
$ircstyle = $mybb->settings['lightirc_mybb_style'];
if ($ircstyle == '' || $ircstyle == 'default')
{
	$ircstyle = '';
}
else
{
	$ircstyle = ', styleURL:"'.$mybb->settings['bburl'].'/'.$lircpath.'/css/'.$ircstyle.'.css"';
}

// Output
eval("\$chat = \"".$templates->get("lightirc_mybb_chat")."\";");
output_page($chat);
?>