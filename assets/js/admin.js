(function ($) {
    'use strict';

    $(function () {
        // Media uploader
        $('.yeti-login-upload').on('click', function (e) {
            e.preventDefault();
            var button = $(this);
            var target = $(button.data('target'));
            var preview = $(button.data('target') + '_preview');

            var frame = wp.media({
                title: 'Select Image',
                button: { text: 'Use Image' },
                multiple: false,
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                target.val(attachment.url);
                preview.html('<img src="' + attachment.url + '" alt="" />');

                // Add remove button if not present
                if (!button.next('.yeti-login-remove').length) {
                    button.after(
                        '<button type="button" class="button yeti-login-remove" data-target="' +
                            button.data('target') +
                            '">Remove</button>'
                    );
                }
            });

            frame.open();
        });

        // Remove image
        $(document).on('click', '.yeti-login-remove', function (e) {
            e.preventDefault();
            var button = $(this);
            var target = $(button.data('target'));
            var preview = $(button.data('target') + '_preview');

            target.val('');
            preview.empty();
            button.remove();
        });
    });
})(jQuery);
