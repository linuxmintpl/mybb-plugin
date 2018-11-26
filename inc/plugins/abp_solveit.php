<?php

/**
 * Solve IT ! plugin : Mark a thread as resolved
 * (c) CrazyCat 2018
 */
if (!defined('IN_MYBB'))
    die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');


define('CN_ABPSOLVEIT', str_replace('.php', '', basename(__FILE__)));

if (defined('THIS_SCRIPT')) {
    global $templatelist;
    if (isset($templatelist)) {
        $templatelist .= ',';
    }
    if (THIS_SCRIPT == 'showthread.php') {
        $templatelist .= CN_ABPSOLVEIT . '_button';
    }
}

// Hooks are loaded only in front
if (defined('IN_ADMINCP')) {
    $plugins->add_hook('admin_config_menu', CN_ABPSOLVEIT . '_admin_config_menu');
    $plugins->add_hook('admin_config_action_handler', CN_ABPSOLVEIT . '_admin_config_action_handler');
    $plugins->add_hook('admin_load', CN_ABPSOLVEIT . '_admin_load');
    $plugins->add_hook('admin_config_settings_change_commit', CN_ABPSOLVEIT . '_setting_update');
} else {
    $plugins->add_hook('postbit', CN_ABPSOLVEIT . '_postbit');
    $plugins->add_hook('xmlhttp', CN_ABPSOLVEIT . '_ajax');
    $plugins->add_hook('showthread_start', CN_ABPSOLVEIT . '_head');
}

/**
 * Initialization of the info
 * @global object $lang
 * @return array
 */
function abp_solveit_info() {
    global $lang;
    $lang->load(CN_ABPSOLVEIT);
    return array(
        'name' => $lang->abp_siname,
        'description' => $lang->abp_sidesc . '<a href=\'https://ko-fi.com/V7V7E5W8\' target=\'_blank\'><img height=\'30\' style=\'border:0px;height:30px;float:right;\' src=\'https://az743702.vo.msecnd.net/cdn/kofi1.png?v=0\' border=\'0\' alt=\'Buy Me a Coffee at ko-fi.com\' /></a>',
        'website' => 'https://www.g33k-zone.org',
        'author' => 'CrazyCat',
        'authorsite' => 'https://www.g33k-zone.org',
        'version' => '0.3.2',
        'compatibility' => '18*',
        'codename' => CN_ABPSOLVEIT
    );
}

/**
 * Installation of the plugin
 * Creates settings, templates, styles and alter tables
 * @global object $db
 * @global object $lang
 */
function abp_solveit_install() {
    global $db, $lang;
    $lang->load(CN_ABPSOLVEIT);

    $settinggroups = array(
        'name' => CN_ABPSOLVEIT,
        'title' => $lang->abp_sigs,
        'description' => $lang->abp_sigsd,
        'disporder' => 0,
        'isdefault' => 0
    );
    $db->insert_query('settinggroups', $settinggroups);
    $gid = $db->insert_id();

    $settings[] = array(
        'name' => CN_ABPSOLVEIT . '_forums',
        'title' => $lang->abp_sis1_tit,
        'description' => $lang->abp_sis1_desc,
        'optionscode' => 'forumselect',
        'value' => '',
        'disporder' => 1
    );
    $settings[] = array(
        'name' => CN_ABPSOLVEIT . '_groups',
        'title' => $lang->abp_sis2_tit,
        'description' => $lang->abp_sis2_desc,
        'optionscode' => 'groupselect',
        'value' => '3,4',
        'disporder' => 2
    );
    $settings[] = array(
        'name' => CN_ABPSOLVEIT . '_owner',
        'title' => $lang->abp_sis3_tit,
        'description' => $lang->abp_sis3_desc,
        'optionscode' => 'yesno',
        'value' => 1,
        'disporder' => 3
    );
    $settings[] = array(
        'name' => CN_ABPSOLVEIT . '_close',
        'title' => $lang->abp_sis4_tit,
        'description' => $lang->abp_sis4_desc,
        'optionscode' => 'yesno',
        'value' => 0,
        'disporder' => 4
    );

    foreach ($settings as $i => $setting) {
        $insert = array(
            'name' => $db->escape_string($setting['name']),
            'title' => $db->escape_string($setting['title']),
            'description' => $db->escape_string($setting['description']),
            'optionscode' => $db->escape_string($setting['optionscode']),
            'value' => $db->escape_string($setting['value']),
            'disporder' => $setting['disporder'],
            'gid' => $gid,
        );
        $db->insert_query('settings', $insert);
    }
    rebuild_settings();

    $db->write_query("ALTER TABLE " . TABLE_PREFIX . "threads ADD solve_pid INT(11) NULL");
    $db->write_query("ALTER TABLE " . TABLE_PREFIX . "users ADD solvecpt INT(11) NOT NULL DEFAULT 0");
    $templates = array(
        '_button' => '<!-- start: ' . CN_ABPSOLVEIT . '_button -->
		<a href="javascript:void(0)" onclick="Threadsolve({$post[\\\'pid\\\']}); return false;" title="' . $lang->abp_sijs1 . '" class="postbit_solver"><span>' . $lang->abp_sijs1 . '</span></a>
		<!-- end: ' . CN_ABPSOLVEIT . '_button -->',
        '_link' => '<!-- start: ' . CN_ABPSOLVEIT . '_link -->
		<a href="{$link}" title="' . $lang->abp_sijs2 . '" class="postbit_solution"><span>' . $lang->abp_sijs2 . '</span></a>
		<!-- end: ' . CN_ABPSOLVEIT . '_link -->',
    );
    foreach ($templates as $name => $template) {
        $db->insert_query('templates', array('title' => CN_ABPSOLVEIT . $name, 'template' => $template, 'sid' => -1, 'version' => 1, 'dateline' => TIME_NOW));
    }

    require_once(MYBB_ADMIN_DIR . 'inc/functions_themes.php');
    $style = '.abp_solution {
    background-color: #D6ECA6;
    border: 1px solid #8DC93E;
}
.postbit_buttons a.postbit_solver span {
    background-position: 0 -200px;
}
';
    $stylesheet = array(
        'name' => CN_ABPSOLVEIT . '.css',
        'tid' => 1,
        'attachedto' => 'showthread.php',
        'stylesheet' => $db->escape_string($style),
        'cachefile' => CN_ABPSOLVEIT . '.css',
        'lastmodified' => TIME_NOW,
    );
    $db->insert_query('themestylesheets', $stylesheet);
    cache_stylesheet(1, CN_ABPSOLVEIT . '.css', $style);
    update_theme_stylesheet_list(1);
}

/**
 * Checks if plugin is installed
 * @global object $db
 * @return boolean
 */
function abp_solveit_is_installed() {
    global $db;
    return $db->field_exists('solve_pid', 'threads');
}

/**
 * Uninstall the plugin
 * Deletes settings, templates, styles and unalter tables
 * @@WARNING : if "close thread" option is enable, uninstall won't reopen threads
 * @global object $db
 * @global object $mybb
 */
function abp_solveit_uninstall() {
    global $db, $mybb;
    $db->delete_query('templates', "title='" . CN_ABPSOLVEIT . "' OR title LIKE '" . CN_ABPSOLVEIT . "_%'");
    $db->delete_query('settinggroups', "name='" . CN_ABPSOLVEIT . "'");
    $db->delete_query('settings', "name LIKE '" . CN_ABPSOLVEIT . "_%'");
    rebuild_settings();
    $db->write_query("ALTER TABLE " . TABLE_PREFIX . "threads DROP COLUMN solve_pid");
    $db->write_query("ALTER TABLE " . TABLE_PREFIX . "users DROP COLUMN solvecpt");

    require_once(MYBB_ADMIN_DIR . 'inc/functions_themes.php');
    $query = $db->simple_select('themes', 'tid');
    while ($tid = $db->fetch_field($query, 'tid')) {
        $css_file = MYBB_ROOT . 'cache/themes/theme{$tid}/' . CN_ABPSOLVEIT . '.css';
        $css_min_file = MYBB_ROOT . 'cache/themes/theme{$tid}/' . CN_ABPSOLVEIT . '.min.css';
        if (file_exists($css_file)) {
            unlink($css_file);
        }
        if (file_exists($css_min_file)) {
            unlink($css_min_file);
        }
    }
    $db->delete_query('themestylesheets', "name='" . CN_ABPSOLVEIT . "' OR name LIKE '" . CN_ABPSOLVEIT . "_%'");
    update_theme_stylesheet_list(1);
}

/**
 * Activate the plugin
 * Changes the postbit templates and the showthread
 */
function abp_solveit_activate() {
    global $mybb, $db, $new_settings;
    if (!$db->table_exists(CN_ABPSOLVEIT . '_tuning')) {
        $db->write_query("CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "abp_solveit_tuning (
  fid int(11) NOT NULL COMMENT 'Forum id',
  pid int(11) NOT NULL DEFAULT 0 COMMENT 'Prefix id',
  close int(1) NOT NULL DEFAULT 0 COMMENT 'Close when solved',
  notused1 int(1) NOT NULL DEFAULT 0 COMMENT 'Later',
  notused2 int(1) NOT NULL DEFAULT 0 COMMENT 'Later too',
  PRIMARY KEY (`fid`)
) ENGINE=MyISAM{$collation};");
    }
    require_once MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets(
            'postbit', '#' . preg_quote('{$post[\'button_edit\']}') . '#i', '{$post[\'button_solveit\']}{$post[\'button_edit\']}'
    );
    find_replace_templatesets(
            'postbit', '#' . preg_quote('<div class="post_body scaleimages"') . '#i', '<div class="post_body scaleimages{$post[\'abp_solveit_css\']}"'
    );
    find_replace_templatesets(
            'postbit_classic', '#' . preg_quote('{$post[\'button_edit\']}') . '#i', '{$post[\'button_solveit\']}{$post[\'button_edit\']}'
    );
    find_replace_templatesets(
            'postbit_classic', '#' . preg_quote('<div class="post_body scaleimages"') . '#i', '<div class="post_body scaleimages{$post[\'abp_solveit_css\']}"'
    );
    find_replace_templatesets(
            'showthread', '#' . preg_quote('{$headerinclude}') . '#i', '{$headerinclude}{$abp_solveit_head}'
    );
    $query = $db->simple_select('settinggroups', 'gid', "name = '" . CN_ABPSOLVEIT . "'");
    $gid = $db->fetch_field($query, 'gid');
    foreach (abp_solveit_new_settings() as $sname => $setting) {
        if ($mybb->settings[$setting['name']]) {
            continue;
        }
        $setting['gid'] = $gid;
        $db->insert_query('settings', $setting);
    }
    rebuild_settings();
}

/**
 * Deactivate the plugin
 * Restores the templates
 */
function abp_solveit_deactivate() {
    require_once MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets(
            'postbit', '#' . preg_quote('{$post[\'button_solveit\']}') . '#i', ''
    );
    find_replace_templatesets(
            'postbit', '#' . preg_quote('{$post[\'abp_solveit_css\']}') . '#i', ''
    );
    find_replace_templatesets(
            'postbit_classic', '#' . preg_quote('{$post[\'button_solveit\']}') . '#i', ''
    );
    find_replace_templatesets(
            'postbit_classic', '#' . preg_quote('{$post[\'abp_solveit_css\']}') . '#i', ''
    );
    find_replace_templatesets(
            'showthread', '#' . preg_quote('{$abp_solveit_head}') . '#i', ''
    );
}

/**
 * Generates the link in sub-menu
 * @global object $mybb
 * @global object $lang
 * @param array $sub_menu
 */
function abp_solveit_admin_config_menu(&$sub_menu) {
    global $mybb, $lang;
    $lang->load(CN_ABPSOLVEIT);
    $sub_menu[] = array('id' => CN_ABPSOLVEIT, 'title' => $lang->abp_siname, 'link' => 'index.php?module=config-' . CN_ABPSOLVEIT);
}

function abp_solveit_admin_config_action_handler(&$actions) {
    global $mybb;
    $actions[CN_ABPSOLVEIT] = array('active' => CN_ABPSOLVEIT, 'file' => '');
}

/**
 * Generates the config page
 * @global object $mybb
 * @global DefaultPage $page
 * @global MyLanguage $lang
 * @global DB_MySQLi $db
 */
function abp_solveit_admin_load() {
    global $mybb, $page, $lang, $db;
    if ($mybb->input['module'] != 'config-' . CN_ABPSOLVEIT) {
        return;
    }
    if ($mybb->input['action'] == 'edit' && $mybb->request_method == 'post') {
        if (is_null($mybb->get_input('my_post_key')) || !verify_post_check($mybb->get_input('my_post_key'), true)) {
            flash_message('error verifying', 'alert');
            admin_redirect('index.php?module=config-' . CN_ABPSOLVEIT);
        }
        $prefixes = $mybb->input['prefix'];
        $closes = $mybb->input['close'];
        foreach ($mybb->input['forum'] as $fid) {
            $db->write_query("REPLACE INTO " . TABLE_PREFIX . "abp_solveit_tuning VALUES(" . $fid . ", " . $prefixes[$fid] . ", " . $closes[$fid] . ", 0, 0)");
        }
        flash_message($lang->abp_siadmin_updok, 'success');
        admin_redirect('index.php?module=config-' . CN_ABPSOLVEIT);
    }
    $lang->load(CN_ABPSOLVEIT);
    $page->add_breadcrumb_item($lang->abp_siname, 'index.php?module=config-' . CN_ABPSOLVEIT);
    $page->output_header();
    $table = new Table();
    $table->construct_header($lang->abp_sis1_tit);
    $table->construct_header('Prefix');
    $table->construct_header('Closed');
    $where = "type='f'";
    if ($mybb->settings[CN_ABPSOLVEIT . '_forums'] != -1 && $mybb->settings[CN_ABPSOLVEIT . '_forums'] != '') {
        $where .= ' AND f.fid IN (' . $mybb->settings[CN_ABPSOLVEIT . '_forums'] . ')';
    }
    if ($mybb->settings[CN_ABPSOLVEIT . '_forums'] == '') {
        $where .= " AND f.fid = 0";
    }
    $query = $db->write_query("SELECT f.fid, f.name, st.pid, st.close FROM " . TABLE_PREFIX . "forums f LEFT JOIN " . TABLE_PREFIX . "abp_solveit_tuning st ON st.fid=f.fid WHERE " . $where);
    if ($db->num_rows($query) < 1) {
        $table->construct_cell('<div align="center">' . $lang->abp_siadmin_noforum . '</div>', array('colspan' => 3));
        $table->construct_row();
        $table->output($lang->abp_siadmin_action);
    } else {
        $prefixes = build_prefixes();
        $form = new Form('index.php?module=config-' . CN_ABPSOLVEIT . '&action=edit', 'post');
        while ($forum = $db->fetch_array($query)) {
            $table->construct_cell($forum['name'] . $form->generate_hidden_field('forum[]', $forum['fid']), array('id' => 'forum'));
            if (is_null($forum['pid'])) {
                $pid = $mybb->settings[CN_ABPSOLVEIT . '_prefix'];
                $close = $mybb->settings[CN_ABPSOLVEIT . '_close'];
            } else {
                $pid = $forum['pid'];
                $close = $forum['close'];
            }
            $options = array('-1' => '', '0' => $lang->abp_siadmin_rempfx);
            foreach ($prefixes as $id => $pfx) {
                $options[$id] = $pfx['prefix'];
            }
            $table->construct_cell($form->generate_select_box('prefix[' . $forum['fid'] . ']', $options, $pid, array('id' => 'prefix')));
            $table->construct_cell($form->generate_yes_no_radio('close[' . $forum['fid'] . ']', $close, true));
            $table->construct_row();
        }
        $table->output($lang->abp_siadmin_action);
        $buttons = array();
        $buttons[] = $form->generate_submit_button($lang->abp_siadmin_save);
        $form->output_submit_wrapper($buttons);
        $form->end();
    }
    $page->output_footer();
}

/**
 * Internal usage: generate a thread prefix list
 * @global object $mybb
 * @return string html select
 */
function abp_solveit_listprefixes() {
    global $mybb;
    $prefixes = build_prefixes();
    $pref_chooser = '<select name="upsetting[' . CN_ABPSOLVEIT . '_prefix]" id="setting_' . CN_ABPSOLVEIT . '_prefix">' . PHP_EOL;
    $pref_chooser .= '<option value="-1"></option>' . PHP_EOL;
    $pref_chooser .= '<option value="0">* ' . $lang->abp_siadmin_rempfx . ' *</option>' . PHP_EOL;
    foreach ($prefixes as $id => $pfx) {
        if ($pfx['pid'] == $mybb->settings[CN_ABPSOLVEIT . '_prefix']) {
            $selected = ' selected="selected"';
        } else {
            $selected = '';
        }
        $pref_chooser .= '<option value="' . $pfx['pid'] . '"' . $selected . '>' . $pfx['prefix'] . '</option>' . PHP_EOL;
    }
    return $pref_chooser;
}

/**
 * Create the buttons in the postbits, and changes the
 * class of a solution post
 * @global object $mybb
 * @global type $templates
 * @global object $lang
 * @global type $thread
 * @param object $post
 * @return nothing (exiting the process)
 */
function abp_solveit_postbit(&$post) {
    global $mybb, $templates, $lang, $thread;
    $lang->load(CN_ABPSOLVEIT);
    $guser = array_merge(array($mybb->user['usergroup']), explode(',', $mybb->user['additionalgroups']));
    $s_forums = explode(',', $mybb->settings[CN_ABPSOLVEIT . '_forums']);
    if ($mybb->settings[CN_ABPSOLVEIT . '_forums'] != -1 && !in_array($thread['fid'], $s_forums)) {
        // Not a forum where we can solve, exiting.
        return;
    }
    if ($thread['firstpost'] == $post['pid']) {
        if ((int) $thread['solve_pid'] > 0) {
            // Thread is already solved, we'll add a link to the solution on firstpost
            $link = get_post_link($thread['solve_pid'], $thread['tid']) . '#pid' . $thread['solve_pid'];
            eval("\$post['button_solveit'] .= \"" . $templates->get(CN_ABPSOLVEIT . '_link') . "\";");
            return;
        } else {
            return;
        }
    } else {
        if ((int) $thread['solve_pid'] > 0) {
            if ((int) $thread['solve_pid'] == $post['pid']) {
                eval("\$post['abp_solveit_css'] = \" abp_solution\";");
            } else {
                eval("\$post['abp_solveit_css'] = \"\";");
            }
            return;
        } else {
            if (count(array_intersect(explode(',', $mybb->settings[CN_ABPSOLVEIT . '_groups']), $guser)) > 0 || ($mybb->settings[CN_ABPSOLVEIT . '_owner'] == 1 && $thread['uid'] == $mybb->user['uid'])) {
                eval("\$post['button_solveit'] .= \"" . $templates->get(CN_ABPSOLVEIT . '_button') . "\";");
            }
        }
    }
    return;
}

/**
 * Adds the javascript in front
 * @global object $mybb
 * @global string $abp_solveit_head
 * @global object $lang
 */
function abp_solveit_head() {
    global $mybb, $abp_solveit_head, $lang;
    $lang->load(CN_ABPSOLVEIT);
    eval("\$abp_solveit_head = \"\n\n<!-- start: abp_solveit_head -->\";");
    eval("\$abp_solveit_head .= \"
	<script type='text/javascript'>
        lang.abp_solveit_error = '" . $lang->abp_sijs3 . "';
	function Threadsolve(pid) {
		jQuery.ajax({
			url: '" . $mybb->settings['bburl'] . "/xmlhttp.php',
			type: 'post',
			data: {
				plugin: '" . CN_ABPSOLVEIT . "',
				my_post_key: my_post_key,
				type: 'solve',
				tid: " . $mybb->get_input('tid') . ",
				pid: pid
			},
			success: function(data) {
                            if (data.error == null) {
				jQuery('#post_'+pid).find('.post_content').addClass('abp_solution');
				jQuery('.postbit_solver').remove();
                            } else {
                                $.jGrowl(lang.abp_solveit_error + '<br />' + data.error, {theme:'jgrowl_error'});
                            }
			}
		});
	}
	</script>\";");
    eval("\$abp_solveit_head .= \"\n\n<!-- end: abp_solveit_head -->\";");
}

/**
 * Ajax part : just a few checks and call the action function
 * @global object $mybb
 * @global string $charset
 * @global object $db
 * @global object $lang
 * @return json
 */
function abp_solveit_ajax() {
    global $mybb, $charset, $db, $lang;
    $lang->load(CN_ABPSOLVEIT);
    if ($mybb->get_input('plugin') == CN_ABPSOLVEIT) {
        header("Content-type: application/json; charset={$charset}");
        $allowedgroups = explode(",", $settings[CN_ABPSOLVEIT . '_groups']);
        $my_post_key = $mybb->get_input('my_post_key');
        if (is_null($my_post_key) || !verify_post_check($my_post_key, true)) {
            echo json_encode(array('error' => $lang->abp_sierr1));
            return;
        }
        if ($mybb->get_input('type') == 'solve') {
            echo json_encode(abp_solveit_action($mybb->get_input('tid'), $mybb->get_input('pid')));
            return;
        }
    }
    return;
}

/**
 * Real action
 * Checks if user can solve and updates datas
 * @global object $mybb
 * @global object $db
 * @param int $tid
 * @param int $pid
 * @return array
 */
function abp_solveit_action($tid, $pid) {
    global $mybb, $db, $lang;
    $lang->load(CN_ABPSOLVEIT);
    $guser = array_merge(array($mybb->user['usergroup']), explode(',', $mybb->user['additionalgroups']));
    $s_forums = explode(',', $mybb->settings[CN_ABPSOLVEIT . '_forums']);
    $query = $db->simple_select('threads', '*', "tid=" . $db->escape_string($tid));
    $thread = $db->fetch_array($query);
    if ($mybb->settings[CN_ABPSOLVEIT . '_forums'] != -1 && !in_array($thread['fid'], $s_forums)) {
        // Not a forum where we can solve, exiting.
        return array('error' => $lang->abp_sierr2);
    }
    if (count(array_intersect(explode(',', $mybb->settings[CN_ABPSOLVEIT . '_groups']), $guser)) == 0 && (($mybb->settings[CN_ABPSOLVEIT . '_owner'] == 1 && $thread['uid'] != $mybb->user['uid']) || $mybb->settings[CN_ABPSOLVEIT . '_owner'] == 0 )) {
        return array('error' => $lang->abp_sierr3);
    }

    $qset = $db->simple_select(CN_ABPSOLVEIT . '_tuning', 'pid,close', 'fid=' . $thread['fid']);
    $solver = array('solve_pid' => $pid, 'closed' => 0, 'prefix' => 0);
    if ($db->num_rows($qset) == 0) {
        $solver['prefix'] = (int) $mybb->settings[CN_ABPSOLVEIT . '_prefix'];
        $solver['closed'] = (int) $mybb->settings[CN_ABPSOLVEIT . '_close'];
    } else {
        //return array('error' => 'Pfx = '.$db->fetch_field($qset, 'pid').' - Close = '.$db->fetch_field($qset, 'close', 0));
        $solver['prefix'] = $db->fetch_field($qset, 'pid', 0);
        $solver['closed'] = $db->fetch_field($qset, 'close', 0);
    }

    $query = $db->simple_select('posts', 'uid', "pid=" . $db->escape_string($pid));
    $s_uid = $db->fetch_field($query, 'uid');

    $db->update_query('threads', $solver, "tid=" . $db->escape_string($tid));
    $db->write_query("update " . TABLE_PREFIX . "users set solvecpt=solvecpt+1 where uid=" . $s_uid);


    return array('s_pid' => $thread['firstpost']);
}

/**
 * Clean the fine tuning table when settings are updated
 * @global object $mybb
 * @global object $db
 * @return void
 */
function abp_solveit_setting_update() {
    global $mybb, $db;
    if ((int) $mybb->settings[CN_ABPSOLVEIT . '_forums'] == -1) {
        // All forums selected
        return;
    }
    if ((int) $mybb->settings[CN_ABPSOLVEIT . '_forums'] == 0) {
        // No forum selected
        $db->delete_query(CN_ABPSOLVEIT . '_tuning', "1=1");
    } else {
        $db->delete_query(CN_ABPSOLVEIT . '_tuning', "fid NOT IN (" . $mybb->settings[CN_ABPSOLVEIT . '_forums'] . ")");
    }
    return;
}

/*
 * Array of new settings 
 */

function abp_solveit_new_settings() {
    global $lang;
    $lang->load(CN_ABPSOLVEIT);
    $new_settings['prefix'] = array(
        'name' => CN_ABPSOLVEIT . '_prefix', // Added in 0.2
        'title' => $lang->abp_sis5_tit,
        'description' => $lang->abp_sis5_desc,
        'optionscode' => 'php
    ".abp_solveit_listprefixes()."',
        'value' => '-1',
        'disporder' => 5
    );
    return $new_settings;
}
