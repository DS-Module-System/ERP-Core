
import tinymce from 'tinymce';

// DOM model
import 'tinymce/models/dom/model'
// Theme
import 'tinymce/themes/silver';
// Toolbar icons
import 'tinymce/icons/default';

// importing the plugin js.
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/anchor';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/autoresize';
import 'tinymce/plugins/autosave';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/code';
import 'tinymce/plugins/codesample';
import 'tinymce/plugins/directionality';
import 'tinymce/plugins/emoticons';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/help';
import 'tinymce/plugins/image';
import 'tinymce/plugins/importcss';
import 'tinymce/plugins/insertdatetime';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/media';
import 'tinymce/plugins/nonbreaking';
import 'tinymce/plugins/pagebreak';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/quickbars';
import 'tinymce/plugins/save';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/table';
import 'tinymce/plugins/template';
import 'tinymce/plugins/visualblocks';
import 'tinymce/plugins/visualchars';
import 'tinymce/plugins/wordcount';

// importing plugin resources
import 'tinymce/plugins/emoticons/js/emojis';

// Content styles, including inline UI like fake cursors
// All the above CSS files are loaded on to the page but these two must
// be loaded into the editor iframe so they are loaded as strings and passed
// to the init function.
// import contentCss from '!!raw-loader!tinymce/skins/content/default/content.min.css';
// import contentUiCss from '!!raw-loader!tinymce/skins/ui/oxide/content.min.css';

var TinyMCEDefaults = {

	plugins: [
		'advlist',
		'autolink',
		'lists',
		'link',
		'image',
		'charmap',
		'preview',
		'anchor',
		'searchreplace',
		'visualblocks',
		'code',
		'fullscreen',
		'media',
		'table',
		'importcss',
	],
	relative_urls: false,
	remove_script_host: true,
	// content_css: '//www.tiny.cloud/css/codepen.min.css',
	menubar: "edit insert view format table",
	toolbar: "formatselect | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | undo redo | image code ",
	file_picker_callback: myFileBrowser,

	block_formats: "Paragraph=p;Header 1=h1;Header 2=h2;Header 3=h3;Header 4=h4;Header 5=h5;Preformatted text=pre;Address=address;div=div",

	style_formats: [
		{
			title: 'Image Left', selector: 'img', styles: {
				'float': 'left',
				'margin': '0px 5px 2px 0px'
			}
		},
		{
			title: 'Image Right', selector: 'img', styles: {
				'float': 'right',
				'margin': '0px 0px 2px 5px'
			}
		}
	],

	// content_css: '/assets/main.css?' + new Date().getTime(),
	// importcss_selector_filter: /\.my/,
	body_class: "BETinyMCEBody",
	images_upload_url: '/tiny/mce',
	image_advtab: true,
	images_reuse_filename: true,
	automatic_uploads: true,
	file_picker_types: 'image',
	base_url: '/libs/tinymce'
	// skin: false,
	// content_css: false
	// external_plugins: {
	//     "ImageGallery": `${baseUrl}/public/ImageGallery/plugin.min.js`,
	// 	"BootstrapTools": `${baseUrl}/public/BootstrapTools/plugin.min.js`,
	// },
	// apiKey: "CR64DFLT",
	/*file_browser_callback: function(field_name, url, type, win) {
	tinymce.activeEditor.windowManager.open({
	title: "File Manager",
	url: '/be/fileman/popup.php',
	width: 700,
	height: 600
	}, {
	action: type,
	target_ctrl: win.document.getElementById(field_name)
	});
	}*/
};

function myFileBrowser(callback, value, meta) {

	var type = meta.filetype;
	var cmsURL = "/manager/?module=1&conf=tiny";
	if (cmsURL.indexOf("?") < 0) {
		cmsURL = cmsURL + "?type=" + type;
	}
	else {
		cmsURL = cmsURL + "&type=" + type;
	}

	var windowManagerCSS = '<style type="text/css">' +
		'.tox-dialog {max-width: 100%!important; width:97.5%!important; overflow: hidden; height:95%!important; border-radius:0.25em;}' +
		'.tox-dialog__body { padding: 0!important; }' +
		'.tox-dialog__body-content > div { height: 100%; overflow:hidden}' +
		'</style > ';

	window.tinymceCallBackURL = '';
	window.tinymceWindowManager = tinymce.activeEditor.windowManager;
	tinymceWindowManager.open({
		title: 'File Manager',
		body: {
			type: 'panel',
			items: [{
				type: 'htmlpanel',
				html: windowManagerCSS + '<div style="padding-bottom:56.25%; position:relative; display:block; width: 100%"><iframe id="myframe" src="' + cmsURL + '"  frameborder="0" style="width:100%; height:100%; position:absolute; top: 0; left: 0;" ></iframe></div>'
			}]
		},
		buttons: [
			{
				type: 'submit',
				text: 'OK',
				primary: true,
			}
		],
		onSubmit: function() {
			var result = '';
			var checked = $('#myframe').contents().find('.checkbox:checked');
			var parent = checked.parent();
			if(parent.is('td')) {
				var grandparent = parent.parent();
				result = grandparent.find('img').attr('src');
			} else {
				result = parent.find('img').attr('src');
			}
			result = result.split('?')[0];
			result = result.replace(/\\/g,  "/");
			tinymceWindowManager.close(callback(result));
		},
		onClose: function () {
			if (tinymceCallBackURL != '')
				callback(tinymceCallBackURL, {});
		},

	});
	return false;
}

$(document).ready(function () {
	(function () {
		$("textarea[data-tinymce]").each(function () {
			var obj = $(this);
			var data = $(this).attr("data-tinymce");
			data = (typeof (data) != 'undefined' && data != "") ? JSON.parse(data) : {};

			if(document.documentElement.getAttribute('data-bs-theme') == 'dark') {
				data['skin'] = 'oxide-dark';
				data['content_css'] = 'dark';
			}

			var options = {};
			$.extend(options, TinyMCEDefaults, data);
			options.selector = '#' + obj.attr('id');
			// options.width = $(options.selector).width();
			// if (options.width < 600)
			// 	options.width = 600;
			// options.height = $(options.selector).height();
			// if (options.height < 400)
			// 	options.height = 400;
			tinymce.init(options);
		});
		$(window).trigger('createTinyMCE', {});
	})();
});

