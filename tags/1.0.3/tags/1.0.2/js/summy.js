/**
 * @package		WP-Summy
 * @author		Christodoulos Tsoulloftas
 * @copyright   Copyright 2013, http://www.komposta.net
 */
jQuery(document).ready(function($) {
    $('#summyWork').click(function() {
        $('#content-html').click();
        $('#summyWork').prop('disabled', true);
        $('#summySpinner').show();

        var data = {
            action: 'summy',
            title: $('#title').val(),
            content: $('#content').val(),
            language: $('#summyLanguage').val(),
            rate: $('#summyRate').val(),
            minWordsLimit: $('#summyMinWordsLimit').val(),
            maxWordsLimit: $('#summyMaxWordsLimit').val(),
            termScore: $('#summyTermScore').val(),
            positionScore: $('#summyPositionScore').val(),
            TW: $('#summyTW').val(),
            PW: $('#summyPW').val(),
            KW: $('#summyKW').val(),
            _summynonce: $('#_summynonce').val()
        };

        jQuery.post(ajaxurl, data, function(response) {
            if (response.error) {
                alert(response.error);
            }
            else {
                $('#excerpt').val(response.data.summary)
            }
            $('#summyWork').prop('disabled', false);
            $('#summySpinner').hide();
        });
    });
});