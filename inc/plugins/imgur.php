<?php

/**
 * Imgur.com plugin : allow to upload an image to imgur
 * and add it in the post
 * (c) CrazyCat 2014 - 2019
 */
if (!defined("IN_MYBB"))
    die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');

define('CN_ABPIMGUR', str_replace('.php', '', basename(__FILE__)));

$plugins->add_hook('newreply_end', 'imgur_button');
$plugins->add_hook('newthread_end', 'imgur_button');
$plugins->add_hook('editpost_start', 'imgur_button');
$plugins->add_hook('private_send_start', 'imgur_button');
$plugins->add_hook('misc_start', 'imgur_popup');
$plugins->add_hook('showthread_start', 'imgur_loader');

/**
 * Displayed informations
 */
function imgur_info() {
    global $lang;
    $lang->load(CN_ABPIMGUR);
    return array(
        'name' => $lang->imgur_name,
        'description' => $lang->imgur_desc . '<a href=\'https://ko-fi.com/V7V7E5W8\' target=\'_blank\'><img height=\'30\' style=\'border:0px;height:30px;float:right;\' src=\'https://az743702.vo.msecnd.net/cdn/kofi1.png?v=0\' border=\'0\' alt=\'Buy Me a Coffee at ko-fi.com\' /></a>',
        'website' => 'https://www.g33k-zone.org',
        'author' => 'CrazyCat',
        'authorsite' => 'https://www.g33k-zone.org',
        'version' => '2.5',
        'compatibility' => '18*',
        'codename' => CN_ABPIMGUR
    );
}

/**
 * Install procedure
 * Just add the setting to MyBB
 */
function imgur_install() {
    global $db, $lang;
    $lang->load(CN_ABPIMGUR);

    // Setting group
    $settinggroups = array(
        'name' => CN_ABPIMGUR,
        'title' => $lang->imgur_setting_title,
        'description' => $lang->imgur_setting_description,
        'disporder' => 0,
        "isdefault" => 0
    );

    $db->insert_query('settinggroups', $settinggroups);
    $gid = $db->insert_id();

    // Settings
    $settings[] = array(
        'name' => CN_ABPIMGUR . '_client_id',
        'title' => $lang->imgur_ci_title,
        'description' => $lang->imgur_ci_description,
        'optionscode' => 'text',
        'value' => $lang->imgur_ci_default,
        'disporder' => 1
    );

    $dispopts = array(
        'r=' . $lang->imgur_disp_raw,
        't=' . $lang->imgur_disp_small,
        'm=' . $lang->imgur_disp_medium,
        'l=' . $lang->imgur_disp_large
    );
    $settings[] = array(
        'name' => CN_ABPIMGUR . '_display',
        'title' => $lang->imgur_display_title,
        'description' => $lang->imgur_display_description,
        'optionscode' => "select\n" . implode("\n", $dispopts),
        'value' => 'm',
        'disporder' => 2
    );

    $settings[] = array(
        'name' => CN_ABPIMGUR . '_link',
        'title' => $lang->imgur_link_title,
        'description' => $lang->imgur_link_description,
        'optionscode' => "yesno",
        'value' => 0,
        'disporder' => 3
    );

    $settings[] = array(
        'name' => CN_ABPIMGUR . '_quick',
        'title' => $lang->imgur_quick_title,
        'description' => $lang->imgur_quick_description,
        'optionscode' => "yesno",
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
}

/**
 * Uninstall function
 * Remove settings and templates
 * @see imgur_deactivate
 */
function imgur_uninstall() {
    global $db;
    $db->delete_query('settings', "name LIKE '" . CN_ABPIMGUR . "_%'");
    $db->delete_query('settinggroups', "name = '" . CN_ABPIMGUR . "'");
    rebuild_settings();
    imgur_deactivate();
}

/**
 * Checks if the plugin is installed or not
 */
function imgur_is_installed() {
    global $mybb;
    if (isset($mybb->settings['imgur_client_id'])) {
        return true;
    }
    return false;
}

/**
 * Plugin activation
 * Adds and modify the templates
 */
function imgur_activate() {
    global $db, $lang;

    $imgur_template = array();
    $imgur_template[] = array(
        'title' => CN_ABPIMGUR . '_button',
        'template' => '<div style="margin:auto; width: 170px; margin-top: 20px;">
		<div id="abp_imgur_zone" style="width:150px;height:50px;margin:auto; border: 3px dashed #BBBBBB; line-height:50px; text-align: center; background: #000000 url({$mybb->settings[\\\'bburl\\\']}/images/imgur.png) center no-repeat;"></div>
<script>
function imgurload() {
	$(document).on("dragenter", "#abp_imgur_zone", function() {
				$(this).css("border", "3px dashed red");
				return false;
	});
	 
	$(document).on("dragover", "#abp_imgur_zone", function(e){
				e.preventDefault();
				e.stopPropagation();
				$(this).css("border", "3px dashed red");
				return false;
	});
	 
	$(document).on("dragleave", "#abp_imgur_zone", function(e) {
				e.preventDefault();
				e.stopPropagation();
				$(this).css("border", "3px dashed #BBBBBB");
				return false;
	});
	$(document).on("drop", "#abp_imgur_zone", function(e) {
		if(e.originalEvent.dataTransfer){
		   if(e.originalEvent.dataTransfer.files.length) {
			   // Stop the propagation of the event
			   e.preventDefault();
			   e.stopPropagation();
			   // Main function to upload
			   upload(e.originalEvent.dataTransfer.files);
		   }
		}
		else {
		   $(this).css("border", "3px dashed #BBBBBB");
		}
		return false;
	});
	$("#abp_imgur_zone").click(function() {
		MyBB.popupWindow(\\\'/misc.php?action=imgur&popup=true&editor=MyBBEditor&modal=1\\\');
	});
}

function upload(files) {
	var myInsert = "";
	var dsize = \\\'{$mybb->settings[\\\'imgur_display\\\']}\\\';
	var dlink = {$mybb->settings[\\\'imgur_link\\\']};
	$("#abp_imgur_zone").css("background-image", "url({$mybb->settings[\\\'bburl\\\']}/images/loader.gif)");
	$.each(files, function(i, file) {
		if (!file || !file.type.match(/image.*/)) return;
		var fd = new FormData();
		fd.append("image", file);
		$.ajax({
			beforeSend:function (xhr) {
				xhr.setRequestHeader("Authorization", "Client-ID {$mybb->settings[\\\'imgur_client_id\\\']}");
			},
			url:"https://api.imgur.com/3/image.json",
			method:"POST",
			data:fd,
			dataType: "json",
			processData: false,
			contentType: false,
			success:function(data) {
				var link = data.data.link;
				link = link.replace("/^https?://", "");
				var code = "";
				if (dsize!="r") {
					pos = link.lastIndexOf(".");
					if (dlink==1) {
						code = "[url=" + link + "][img]" + link.substring(0, pos) + dsize + link.substring(pos) + "[/img][/url]";
					} else {
						code = "[img]" + link.substring(0, pos) + dsize + link.substring(pos) + "[/img]";
					}
				} else {
					code = "[img]" + link + "[/img]";
				}
				if (MyBBEditor) {
					MyBBEditor.insertText(code);
				} else {
					$("#message, #signature").focus();
					if ($("#message, #signature").hasOwnProperty("replaceSelectedText")) {
						$("#message, #signature").replaceSelectedText(code);
					} else {
						replaceSelectedText($("#message, #signature"), code);
					}
					
				}
			}
		});
		fd = null;
	});
	$("#abp_imgur_zone").css("background-image", "url({$mybb->settings[\\\'bburl\\\']}/images/imgur.png)")
	$("#abp_imgur_zone").css("border", "3px dashed #BBBBBB");
}
function replaceSelectedText(obj, content) {
	var myBegin = obj.prop("selectionStart");
	var myEnd = obj.prop("selectionEnd");
	var myText = obj.val();
	obj.val(myText.substr(0, myBegin)+content+myText.substr(myEnd));
}
$(function() {
	imgurload();
});
</script>
</div>',
        'sid' => -1,
        'version' => 1.0,
        'dateline' => TIME_NOW
    );

    $imgur_template[] = array(
        'title' => CN_ABPIMGUR . '_popup',
        'template' => '<div class="modal" style="width:200px">
	<div style="overflow-y: auto; max-height: 200px; background-color:rgb(43,43,43);padding:10px;text-align:center;" class="modal_{$pid}">
		<img src="{$mybb->settings[\\\'bburl\\\']}/images/imgur.png" /><br />
		<button onclick="$(\\\'#selector\\\').click()">{$lang->imgur_select}</button>
		<input id="selector" style="visibility:hidden;position:absolute;top:0;" type="file" onchange="pupload(this.files)" accept="image/*" multiple>
		<p id="uploading" style="display:none;"><img src="{$mybb->settings[\\\'bburl\\\']}/images/loader.gif" border="0" /></p>
	</div>
	<script type="text/javascript">
	function pupload(files) {
		upload(files);
		$.modal.close();
	}
	
	</script>
</div>',
        'sid' => -1,
        'version' => 1.1,
        'dateline' => TIME_NOW
    );

    foreach ($imgur_template as $row) {
        $db->insert_query("templates", $row);
    }

    require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';
    find_replace_templatesets('newreply', '#{\$smilieinserter}#', '{\$smilieinserter}<!-- Imgur -->{$imgur_button}<!-- /Imgur -->');
    find_replace_templatesets('newthread', '#{\$smilieinserter}#', '{\$smilieinserter}<!-- Imgur -->{$imgur_button}<!-- /Imgur -->');
    find_replace_templatesets('editpost', '#{\$smilieinserter}#', '{\$smilieinserter}<!-- Imgur -->{$imgur_button}<!-- /Imgur -->');
    find_replace_templatesets('private_send', '#{\$smilieinserter}#', '{\$smilieinserter}<!-- Imgur -->{$imgur_button}<!-- /Imgur -->');
}

/**
 * Plugin deactivation
 * Removes the templates
 */
function imgur_deactivate() {
    global $db;
    $db->delete_query('templates', "title LIKE '" . CN_ABPIMGUR . "_%'");
    require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';
    find_replace_templatesets('newreply', '#\<!--\sImgur\s--\>(.+)\<!--\s/Imgur\s--\>#is', '', 0);
    find_replace_templatesets('newthread', '#\<!--\sImgur\s--\>(.+)\<!--\s/Imgur\s--\>#is', '', 0);
    find_replace_templatesets('editpost', '#\<!--\sImgur\s--\>(.+)\<!--\s/Imgur\s--\>#is', '', 0);
    find_replace_templatesets('private_send', '#\<!--\sImgur\s--\>(.+)\<!--\s/Imgur\s--\>#is', '', 0);
}

//########## FUNCTIONS ##########
function imgur_loader() {
    global $mybb;
    if (isset($mybb->settings[CN_ABPIMGUR . '_quick']) && $mybb->settings[CN_ABPIMGUR . '_quick'] == 1) {
        imgur_button();
    }
}

/**
 * Displays the button
 */
function imgur_button() {
    global $db, $mybb, $lang, $templates, $theme, $imgur_button;
    $lang->load(CN_ABPIMGUR);
    eval("\$imgur_button .= \"" . $templates->get('imgur_button') . "\";");
}

/**
 * Displays the popup
 */
function imgur_popup() {
    global $mybb, $db, $headerinclude, $lang, $templates;
    if ($mybb->input['action'] == "imgur") {
        $lang->load(CN_ABPIMGUR);
        eval("\$imgur_popup = \"" . $templates->get('imgur_popup', 1, 0) . "\";");
        output_page($imgur_popup);
    }
}
