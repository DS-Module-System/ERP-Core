
// import 'jquery-ui';
// import 'jquery-ui/ui/widgets/sortable';
// import $ from "jquery";

import './../libs/jquery-ui-1.13.2/external/jquery/jquery.js'
import './../libs/jquery-ui-1.13.2/jquery-ui.min.js'
import './../libs/jquery.fileupload.js';

// import Modal from "bootstrap/js/src/modal"


String.prototype.addSetVal = function (val) {
	if (this == '')
		return val;
	return this + ',' + val;
};

String.prototype.removeSetVal = function (val) {
	if (this == '')
		return '';
	return this.split(',').filter(function (v) {
		return v != val;
	}).join(',');
};

function showLoader() {
	$('#loader').addClass('show');
};

function removeLoader() {
	$('#loader').removeClass('show');
};


function onClickSave (e, formButtonEl) {
	e.preventDefault();
	showLoader();
	var request = $.ajax({
		url: $(e.target).closest('.api-save').attr('data-url'),
		method: "POST",
		data: {formData: $('#apiFormFile form').serialize(), formClass: $(this).val()},
		dataType: "html"
	});

	request.done(function (msg) {
		removeLoader();
		Modal.getOrCreateInstance(document.getElementById('apiFormFile')).hide();
	});

	request.fail(function (jqXHR, textStatus) {
		removeLoader();
		$('#apiFormFile').html(jqXHR.responseText);
		$('.api-save').on('click', function (e) {
			onClickSave(e, formButtonEl);
		});
	});
};

$.widget('custom.managedfiles', {

	options: {

		url: null,
		multiple: false,
		acceptFileExtensions: '',
		maxFileSize: 10000000,

		image: false
	},

	input: null,
	uploaded: null,
	current: null,

	filesInfo: {}, //

	_create: function () {
		var self = this;

		if (this.options.image) {
			if(this.options.acceptFileExtensions == '') {
				this.options.acceptFileExtensions = 'png,jpg,jpeg';
			}
		} else {
			if (this.options.acceptFileExtensions == '') {
				this.options.acceptFileExtensions = 'pdf';
			}
		}

		this.input = this.element;
		this.options.disabled = typeof this.element.attr('disabled') !== 'undefined';

		this.element.css('display', 'none');

		var q = $('<div class="managedfiles"><div class="uploaded"></div><button type="button"><span class="icon"><i class="mdi mdi-plus-box"></i></span><input type="file" name="" style="width:1px; height:1px; opacity:0; position:fixed; left:-100px; top:-100px;" /></button><div class="current"></div></div>');
		this.uploaded = q.find('.uploaded');
		this.current = q.find('.current');
		if (this.options.disabled) {
			q.find('button').remove();
		} else {

			q.find('button').on('click', function () {
				q.find('input').first()[0].click();
			});

			if (this.options.multiple) {
				q.find('button').find('input').attr('multiple', 'multiple');
				this.uploaded.sortable({
					handle: ".handle",
					update: function (event, ui) {
						self.input.val($(this).sortable("toArray", {attribute: "data-fileid"}).join(','));
					}
				});
			}
		}

		this.element.after(q);

		if (this.input.val() != '') {

			$.get(this.options.url + '/multiple/' + this.input.val(), {}, function (response) {
				for (var id in response) {
					self._initFile(response[id]);
				}
			}, 'json');
		}

		if (!this.options.disabled) {

			var fileuploadOptions = {
				dataType: 'json',
				limitMultiFileUploads: 1,
				sequentialUploads: true,
				//maxFileSize:100,
				imageTypesTest: /(\.|\/)(gif|jpe?g|png)$/i,
				formData: {options: $.param({image: self.options.image}, false)},

				add: function (e, data) {
					var file = data.files[0];
					if (self.options.maxFileSize && self.options.maxFileSize < file.size) {
						file.error = 'File "' + file.name + '" is larger that ' + self.formatFileSize(self.options.maxFileSize);
					} else if (self.options.acceptFileExtensions && !RegExp('\.(' + self.options.acceptFileExtensions.replace(/,/g, '|') + ')$', 'i').test(file.name)) {
						file.error = 'Invalid file extension for "' + file.name + '". Allowed extensions are: ' + self.options.acceptFileExtensions;
					}

					if (file.error) {
						alert(file.error);
						return;
					}

					data.process().done(function () {
						data.submit();
					});
				},

				send: function (e, data) {
					data.files[0].label = $('<div class="uploading" />').text('Uploading ' + data.files[0].name).appendTo(self.current);
				},

				done: function (e, data) {
					if (!data.result)
						data.result = {};
					if (data.result.fileinfo) {
						if (self.options.multiple) {
							self.input.val(self.input.val().addSetVal(data.result.fileinfo.id));
						} else {
							self.input.val(data.result.fileinfo.id);
							self.uploaded.empty();
						}
						self._initFile(data.result.fileinfo);
						data.files[0].label.remove();
					} else {
						$('<span/>').text(' - Upload error: ' + data.result.message).appendTo(data.files[0].label);
						data.files[0].label.addClass('error');
					}
				},

				fail: function (e, data) {
					$('<span/>').text('Upload fail: ' + data.textStatus).appendTo(data.files[0].label);
					data.files[0].label.addClass('error');
				},

			};

			$.extend(fileuploadOptions, this.options);

			var urlQuery = "";
			if(this.options['path'] !== undefined && this.options['path'] !== undefined !== '') {
				urlQuery = '?path=' + encodeURIComponent(this.options.path);
			}

			fileuploadOptions.url += '/create' + urlQuery;
			// console.log(fileuploadOptions);

			$(q.find('input')).fileupload(fileuploadOptions).prop('disabled', !$.support.fileInput)
				.parent().addClass($.support.fileInput ? undefined : 'disabled');

		}
	},

	_initFile: function (fileinfo) {
		var self = this;

		if (!fileinfo)
			return;

		var file = $('<div class="file-info" />').attr('data-fileid', fileinfo.id);
		if (!this.options.disabled && this.options.multiple) {
			file.append('<div class="handle"><span class="icon move"><i class="mdi mdi-arrow-all"></i></span></div>');
		}

		if (this.options.image) {
			file.addClass('image-file-info');
		}

		if (fileinfo.url_optimized) {
			// file.append($('<img />').attr('src', fileinfo.thumb).attr('title', fileinfo.width + 'X' + fileinfo.height + ' ' + fileinfo.name)).append('<br />');
			file.append($('<div class="img"></div>').attr('style', 'background-image: url("' + fileinfo.url_optimized + '")'));
		} else if (fileinfo.type.indexOf("audio") != -1) {
			file.append($('<div class="fileupload-icon audio"></div>').attr('title', fileinfo.name)).append('<br />');
		} else {
			var fileTypes = ['bmp', 'cpp', 'doc', 'docx', 'dxf', 'exe', 'gif', 'html', 'jpg', 'mov', 'odt', 'pdf', 'png', 'ppt', 'psd', 'sql', 'tiff', 'txt', 'wav', 'xls', 'xlsx', 'xml', 'zip'],
				isSetIcon = false;
			for (var i in fileTypes) {
				if (fileinfo.type.indexOf(fileTypes[i]) != -1) {
					file.append($('<div class="fileupload-icon ' + fileTypes[i] + '"></div>"').attr('title', fileinfo.name));
					isSetIcon = true;
					break;
				}
			}
			if (isSetIcon === false) {
				file.append($('<div class="fileupload-icon default"></div>"').attr('title', fileinfo.name));
			}
		}
		file.append($('<div class="file-info-bottom-line"></div>'));

		if (!this.options.disabled) {
			$('<a href="#" title="Delete" class="delete"><i class="mdi mdi-delete"></i></a>').on('click', function (event) {

				$('.file-info[data-fileid="'+fileinfo.id+'"]').remove();

				if(self.options.multiple === true) {
					$.ajax({
						url: self.options.url + '/' + fileinfo.id + '/delete',
						method: "POST",
						async: false
					}).done(function (msg) {
						self.input.val(self.input.val().removeSetVal(fileinfo.id));
						event.preventDefault();
					});
				} else {
					self.input.val(self.input.val().removeSetVal(fileinfo.id));
				}

				return false;
			}).appendTo(file);
		}

		file.append('<a href="' + fileinfo.url + '" title="View" class="view" target="_blank"><i class="mdi mdi-eye"></i></a>');
		$('<a href="#" data-file-id="' + fileinfo.id + '" title="Edit" class="edit managedfiles-edit-btn" ><i class="mdi mdi-file-edit"></i></a>').on('click', function (event) {
			event.preventDefault();
			self.manipulateForm($(this));
			return false;
		}).appendTo(file);

		self.uploaded.append(file);
	},

	formatFileSize: function ($size) {
		var $unit = 'bytes';
		if ($size > 1024) {
			$size /= 1024;
			$unit = 'KB';
		}
		if ($size > 1024) {
			$size /= 1024;
			$unit = 'MB';
		}
		if ($size > 1024) {
			$size /= 1024;
			$unit = 'GB';
		}

		var $prec = 0;
		if ($size < 10)
			$prec = 2;
		if ($size < 100)
			$prec = 1;

		return Math.round($size, $prec) + ' ' + $unit;
	},


	manipulateForm: function(formButtonEl) {
		var $apiForm = $('#apiFormFile');

		showLoader();
		var request = $.ajax({
			url: this.options.url + '/api-edit/' + formButtonEl.attr('data-file-id'),
			method: "GET",
			dataType: "html"
		});

		request.done(function (msg) {
			removeLoader();
			// $( "#log" ).html( msg );
			// console.log($apiForm.html(msg));
			$apiForm.html(msg);
			Modal.getOrCreateInstance(document.getElementById('apiFormFile')).show();
			$('.api-save').on('click', function (e) {
				onClickSave(e, formButtonEl);
			});
			$('.btn-close').on('click', function (e) {
				Modal.getOrCreateInstance(document.getElementById('apiFormFile')).hide();
			});

		});
	},

});



$(document).ready(function () {
	(function () {
		$("input[data-managedfiles]").each(function () {

			var data = $(this).attr("data-managedfiles");

			data = (typeof (data) != 'undefined' && data != "") ? JSON.parse(data) : {};

			var options = {url: (typeof window.FILE_UPLOAD_BASE_URL !== 'undefined' ? window.FILE_UPLOAD_BASE_URL : '/files')};

			$.extend(options, data);

			$(this).managedfiles(options);
		});
		$(window).trigger('createManagedFiles', {});
	})();
});
