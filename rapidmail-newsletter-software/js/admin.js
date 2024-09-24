jQuery(function($) {

    var frm = $('#wpbody-content form');

    $('#rm-api-version').change(function() {

        frm
            .find('input[type=text],input[type=password],textarea,select')
            .prop('readonly', true);

        frm.find('input[type=submit]').prop('disabled', true);
        frm.submit();

    });

    $('#comment_subscription_active').click(function() {

        $('#comment_subscription_label')
            .val('')
            .prop('readonly', !$(this).prop('checked'));

    });

});