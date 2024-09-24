jQuery(function($) {

    $('form[id^=rm-subscriptionform-]').on('submit', function() {

        var self = $(this),
            data = self.serialize();

        self.find('input[type="submit"]').hide();
        self.find('input').prop('disabled', true);
        self.find('.spinner').show();

        $
            .post({
                url: self.attr('action'),
                data: data,
                dataType: 'json',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                }
            })
            .always(function() {

                self.find('.rm-error').hide();

                self.find('input[type="submit"]').show();
                self.find('input').prop('disabled', false);
                self.find('.spinner').hide();

            })
            .then(function(xhr) {

                self
                    .removeClass('rm-subscribe-error')
                    .addClass('rm-subscribe-success');

                self.find('.rm-success-container').remove();

                var el = $('<li class="rm-success-container"><span class="rm-success"></span></li>');
                el.find('.rm-success').html(rmwidget.msg_subscribe_success);
                el.prependTo(self.find('ul'));

                self[0].reset();

            })
            .fail(function(xhr) {

                self
                    .addClass('rm-subscribe-error')
                    .removeClass('rm-subscribe-success');

                var hasGlobalError = false,
                    hasLocalError = false;

                if (xhr.status === 422) {

                    $.each(xhr.responseJSON.validation_messages, function(fieldName, message) {

                        var cls = 'rm-error-' + fieldName,
                            el = self.find('.' + cls),
                            target = [];

                        if (el.length) {
                            el.html(message).show();
                            hasLocalError = true;
                        } else {

                            target = self.find('.rm-' + fieldName + '-error-after,[name="' + fieldName + '"]').first();

                            if (target.length) {

                                $('<span class="rm-error"></span>')
                                    .html(message)
                                    .addClass(cls)
                                    .insertAfter(target);

                                hasLocalError = true;

                            } else {
                                hasGlobalError = true;
                            }

                        }

                    });

                } else {
                    hasGlobalError = true;
                }

                if (hasGlobalError && !hasLocalError) {

                    var el = $('<li class="rm-global-errors-container"><span class="rm-error"></span></li>');
                    el.find('.rm-error').html(rmwidget.msg_an_error_occurred);
                    el.prependTo(self.find('ul'));

                }

            });

        return false;

    });

});