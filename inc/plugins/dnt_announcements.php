<?php
/**
*@ Autor: Dark Neo
*@ Fecha: 2013-12-12
*@ Version: 1.x
*@ Contacto: neogeoman@gmail.com
*/

// Inhabilitar acceso directo a este archivo
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook('global_start', 'dnt_announcements_templates');
$plugins->add_hook('global_intermediate', 'dnt_announcements_global');

// Informacion del plugin
function dnt_announcements_info()
{
	global $mybb;

    //Revisar si la opcion esta activa
    if($mybb->settings['dnt_announcements_active'] == 1)
    {
		$dnt_announcements_config_link = '<div style="float: right;"><a href="index.php?module=config&amp;action=change&amp;search=dnt_announcements" style="color:#035488; background: url(../images/usercp/options.gif) no-repeat 0px 18px; padding: 18px; text-decoration: none;">Configurar</a></div>';
	}
	
	return array(
        "name"			=> "DNT Announcements",
    	"description"	=> "Display announcements to your forums for certain groups" . $dnt_announcements_config_link,
		"website"		=> "https://www.mybb.com",
		"author"		=> "Dark Neo",
		"authorsite"	=> "https://soportemybb.es",
		"version"		=> "1.3",
		"codename" 		=> "dnt_announcements",
		"compatibility" => "18*"
	);
} 

//Se ejecuta al activar el plugin
function dnt_announcements_activate() {
    //Variables que vamos a utilizar
   	global $mybb, $cache, $db, $lang, $templates;

    $lang->load("dnt_announcements", false, true);

    // Crear el grupo de opciones
    $query = $db->simple_select("settinggroups", "COUNT(*) as dnt_rows");
    $dnt_rows = $db->fetch_field($query, "dnt_rows");

    $dnt_announcements_groupconfig = array(
        'name' => 'dnt_announcements',
        'title' => "DNT Announcements",
        'description' => "Display nice announcements to your forum",
        'disporder' => $dnt_rows+1,
        'isdefault' => 0
    );

    $group['gid'] = $db->insert_query("settinggroups", $dnt_announcements_groupconfig);

    // Crear las opciones del plugin a utilizar
    $dnt_announcements_config = array();

    $dnt_announcements_config[] = array(
        'name' => 'dnt_announcements_active',
        'title' => "Enable announcements on your forums",
        'description' => "Set to no if you want to disable this plugin (Not display announcements on your forums if disabled)",
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 10,
        'gid' => $group['gid']
    );

	$dnt_announcements_config[] = array(
		"name"			=> "dnt_announcements_config_guests_gid",
		"title"			=> "Announcements for guests",
		"description"   => "Select usergroups who can view announcements for guests",
		"optionscode" 	=> "groupselect",
		"value"			=> '1,5,7',
		"disporder"		=> 20,
		"gid"			=> $group['gid']
	);	

	$dnt_announcements_config[] = array(
		"name"			=> "dnt_announcements_config_registered_gid",
		"title"			=> "Announcements for registered users",
		"description"   => "Select usergroups who can view announcements for registered users",
		"optionscode" 	=> "groupselect",
		"value"			=> 2,
		"disporder"		=> 30,
		"gid"			=> $group['gid']
	);	

	$dnt_announcements_config[] = array(
		"name"			=> "dnt_announcements_config_mods_gid",
		"title"			=> "Announcements for moderators",
		"description"   => "Select usergroups who can view announcements for moderators",
		"optionscode" 	=> "groupselect",
		"value"			=> '3,6',
		"disporder"		=> 40,
		"gid"			=> $group['gid']
	);	

	$dnt_announcements_config[] = array(
		"name"			=> "dnt_announcements_config_admins_gid",
		"title"			=> "Announcements for administrators",
		"description"   => "Select usergroups who can view announcements for admnistrators",
		"optionscode" 	=> "groupselect",
		"value"			=> 4,
		"disporder"		=> 50,
		"gid"			=> $group['gid']
	);	

    foreach($dnt_announcements_config as $array => $content)
    {
        $db->insert_query("settings", $content);
    }

	//Rebuild settings file to load new settings...
	rebuild_settings();

	// Adding new group of templates for this plugin...  
	$templategrouparray = array(
		'prefix' => 'dntannouncements',
		'title'  => 'DNT Announcements'
	);
	$db->insert_query("templategroups", $templategrouparray);
	
	//Adding new templates
	$templatearray = array(
		'title' => 'dntannouncements_guests',
		'template' => $db->escape_string('<div class="dnt_announcements_guests"><span class="fa fa-exclamation-circle"></span>Hi Guest this is an announcement for guests !!!</div>'),
		'sid' => '-2',
		'version' => '1806',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);
	$templatearray = array(
		'title' => 'dntannouncements_registered',
		'template' => $db->escape_string('<div class="dnt_announcements_registered"><span class="fa fa-exclamation-circle"></span>Hi {$mybb->user[\'username\']} this is an announcement for all registered users on the forum !!!</div>'),
		'sid' => '-2',
		'version' => '1806',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);
	$templatearray = array(
		'title' => 'dntannouncements_admins',
		'template' => $db->escape_string('<div class="dnt_announcements_admins"><span class="fa fa-exclamation-circle"></span>Hi {$mybb->user[\'username\']} this is an announcement for administrators only !!!</div>'),
		'sid' => '-2',
		'version' => '1806',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);
	$templatearray = array(
		'title' => 'dntannouncements_mods',
		'template' => $db->escape_string('<div class="dnt_announcements_mods"><span class="fa fa-exclamation-circle"></span>Hi {$mybb->user[\'username\']} this is an announcement for all moderators !!!</div>'),
		'sid' => '-2',
		'version' => '1806',
		'dateline' => TIME_NOW
		);
	$db->insert_query("templates", $templatearray);

	// Add stylesheet
	$dnt_announcements_css = '.dnt_announcements_admins {
	color: #00529B;
	background-color: #BDE5F8;
	padding: 10px;
	border-left: 3px solid #00529B;
	border-radius: 2px;
	margin: 0px;
	text-align: center;
	margin-bottom: 10px;
}

.dnt_announcements_registered {
	color: #279025;
    background-color: #DFF2BF;
	padding: 10px;
	border-left: 3px solid #279025;
	border-radius: 2px;
	margin: 5px 0px 10px 0px;
	text-align: center;
}

.dnt_announcements_mods {
    color: #9F6000;
    background-color: #FEEFB3;
	padding: 10px;
	border-left: 3px solid #9F6000;
	border-radius: 2px;
	margin: 0px;
	text-align: center;
	margin-bottom: 10px;
}

.dnt_announcements_guests {
    color: #D8000C;
    background-color: #FFBABA;
	padding: 10px;
	border-left: 3px solid #D8000C;
	border-radius: 2px;
	margin: 0px;
	text-align: center;	
	margin-bottom: 10px;	
}

.dnt_announcements_admins > span, .dnt_announcements_registered > span, .dnt_announcements_mods > span, .dnt_announcements_guests > span{
	float: left;
	margin-left: 10px;
	margin-top: 2px;
}';

	$stylesheet = array(
		"name"			=> "dnt_announcements.css",
		"tid"			=> 1,
		"attachedto"	=> '',		
		"stylesheet"	=> $db->escape_string($dnt_announcements_css),
		"cachefile"		=> "dnt_announcements.css",
		"lastmodified"	=> TIME_NOW,
	);
	
	$sid = $db->insert_query("themestylesheets", $stylesheet);
	
	//Archivo requerido para cambios en estilos y plantillas.
	require_once MYBB_ADMIN_DIR.'/inc/functions_themes.php';
	cache_stylesheet($stylesheet['tid'], $stylesheet['cachefile'], $dnt_announcements_css);
	update_theme_stylesheet_list(1, false, true);
		
     //Archivo requerido para reemplazo de templates
   	require "../inc/adminfunctions_templates.php";
    // Reemplazos que vamos a hacer en las plantillas 1.- Platilla 2.- Contenido a Reemplazar 3.- Contenido que reemplaza lo anterior
    find_replace_templatesets("header", '#'.preg_quote('{$pm_notice}').'#', '{$pm_notice}{$dnt_announcements}');

    //Se actualiza la info de las plantillas
   	$cache->update_forums();
}

function dnt_announcements_deactivate() {
    //Variables que vamos a utilizar
	global $mybb, $cache, $db;
  
    //Eliminamos la hoja de estilo creada...
   	$db->delete_query('themestylesheets', "name='dnt_announcements.css'");
	$query = $db->simple_select('themes', 'tid');
	while($theme = $db->fetch_array($query))
	{
		require_once MYBB_ADMIN_DIR.'inc/functions_themes.php';
		update_theme_stylesheet_list($theme['tid']);
	}

    //Archivo requerido para reemplazo de templates
 	require MYBB_ROOT.'inc/adminfunctions_templates.php';
	
    //Reemplazos que vamos a hacer en las plantillas 1.- Platilla 2.- Contenido a Reemplazar 3.- Contenido que reemplaza lo anterior
    find_replace_templatesets("header", '#'.preg_quote('{$dnt_announcements}').'#', '',0);
	
	//Delete templates
	$db->delete_query("templategroups", "prefix='dntannouncements'");
	$db->delete_query("templates", "title IN('dntannouncements_guests','dntannouncements_registered','dntannouncements_mods','dntannouncements_admins')");
	// Delete settings
	$db->delete_query("settings", "name IN ('dnt_announcements_active', 'dnt_announcements_config_guests_gid', 'dnt_announcements_config_registered_gid', 'dnt_announcements_config_mods_gid', 'dnt_announcements_config_admins_gid')");
	$db->delete_query("settinggroups", "name='dnt_announcements'");

    rebuild_settings();
	
    //Se actualiza la info de las plantillas
  	$cache->update_forums();
}

function dnt_announcements_templates(){
    global $mybb, $templates, $templatelist;

	if($mybb->settings['dnt_announcements_active'] == 0)
    {
        return false;
    }

	if(isset($templatelist))
	{
		$templatelist .= ",dntannouncements_guests,dntannouncements_registered,dntannouncements_mods,dntannouncements_admins";
	}
}
	
function dnt_announcements_global(){
    global $mybb, $theme, $templates, $dnt_announcements;

	if($mybb->settings['dnt_announcements_active'] == 0)
    {
        return false;
    }

	if(isset($GLOBALS['templatelist']))
	{
		$GLOBALS['templatelist'] .= ",dntannouncements_guests,dntannouncements_registered,dntannouncements_mods,dntannouncements_admins";
	}
	
	$dnt_announcements = "";
	$dnt_announcements_gu = $mybb->settings['dnt_announcements_config_guests_gid'];
	$dnt_announcements_re = $mybb->settings['dnt_announcements_config_registered_gid'];
	$dnt_announcements_mo = $mybb->settings['dnt_announcements_config_mods_gid'];
	$dnt_announcements_ad = $mybb->settings['dnt_announcements_config_admins_gid'];
	$mybb->user['usergroup'] = (int)$mybb->user['usergroup'];
	$dag = $dar = $dam = $daa = false;
	
	if(!empty($mybb->user['additionalgroups'])){
		$dnt_add_groups = explode(",", $mybb->user['additionalgroups']);
		foreach($dnt_add_groups as $dagr){
			$dagc = $dagr;
		}
	}

	if(!empty($dnt_announcements_gu)){
		if($dnt_announcements_gu == -1)
			$dag = true;
		else
		{
			$dnt_announcements_gu = explode(",", $dnt_announcements_gu);
			foreach($dnt_announcements_gu as $dagu){
				if($mybb->user['usergroup'] == $dagu){
					$dag = true;
				}
			}			
		}
	}

	if(!empty($dnt_announcements_re)){
		if($dnt_announcements_re == -1)
			$dar = true;
		else
		{
			$dnt_announcements_re = explode(",", $dnt_announcements_re);
			foreach($dnt_announcements_re as $dare){
				if($mybb->user['usergroup'] == $dare){
					$dar = true;
				}
			}
		}
	}

	if(!empty($dnt_announcements_mo)){
		if($dnt_announcements_mo == -1)
			$dam = true;
		else
		{		
			$dnt_announcements_mo = explode(",", $dnt_announcements_mo);
			foreach($dnt_announcements_mo as $damo){
				if($mybb->user['usergroup'] == $damo){
					$dam = true;
				}
			}
		}
	}

	if(!empty($dnt_announcements_ad)){
		if($dnt_announcements_ad == -1)
			$daa = true;
		else
		{		
			$dnt_announcements_ad = explode(",", $dnt_announcements_ad);
			foreach($dnt_announcements_ad as $daad){
				if($mybb->user['usergroup'] == $daad){
					$daa = true;
				}
			}
		}
	}
	
	if(!empty($dnt_announcements_gu) && ($mybb->user['uid'] == 0 && $dag == true || $dag == true)){
		eval("\$dnt_announcements .= \"".$templates->get("dntannouncements_guests",1,0)."\";");	
	}
	if(!empty($dnt_announcements_ad) && ($daa == true || $dagr == true || $dagc == $dnt_announcements_ad)){
		eval("\$dnt_announcements .= \"".$templates->get("dntannouncements_admins",1,0)."\";");	
	}	
	if(!empty($dnt_announcements_mo) && ($dam == true || $dagr == true || $dagc == $dnt_announcements_mo)){
		eval("\$dnt_announcements .= \"".$templates->get("dntannouncements_mods",1,0)."\";");	
	}	
	if(!empty($dnt_announcements_re) && ($dar == true || $dagr == true || $dagc == $dnt_announcements_re)){
		eval("\$dnt_announcements .= \"".$templates->get("dntannouncements_registered",1,0)."\";");	
	}	
	
	return $dnt_announcements;
}

?>