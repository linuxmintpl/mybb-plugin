/* New button to thankyou mybb system
** Compatibility: MyBB 1.8.x
** Author : Dark Neo
** Version: 2.5.5
** Plugin : Thank You MyBB System.
**/

$(document).ready(function($) {
	'use strict';

	var $document = $(document);

	/***********************
	 * Add custom MyBB CSS *
	 ***********************/
	$('<style type="text/css">' +
		'.sceditor-dropdown { text-align: ' + ($('body').css('direction') === 'rtl' ? 'right' :'left') + '; }' +
		'.sceditor-button-'+hide_tag+' div  { background: url(images/thx/oculto.png) no-repeat; }' +
	'</style>').appendTo('body');


	/**********************
	 * Add BBcode Command *
	 **********************/
	$.sceditor.plugins.bbcode.bbcode.set(hide_tag, {
		allowsEmpty: true,
		isInline: false,   
		format: function(element, content) {
			content = $("div#"+hide_tag+"c");
			return '['+hide_tag+']' + content + '[/'+hide_tag+']';
		},
		html: function (token, attrs, content) {
			return '<div data-desc="'+hide_tag+'" style="border: 1px solid #ccc; border-radius: 4px; padding: 10px;" id="'+hide_tag+'c"><span style="display: none;">['+hide_tag+']</span>' + content + '<span style="display: none;">[/'+hide_tag+']</span></div>';
		},
		breakStart: true,
		breakEnd: true
	});
		
	/***************
	 * Add command *
	 ***************/
	$.sceditor.command.set(hide_tag, {_dropDown: function (editor, caller, html) {
		var $content;
		$content = $(
			'<div>' +
				'<label for="code">' + editor._(hide_tag_content+':') + '</label> ' +
				'<textarea type="text" id="'+hide_tag+'" />' +                    
			'</div>' +
			'<div><input type="button" class="button" value="' + editor._('Insert') + '" /></div>'
		);

		setTimeout(function() {
			$content.find('#'+hide_tag).focus();
		},100);

		$content.find('.button').click(function (e) {
			var val = $content.find('#'+hide_tag).val(),
				before = '['+hide_tag+']',
				end = '[/'+hide_tag+']';

			if (html) {
				before = before + html + end;
				end = null;
			}
			else if (val) {
				before = before + val + end;
				end = null;
			}

			editor.insert(before, end);
			editor.closeDropDown(true);
			e.preventDefault();
		});
		editor.createDropDown(caller, 'insert'+hide_tag, $content);
		},
		exec: function (caller) {
			$.sceditor.command.get(hide_tag)._dropDown(this, caller);
		},
		txtExec: ['['+hide_tag+']', '[/'+hide_tag+']'],
		tooltip: hide_tag_title+':'
	}); 
}); 	