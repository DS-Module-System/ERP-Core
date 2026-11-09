
import {Modal} from "bootstrap"

function showLoader() {
    $('#loader').addClass('show');
}
function removeLoader() {
    $('#loader').removeClass('show');
}

$(document).ready(function () {

    /* GET LIST DATA */
    $('.api-table').each(function (k, v) {
        var url = $(v).attr('data-list-url');
        if ($(v).attr('data-api-options')) {
            if (url.indexOf('?') > 0) {
                url += '&api_options=' + encodeURIComponent($(v).attr('data-api-options'));
            } else {
                url += '?api_options=' + encodeURIComponent($(v).attr('data-api-options'));
            }
        }

        var request = $.ajax({
            url: url,
            method: "GET",
            dataType: "html",
            headers: {"X-Requested-With": "DSModalRequest"}
        });
        request.done(function (msg) {
            removeLoader();
            $(v).find('.list-container-body').html(msg);
        });
    });

	/* UPDATE */
	$(document).on('click', '.api-table .edit-link, .api-edit', function (e) {
        e.preventDefault();
		manipulateForm($(this));
	});

	$(document).on('update-table', '.api-table', function (e) {
        
        var request = $.ajax({
            url: $(this).attr('data-list-url'),
            method: "GET",
            dataType: "html",
            headers: {"X-Requested-With": "DSModalRequest"}
        });
        request.done(function (msg) {
            removeLoader();
            $(e.currentTarget).find('.list-container-body').html(msg);
        });
    });

    /* CREATE */
	$(document).on('click', '.api-new', function (e) {
        e.preventDefault();
        manipulateForm($(this));
    });

    function onClickSave(e, formButtonEl) {
        e.preventDefault();
        showLoader();
        var saveBtn = $(e.target).closest('.api-save');
        var formData = new FormData($('#apiForm form')[0]);
        formData.append(saveBtn.attr('name'), saveBtn.val());
        var request = $.ajax({
            url: saveBtn.attr('data-url'),
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            // data: {formData: $('#apiForm form').serialize(), formClass: $(this).val()},
            dataType: "html",
            headers: {"X-Requested-With": "DSModalRequest"}
        });

        request.done(function (html) {

            removeLoader();

            const responseHtml = document.createElement('div');
            responseHtml.innerHTML = html;
            let hasFormError = false;
            hasFormError = responseHtml.querySelector('.invalid-tooltip') !== null;
            if(hasFormError) {
                $('#apiForm').html(html);
                $(document).trigger('modal-form-load');
            } else {
                $('#apiForm form').closest('.form-modal').remove();
                $('.api-table').trigger('update-table', 'update');
                Modal.getOrCreateInstance($('#apiForm')).hide();
                window.location.reload();
            }
        });

        request.fail(function (jqXHR, textStatus) {
            removeLoader();
            $('#apiForm').html(jqXHR.responseText);
            $(document).trigger('modal-form-load');
        });
    }

	$(document).on('click', '.api-save', function (e) {
		onClickSave(e, $(this));
	});

    function manipulateForm(formButtonEl) {
        var $apiForm = $('#apiForm');
        var url = formButtonEl.attr('data-form-url');

        showLoader();
        var request = $.ajax({
            url: url,
            method: "GET",
            dataType: "html",
            headers: {"X-Requested-With": "DSModalRequest"}
        });

        request.done(function (msg) {
            removeLoader();
            // $( "#log" ).html( msg );
            $apiForm.html(msg);
            Modal.getOrCreateInstance($apiForm).show();

            $(document).trigger('modal-form-load');
        });

        request.fail(function (jqXHR, textStatus) {
            removeLoader();
            window.location = url;
            // $('#apiForm').html(jqXHR.responseText);
            // $(document).trigger('modal-form-load');
        });
    }

	$(document).on('click', '.api-delete', function (e) {
        e.preventDefault();
        showLoader();
        var saveBtn = $(e.target).closest('.api-delete');
        var formData = new FormData($('#apiForm form')[0]);
        formData.append(saveBtn.attr('name'), saveBtn.val());
        var request = $.ajax({
            url: saveBtn.attr('data-url'),
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            // data: {formData: $('#apiForm form').serialize(), formClass: $(this).val()},
            dataType: "html",
            headers: {"X-Requested-With": "DSModalRequest"}
        });

		request.done(function (html) {
            removeLoader();

            const responseHtml = document.createElement('div');
            responseHtml.innerHTML = html;
            let hasFormError = false;
            hasFormError = responseHtml.querySelector('.invalid-tooltip') !== null;
            if(hasFormError) {
                $('#apiForm').html(html);
                $(document).trigger('modal-form-load');
            } else {
                $('#apiForm form').closest('.form-modal').remove();
                $('.api-table').trigger('update-table', 'update');
                Modal.getOrCreateInstance($('#apiForm')).hide();
                window.location.reload();
            }
		});

		request.fail(function (response) {
			if (response.responseJSON.code === 405) {
				var errorDiv = $('.api-error');
				errorDiv.addClass('active');
				errorDiv.text(response.responseJSON.message);
			}
			removeLoader();
			$('.api-table').trigger('update-table', 'update');
		});
	});
});
