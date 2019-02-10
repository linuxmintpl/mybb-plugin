<?php
/*
 * MyBB: [jQuery] Appearing Scroll to Top
 *
 * File: totop.php
 * 
 * Authors: Vintagedaddyo, effone
 *
 * MyBB Version: 1.8
 *
 * Plugin Version: 1.1
 * 
 */

// Disallow direct access to this file for security reasons

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("pre_output_page","totop");

function totop_info()
{
    global $lang;

    $lang->load("totop");
    
    $lang->totop_Desc = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float:right;">' .
        '<input type="hidden" name="cmd" value="_s-xclick">' . 
        '<input type="hidden" name="hosted_button_id" value="AZE6ZNZPBPVUL">' .
        '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">' .
        '<img alt="" border="0" src="https://www.paypalobjects.com/pl_PL/i/scr/pixel.gif" width="1" height="1">' .
        '</form>' . $lang->totop_Desc;

    return Array(
        'name' => $lang->totop_Name,
        'description' => $lang->totop_Desc,
        'website' => $lang->totop_Web,
        'author' => $lang->totop_Auth,
        'authorsite' => $lang->totop_AuthSite,
        'version' => $lang->totop_Ver,
        'compatibility' => $lang->totop_Compat
    );
}

// Activate

function totop_activate() {

global $db, $lang;

    $lang->load("totop");

$totop_group = array(
        'gid'    => '0',
        'name'  => 'totop',
        'title'      => $lang->totop_settings_Title,
        'description'    => $lang->totop_settings_Description,
        'disporder'    => "1",
        'isdefault'  => "0",
    );

$db->insert_query('settinggroups', $totop_group);

 $gid = $db->insert_id();

$totop_setting_1 = array(
        'sid'            => '0',
        'name'        => 'totop_enable',
        'title'            => $lang->totop_option_1_Title,
        'description'    => $lang->totop_option_1_Description,
        'optionscode'    => 'yesno',
        'value'        => '1',
        'disporder'        => 1,
        'gid'            => intval($gid),
    );

$totop_setting_2 = array(
        'sid'            => '0',
        'name'        => 'totop_enable_index',
        'title'            => $lang->totop_option_2_Title,
        'description'    => $lang->totop_option_2_Description,
        'optionscode'    => 'yesno',
        'value'        => '1',
        'disporder'        => 2,
        'gid'            => intval($gid),
    );

$totop_setting_3 = array(
        'sid'            => '0',
        'name'        => 'totop_enable_forumdisplay',
        'title'            => $lang->totop_option_3_Title,
        'description'    => $lang->totop_option_3_Description,
        'optionscode'    => 'yesno',
        'value'        => '1',
        'disporder'        => 3,
        'gid'            => intval($gid),
    );

$totop_setting_4 = array(
        'sid'            => '0',
        'name'        => 'totop_enable_showthread',
        'title'            => $lang->totop_option_4_Title,
        'description'    => $lang->totop_option_4_Description,
        'optionscode'    => 'yesno',
        'value'        => '1',
        'disporder'        => 4,
        'gid'            => intval($gid),
    );

$totop_setting_5 = array(
        'sid'            => '0',
        'name'        => 'totop_enable_memberlist',
        'title'            => $lang->totop_option_5_Title,
        'description'    => $lang->totop_option_5_Description,
        'optionscode'    => 'yesno',
        'value'        => '1',
        'disporder'        => 5,
        'gid'            => intval($gid),
    );

$totop_setting_6 = array(
        'sid'            => '0',
        'name'        => 'totop_enable_member',
        'title'            => $lang->totop_option_6_Title,
        'description'    => $lang->totop_option_6_Description,
        'optionscode'    => 'yesno',
        'value'        => '1',
        'disporder'        => 6,
        'gid'            => intval($gid),
    );

$totop_setting_7 = array(
        'sid'            => '0',
        'name'        => 'totop_enable_misc',
        'title'            => $lang->totop_option_7_Title,
        'description'    => $lang->totop_option_7_Description,
        'optionscode'    => 'yesno',
        'value'        => '1',
        'disporder'        => 7,
        'gid'            => intval($gid),
    );

$totop_setting_8 = array(
        'sid'            => '0',
        'name'        => 'totop_enable_search',
        'title'            => $lang->totop_option_8_Title,
        'description'    => $lang->totop_option_8_Description,
        'optionscode'    => 'yesno',
        'value'        => '1',
        'disporder'        => 8,
        'gid'            => intval($gid),
    );

$totop_setting_9 = array(
        'sid'            => '0',
        'name'        => 'totop_enable_modcp',
        'title'            => $lang->totop_option_9_Title,
        'description'    => $lang->totop_option_9_Description,
        'optionscode'    => 'yesno',
        'value'        => '1',
        'disporder'        => 9,
        'gid'            => intval($gid),
    );

$totop_setting_10 = array(
        'sid'            => '0',
        'name'        => 'totop_enable_usercp',
        'title'            => $lang->totop_option_10_Title,
        'description'    => $lang->totop_option_10_Description,
        'optionscode'    => 'yesno',
        'value'        => '1',
        'disporder'        => 10,
        'gid'            => intval($gid),
    );

$totop_setting_11 = array(
        'sid'            => '0',
        'name'        => 'totop_enable_private',
        'title'            => $lang->totop_option_11_Title,
        'description'    => $lang->totop_option_11_Description,
        'optionscode'    => 'yesno',
        'value'        => '1',
        'disporder'        => 11,
        'gid'            => intval($gid),
    );

$totop_setting_12 = array(
        'sid'            => '0',
        'name'        => 'totop_enable_printthread',
        'title'            => $lang->totop_option_12_Title,
        'description'    => $lang->totop_option_12_Description,
        'optionscode'    => 'yesno',
        'value'        => '1',
        'disporder'        => 12,
        'gid'            => intval($gid),
    );

$totop_setting_13 = array(
        'sid'            => '0',
        'name'        => 'totop_enable_calendar',
        'title'            => $lang->totop_option_13_Title,
        'description'    => $lang->totop_option_13_Description,
        'optionscode'    => 'yesno',
        'value'        => '1',
        'disporder'        => 13,
        'gid'            => intval($gid),
    );

$totop_setting_14 = array(
        'sid'            => '0',
        'name'        => 'totop_enable_portal',
        'title'            => $lang->totop_option_14_Title,
        'description'    => $lang->totop_option_14_Description,
        'optionscode'    => 'yesno',
        'value'        => '1',
        'disporder'        => 14,
        'gid'            => intval($gid),
    );

$db->insert_query('settings', $totop_setting_1);
$db->insert_query('settings', $totop_setting_2);
$db->insert_query('settings', $totop_setting_3);
$db->insert_query('settings', $totop_setting_4);
$db->insert_query('settings', $totop_setting_5);
$db->insert_query('settings', $totop_setting_6);
$db->insert_query('settings', $totop_setting_7);
$db->insert_query('settings', $totop_setting_8);
$db->insert_query('settings', $totop_setting_9);
$db->insert_query('settings', $totop_setting_10);
$db->insert_query('settings', $totop_setting_11);
$db->insert_query('settings', $totop_setting_12);
$db->insert_query('settings', $totop_setting_13);
$db->insert_query('settings', $totop_setting_14);

rebuild_settings(); 

}

// Deactivate

function totop_deactivate()
{

  global $db, $lang;

    $lang->load("totop");
    
 $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name IN ('totop_enable')");
    $db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='totop'");

rebuild_settings();

}


function totop($page)
{
	global $mybb,$db;


if ($mybb->settings['totop_enable'] == 1){


if ($mybb->settings['totop_enable_index'] == 1){

        if(THIS_SCRIPT=="index.php")
    {
        {
            $page=str_replace("</head>",'<link rel="stylesheet" type="text/css" href="'.$mybb->settings["bburl"].'/inc/plugins/totop/css/totop.css" />
<script type="text/javascript" src="'.$mybb->settings["bburl"].'/inc/plugins/totop/js/init.js"></script>
</head>',$page);

            $page=str_replace("</body>",'<div id="backtop">
    <a class="go-top" href="#top" title="Back to Top"><img src="'.$mybb->settings["bburl"].'/inc/plugins/totop/img/top.png" /></a>
</div>
</body>',$page);
            
            return $page;
        }
    } 
}

if ($mybb->settings['totop_enable_forumdisplay'] == 1){  

	    if(THIS_SCRIPT=="forumdisplay.php")
	{
		{
            $page=str_replace("</head>",'<link rel="stylesheet" type="text/css" href="'.$mybb->settings["bburl"].'/inc/plugins/totop/css/totop.css" />
<script type="text/javascript" src="'.$mybb->settings["bburl"].'/inc/plugins/totop/js/init.js"></script>
</head>',$page);

            $page=str_replace("</body>",'<div id="backtop">
    <a class="go-top" href="#top" title="Back to Top"><img src="'.$mybb->settings["bburl"].'/inc/plugins/totop/img/top.png" /></a>
</div>
</body>',$page);
			
			return $page;
		}
	}
}

if ($mybb->settings['totop_enable_showthread'] == 1){

        if(THIS_SCRIPT=="showthread.php")
    {
        {
            $page=str_replace("</head>",'<link rel="stylesheet" type="text/css" href="'.$mybb->settings["bburl"].'/inc/plugins/totop/css/totop.css" />
<script type="text/javascript" src="'.$mybb->settings["bburl"].'/inc/plugins/totop/js/init.js"></script>
</head>',$page);

            $page=str_replace("</body>",'<div id="backtop">
    <a class="go-top" href="#top" title="Back to Top"><img src="'.$mybb->settings["bburl"].'/inc/plugins/totop/img/top.png" /></a>
</div>
</body>',$page);
            
            return $page;
        }
    }
}

if ($mybb->settings['totop_enable_memberlist'] == 1){

        if(THIS_SCRIPT=="memberlist.php")
    {
        {
            $page=str_replace("</head>",'<link rel="stylesheet" type="text/css" href="'.$mybb->settings["bburl"].'/inc/plugins/totop/css/totop.css" />
<script type="text/javascript" src="'.$mybb->settings["bburl"].'/inc/plugins/totop/js/init.js"></script>
</head>',$page);

            $page=str_replace("</body>",'<div id="backtop">
    <a class="go-top" href="#top" title="Back to Top"><img src="'.$mybb->settings["bburl"].'/inc/plugins/totop/img/top.png" /></a>
</div>
</body>',$page);
            
            return $page;
        }
    }
}

if ($mybb->settings['totop_enable_member'] == 1){

        if(THIS_SCRIPT=="member.php")
    {
        {
            $page=str_replace("</head>",'<link rel="stylesheet" type="text/css" href="'.$mybb->settings["bburl"].'/inc/plugins/totop/css/totop.css" />
<script type="text/javascript" src="'.$mybb->settings["bburl"].'/inc/plugins/totop/js/init.js"></script>
</head>',$page);

            $page=str_replace("</body>",'<div id="backtop">
    <a class="go-top" href="#top" title="Back to Top"><img src="'.$mybb->settings["bburl"].'/inc/plugins/totop/img/top.png" /></a>
</div>
</body>',$page);
            
            return $page;
        }
    }
}

if ($mybb->settings['totop_enable_misc'] == 1){

        if(THIS_SCRIPT=="misc.php")
    {
        {
            $page=str_replace("</head>",'<link rel="stylesheet" type="text/css" href="'.$mybb->settings["bburl"].'/inc/plugins/totop/css/totop.css" />
<script type="text/javascript" src="'.$mybb->settings["bburl"].'/inc/plugins/totop/js/init.js"></script>
</head>',$page);

            $page=str_replace("</body>",'<div id="backtop">
    <a class="go-top" href="#top" title="Back to Top"><img src="'.$mybb->settings["bburl"].'/inc/plugins/totop/img/top.png" /></a>
</div>
</body>',$page);
            
            return $page;
        }
    }
}

if ($mybb->settings['totop_enable_search'] == 1){

        if(THIS_SCRIPT=="search.php")
    {
        {
            $page=str_replace("</head>",'<link rel="stylesheet" type="text/css" href="'.$mybb->settings["bburl"].'/inc/plugins/totop/css/totop.css" />
<script type="text/javascript" src="'.$mybb->settings["bburl"].'/inc/plugins/totop/js/init.js"></script>
</head>',$page);

            $page=str_replace("</body>",'<div id="backtop">
    <a class="go-top" href="#top" title="Back to Top"><img src="'.$mybb->settings["bburl"].'/inc/plugins/totop/img/top.png" /></a>
</div>
</body>',$page);
            
            return $page;
        }
    }
}

if ($mybb->settings['totop_enable_modcp'] == 1){

        if(THIS_SCRIPT=="modcp.php")
    {
        {
            $page=str_replace("</head>",'<link rel="stylesheet" type="text/css" href="'.$mybb->settings["bburl"].'/inc/plugins/totop/css/totop.css" />
<script type="text/javascript" src="'.$mybb->settings["bburl"].'/inc/plugins/totop/js/init.js"></script>
</head>',$page);

            $page=str_replace("</body>",'<div id="backtop">
    <a class="go-top" href="#top" title="Back to Top"><img src="'.$mybb->settings["bburl"].'/inc/plugins/totop/img/top.png" /></a>
</div>
</body>',$page);
            
            return $page;
        }
    }
}

if ($mybb->settings['totop_enable_usercp'] == 1){

        if(THIS_SCRIPT=="usercp.php")
    {
        {
            $page=str_replace("</head>",'<link rel="stylesheet" type="text/css" href="'.$mybb->settings["bburl"].'/inc/plugins/totop/css/totop.css" />
<script type="text/javascript" src="'.$mybb->settings["bburl"].'/inc/plugins/totop/js/init.js"></script>
</head>',$page);

            $page=str_replace("</body>",'<div id="backtop">
    <a class="go-top" href="#top" title="Back to Top"><img src="'.$mybb->settings["bburl"].'/inc/plugins/totop/img/top.png" /></a>
</div>
</body>',$page);
            
            return $page;
        }
    }
}

if ($mybb->settings['totop_enable_private'] == 1){

        if(THIS_SCRIPT=="private.php")
    {
        {
            $page=str_replace("</head>",'<link rel="stylesheet" type="text/css" href="'.$mybb->settings["bburl"].'/inc/plugins/totop/css/totop.css" />
<script type="text/javascript" src="'.$mybb->settings["bburl"].'/inc/plugins/totop/js/init.js"></script>
</head>',$page);

            $page=str_replace("</body>",'<div id="backtop">
    <a class="go-top" href="#top" title="Back to Top"><img src="'.$mybb->settings["bburl"].'/inc/plugins/totop/img/top.png" /></a>
</div>
</body>',$page);
            
            return $page;
        }
    }
}

if ($mybb->settings['totop_enable_printhtread'] == 1){

        if(THIS_SCRIPT=="printthread.php")
    {
        {
            $page=str_replace("</head>",'<link rel="stylesheet" type="text/css" href="'.$mybb->settings["bburl"].'/inc/plugins/totop/css/totop.css" />
<script type="text/javascript" src="'.$mybb->settings["bburl"].'/inc/plugins/totop/js/init.js"></script>
</head>',$page);

            $page=str_replace("</body>",'<div id="backtop">
    <a class="go-top" href="#top" title="Back to Top"><img src="'.$mybb->settings["bburl"].'/inc/plugins/totop/img/top.png" /></a>
</div>
</body>',$page);
            
            return $page;
        }
    }
}

if ($mybb->settings['totop_enable_calendar'] == 1){

        if(THIS_SCRIPT=="calendar.php")
    {
        {
            $page=str_replace("</head>",'<link rel="stylesheet" type="text/css" href="'.$mybb->settings["bburl"].'/inc/plugins/totop/css/totop.css" />
<script type="text/javascript" src="'.$mybb->settings["bburl"].'/inc/plugins/totop/js/init.js"></script>
</head>',$page);

            $page=str_replace("</body>",'<div id="backtop">
    <a class="go-top" href="#top" title="Back to Top"><img src="'.$mybb->settings["bburl"].'/inc/plugins/totop/img/top.png" /></a>
</div>
</body>',$page);
            
            return $page;
        }
    }
}

if ($mybb->settings['totop_enable_portal'] == 1){

        if(THIS_SCRIPT=="portal.php")
	{

		{
			$page=str_replace("</head>",'<link rel="stylesheet" type="text/css" href="'.$mybb->settings["bburl"].'/inc/plugins/totop/css/totop.css" />
<script type="text/javascript" src="'.$mybb->settings["bburl"].'/inc/plugins/totop/js/init.js"></script>
</head>',$page);

            $page=str_replace("</body>",'<div id="backtop">
    <a class="go-top" href="#top" title="Back to Top"><img src="'.$mybb->settings["bburl"].'/inc/plugins/totop/img/top.png" /></a>
</div>
</body>',$page);
			
			return $page;
		}
	}
}


}
}
?>