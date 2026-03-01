(function ($) {
    'use strict';

    function openUploader(targetSelector) {
        var target = $(targetSelector);
        var preview = $(targetSelector + '_preview');
        var removeBtn = preview.closest('.yeti-login-image-field').find('.yeti-login-remove');

        var frame = wp.media({
            title: 'Select Image',
            button: { text: 'Use Image' },
            multiple: false,
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            target.val(attachment.url);
            preview.html('<img src="' + attachment.url + '" alt="" />').addClass('has-image');
            removeBtn.show();
        });

        frame.open();
    }

    $(function () {
        // Upload button
        $(document).on('click', '.yeti-login-upload', function (e) {
            e.preventDefault();
            openUploader($(this).data('target'));
        });

        // Click preview area to upload
        $(document).on('click', '.yeti-login-preview', function (e) {
            e.preventDefault();
            openUploader($(this).data('target'));
        });

        // Remove image
        $(document).on('click', '.yeti-login-remove', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var targetSelector = $(this).data('target');
            var preview = $(targetSelector + '_preview');

            $(targetSelector).val('');
            preview.html('<span class="placeholder">Click to upload</span>').removeClass('has-image');
            $(this).hide();
        });
    });
})(jQuery);
