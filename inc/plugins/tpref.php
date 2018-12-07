<?php
/* Thread prefixes on index... 
* Add some features to idex page, not loaded by default
* Created by Dark Neo for MyBB 1.8.x series...
*/
if(THIS_SCRIPT == 'index.php' || THIS_SCRIPT == 'forumdisplay.php'){
$plugins->add_hook('build_forumbits_forum', 'forumlist_tprefix');
}

function tpref_info()
{
	global $mybb, $config;
	if($mybb->settings['tpref_enable'] == 1){
		$ext_config = '<div style="float: right;"><a href="index.php?module=config&action=change&search=tpref" style="color:#035488; padding: 21px; text-decoration: none;">Configurar</a></div>';
	}
	else if($mybb->settings['tpref_enable'] == 0){
		$ext_config = '<div style="float: right;"><span style="color:Red; padding: 21px; text-decoration: none;">Plugin disabled</span></div>';
	}	
    return array(
        'name'        	=> "Thread prefixes",
        'description' 	=> "Add prefixes on your index if available..." . $ext_config,
        'website'     	=> 'http://soportemybb.es',
		'version'     	=> '1.0',
		'author'      	=> 'Dark Neo',
		'authorsite'  	=> 'http://soportemybb.es',
		"compatibility" => "18*",
        'codename'      => "tpref"        
    );
}
	
function tpref_activate()
{
	global $mybb, $db,$lang;
	$settings_group = array(
        "gid" => "",
        "name" => "tpref",
        "title" => "Thread Prefixes",
        "description" => "Show preixes o threads on index",
        "disporder" => "",
        "isdefault" => 0,
    );
    $db->insert_query("settinggroups", $settings_group);

	$gid = $db->insert_id();
	$setting[] = array(
		"sid" => "",
		"name" => "tpref_enable",
		"title" => "Enable/Disable plugin",
		"description" => "Enable Yes, Disable No",
		"optionscode" => "yesno",
		"value" => 1,
		"disporder" => 1,
		"gid" => intval($gid)
	);
	
	$gid = $db->insert_id();
	$setting[] = array(
		"sid" => "",
		"name" => "tpref_icon",
		"title" => "Show thread icon",
		"description" => "Show the thread icon if any",
		"optionscode" => "yesno",
		"value" => 1,
		"disporder" => 1,
		"gid" => intval($gid)
	);

	$gid = $db->insert_id();
	$setting[] = array(
		"sid" => "",
		"name" => "tpref_prefix",
		"title" => "Show thread prefix",
		"description" => "Show the thread prefix if any",
		"optionscode" => "yesno",
		"value" => 1,
		"disporder" => 1,
		"gid" => intval($gid)
	);
	
 	foreach ($setting as $i)
	{
		$db->insert_query("settings", $i);
	}
	rebuild_settings();	

	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';	
	// Insert all template changes for templates that exist on MyBB...
    find_replace_templatesets("forumbit_depth2_forum_lastpost", '#'.preg_quote('{$lastpost_subject}').'#', '{$forum[\'tpicon\']}{$forum[\'tpprefix\']}{$lastpost_subject}', 0);	
}

function tpref_deactivate()
{
	global $mybb, $db;
	$db->delete_query("settinggroups", "name='tpref'");
	$db->delete_query("settings","name IN ('tpref_enable','tpref_prefix','tpref_icon')");
	rebuild_settings();
	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';	
    find_replace_templatesets("forumbit_depth2_forum_lastpost", '#'.preg_quote('{$forum[\'tpicon\']}').'#', '', 0);	
    find_replace_templatesets("forumbit_depth2_forum_lastpost", '#'.preg_quote('{$forum[\'tpprefix\']}').'#', '', 0);			
}

function tprefix_format($thread)
{
	global $mybb;
	if($mybb->settings['tpref_enable'] == 0 || $mybb->settings['tpref_prefix'] == 0 && $mybb->settings['tpref_icon'] == 0)
	{
		return false;
	}	
	return array(
		'thread_id' => (int)$thread['tid'],
		'prefix_id' => htmlspecialchars_uni($thread['prefix']),
		'lastposter' => htmlspecialchars_uni($thread['lastposter']),		
		'icon_id' => (int)$thread['icon'],
		'tpstyle' => $thread['displaystyle'],
		'tpprefix' => htmlspecialchars_uni($thread['prefijo']),
		'tpicon' => htmlspecialchars_uni($thread['path'])
	);
	return format_thread($thread);	
}

function forumlist_tprefix(&$_f)
{
	global $cache, $db, $fcache, $mybb, $thread;

	if($mybb->settings['tpref_enable'] == 0 || $mybb->settings['tpref_prefix'] == 0 && $mybb->settings['tpref_icon'] == 0)
	{
		return false;
	}
	if(!isset($cache->cache['tprefix_cache']))
	{
		$cache->cache['tprefix_cache'] = array();
		$tprefix_cache = $cache->read('tprefix_cache');
		$forums = new RecursiveIteratorIterator(new RecursiveArrayIterator($fcache));
		// Sentencia que busca el creador de los temas, cuando existen subforos...
		foreach($forums as $_forum)
		{
			$forum = $forums->getSubIterator();
			if($forum['fid'])
			{
				$forum = iterator_to_array($forum);
				$tprefix_cache[$forum['fid']] = $forum;
				if($private_forums[$forum['fid']]['lastpost'])
				{
					$forum['lastpost'] = $private_forums[$forum['fid']]['lastpost'];
					$lastpost_data = array(
						"lastpost" => $private_forums[$forum['fid']]['lastpost'],
						"lastpostsubject" => $private_forums[$forum['fid']]['subject'],
						"lastposter" => $private_forums[$forum['fid']]['lastposter'],
						"lastposttid" => $private_forums[$forum['fid']]['tid'],
						"lastposteruid" => $private_forums[$forum['fid']]['lastposteruid']
					);
				}
				else
				{
					$lastpost_data = array(
						"lastpost" => $forum['lastpost'],
						"lastpostsubject" => $forum['lastpostsubject'],
						"lastposter" => $forum['lastposter'],
						"lastposttid" => $forum['lastposttid'],
						"lastposteruid" => $forum['lastposteruid']
					);
				}			
				// Fetch subforums of this forum
				if(isset($fcache[$forum['fid']]))
				{
					$forum_info = build_forumbits($forum['fid'], $depth+1);
					// If the child forums' lastpost is greater than the one for this forum, set it as the child forums greatest.
					if($forum_info['lastpost']['lastpost'] > $lastpost_data['lastpost'])
					{
						$lastpost_data = $forum_info['lastpost'];
					}
					$sub_forums = $forum_info['forum_list'];
				}
				// If the current forums lastpost is greater than other child forums of the current parent, overwrite it
				if(!isset($parent_lastpost) || $lastpost_data['lastpost'] > $parent_lastpost['lastpost'])
				{
					$parent_lastpost = $lastpost_data;
				}			
				if(isset($tprefix_cache) && $lastpost_data['lastposteruid'] > 0){	
					$tprefix_cache[$forum['fid']]['tpreftid'] = $lastpost_data['lastposttid'];							
					$tprefix_cache[$forum['fid']]['lastpost'] = $lastpost_data['lastpost'];
					$tprefix_cache[$forum['fid']]['lastposter'] = $lastpost_data['lastposter'];					
				}
			}
		}
			
		// Esta sentencia ordena los usuarios por usuario/foro
		$threads = array();
		foreach($tprefix_cache as $forum)
		{
			if(isset($forum['tpreftid']))
			{
				$threads[$forum['tpreftid']][] = $forum['fid'];
			}
		}
		if(!empty($threads))
		{
			$sql = implode(',', array_keys($threads));
			$query = $db->query("SELECT t.tid, t.fid, t.prefix, t.lastposter, t.icon, tp.displaystyle, tp.prefix as prefijo ,i.iid, i.path
			FROM " .TABLE_PREFIX. "threads t
			LEFT JOIN " .TABLE_PREFIX. "threadprefixes tp			
			ON t.prefix = tp.pid
			LEFT JOIN " .TABLE_PREFIX. "icons i
			ON t.icon = i.iid			
			WHERE tid IN ({$sql})");
			while($thread= $db->fetch_array($query))
			{
				$thread_prefix = tprefix_format($thread); 				
				foreach($threads[$thread['tid']] as $tid)
				{
					$tprefix_cache[$tid]['tprefix_fid'] = $thread_prefix;
				}	
			}
		}
		// Aplicamos los cambios! Reemplazando las lineas de código para guardarlas en cache...
		$cache->cache['tprefix_cache'] = $tprefix_cache;	
	}
	
	$_f['tprefix_lastpost'] = $cache->cache['tprefix_cache'][$_f['fid']]['tprefix_fid'];	
	if($mybb->settings['tpref_icon'] == 1)
	{
		$_f['tpicon'] = $_f['tprefix_lastpost']['tpicon'];
		if(!empty($_f['tpicon'])){
			$_f['tpicon'] = "<img src=\"{$_f['tpicon']}\" alt=\"Thread icon\" width=\"16\" height=\"16\" />&nbsp;";
		}		
	}
	if($mybb->settings['tpref_prefix'] == 1)
	{	
		$_f['pref'] = $_f['tprefix_lastpost']['tpprefix'];
		$_f['tpstyle'] = $_f['tprefix_lastpost']['tpstyle'];
		if(!empty($_f['tpstyle'])){
			$_f['tpprefix'] = $_f['tpstyle'] . "&nbsp;";			
		}
	}
}    