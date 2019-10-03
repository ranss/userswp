jQuery(window).load(function() {

    // Enable auth modals
    uwp_init_auth_modal();

    // select2 selects
    if (jQuery("select.uwp_select2").length > 0) {
        jQuery("select.uwp_select2").select2();
        jQuery("select.uwp_select2_nostd").select2({
            allow_single_deselect: 'true'
        });
    }
});


(function( $, window, undefined ) {
    $(document).ready(function() {
        var showChar = uwp_localize_data.uwp_more_char_limit;
        var ellipsestext = uwp_localize_data.uwp_more_ellipses_text;
        var moretext = uwp_localize_data.uwp_more_text;
        var lesstext = uwp_localize_data.uwp_less_text;
        $('.uwp_more').each(function() {
            var content = $.trim($(this).text());

            if(content.length > showChar) {

                var c = content.substr(0, showChar);
                var h = content.substr(showChar, content.length - showChar);
                var html = c + '<span class="uwp_more_ellipses">' + ellipsestext+ '&nbsp;</span><span class="uwp_more_content"><span>' + h + '</span>&nbsp;&nbsp;<a href="" class="uwp_more_link">' + moretext + '</a></span>';

                $(this).html(html);
            }

        });

        $(".uwp_more_link").click(function(){
            if($(this).hasClass("uwp_less")) {
                $(this).removeClass("uwp_less");
                $(this).html(moretext);
            } else {
                $(this).addClass("uwp_less");
                $(this).html(lesstext);
            }
            $(this).parent().prev().toggle();
            $(this).prev().toggle();
            return false;
        });
    });
}( jQuery, window ));


(function( $, window, undefined ) {

    var uwp_popup_type;

    $(document).ready( function() {
        $( '.uwp-profile-modal-form-trigger' ).on( 'click', function( event ) {
            event.preventDefault();

            uwp_popup_type = $( this ).data( 'type' );

            // do something with the file here
            var data = {
                'action': 'uwp_ajax_image_crop_popup_form',
                'type': uwp_popup_type
            };

            var container = jQuery('#uwp-popup-modal-wrap');
            container.show();

            jQuery.post(uwp_localize_data.ajaxurl, data, function(response) {
                $(document.body).append("<div id='uwp-modal-backdrop'></div>");
                container.replaceWith(response);
            });
        });
    });

    $(document).ready(function() {
        $(".uwp_upload_file_remove").click(function(event){
            event.preventDefault();

            var htmlvar =  $( this ).data( 'htmlvar' );
            var uid =  $( this ).data( 'uid' );

            var data = {
                'action': 'uwp_upload_file_remove',
                'htmlvar': htmlvar,
                'uid': uid
            };

            jQuery.post(uwp_localize_data.ajaxurl, data, function(response) {
                $("#"+htmlvar+"_row").find(".uwp_file_preview_wrap").remove();
                $("#"+htmlvar).closest("td").find(".uwp_file_preview_wrap").remove();
                if($('input[name='+htmlvar+']').data( 'is-required' )){
                    $('input[name='+htmlvar+']').prop('required',true);
                }
            });
        });
    });

}( jQuery, window ));

(function( $, window, undefined ) {
    $(document).ready(function() {
        $("#uwp_layout").change(function(){
            var layout = $(this).val();
            var container = $('#uwp_user_items_layout');
            container.removeClass();
            if (layout == 'list') {
                container.addClass('uwp-users-list-wrap uwp_listview');
            } else if (layout == '2col') {
                container.addClass('uwp-users-list-wrap uwp_gridview uwp_gridview_2col');
            } else if (layout == '3col') {
                container.addClass('uwp-users-list-wrap uwp_gridview uwp_gridview_3col');
            } else if (layout == '4col') {
                container.addClass('uwp-users-list-wrap uwp_gridview uwp_gridview_4col');
            } else if (layout == '5col') {
                container.addClass('uwp-users-list-wrap uwp_gridview uwp_gridview_5col');
            } else {
                container.addClass('uwp-users-list-wrap uwp_listview');
            }
        });

        jQuery( document ).ready(function($) {
            $( "#uwp_login_modal form.uwp-login-form" ).submit(function( e ) {
                e.preventDefault();
                uwp_ajax_login(this);
            });
        });

        function uwp_ajax_login($this) {

            $('#uwp_login_modal .uwp-login-ajax-notice').remove();

            var data = jQuery($this).serialize()+ "&action=uwp_ajax_login";

            jQuery.post(uwp_localize_data.ajaxurl, data, function(response) {
                response = jQuery.parseJSON(response);

                if(response.error){
                    $('#uwp_login_modal form.uwp-login-form').before(response.message);
                } else {
                    $('#uwp_login_modal form.uwp-login-form').before(response.message);
                    setTimeout(function(){location.reload()}, 1200)
                }

            });
        }

    });
}( jQuery, window ));

function uwp_profile_image_change(type){
    // remove it first
    jQuery('.uwp-profile-image-change-modal').remove();

    var $modal = '<div class="modal fade uwp-profile-image-change-modal bsui" tabindex="-1" role="dialog" aria-labelledby="uwp-profile-modal-title" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="uwp-profile-modal-title"></h5></div><div class="modal-body text-center"><i class="fas fa-circle-notch fa-spin fa-3x"></i></div></div></div></div>';
    jQuery('body').append($modal);

    jQuery('.uwp-profile-image-change-modal').modal({
        backdrop: 'static'
    });

    // do something with the file here
    var data = {
        'action': 'uwp_ajax_image_crop_popup_form',
        'type': type,
        'style': 'bootstrap'
    };

    jQuery.post(uwp_localize_data.ajaxurl, data, function(response) {
        jQuery('.uwp-profile-image-change-modal .modal-content').html(response);
    });
}

function uwp_init_auth_modal(){
    // open login form
    if(uwp_localize_data.login_modal) {
        jQuery(".users-wp-login-nav a, .uwp-login-link").click(function () {
            uwp_modal_login_form();
            return false;
        });
    }

    // open the register form
    if(uwp_localize_data.register_modal) {
        jQuery(".users-wp-register-nav a, .uwp-register-link").click(function () {
            uwp_modal_register_form();
            return false;
        });
    }

    // open the forgot password form
    if(uwp_localize_data.forgot_modal) {
        jQuery(".users-wp-forgot-nav a, .uwp-forgot-password-link").click(function () {
            uwp_modal_forgot_password_form();
            return false;
        });
    }
}

/**
 * Get the login form via ajax and load it in a modal.
 */
function uwp_modal_login_form(){
    var data = {
        'action': 'uwp_ajax_login_form' // deliberately no nonce for caching reasons
    };
    jQuery.ajax({
        type: "POST",
        url: uwp_localize_data.ajaxurl,
        data: data,
        beforeSend: function() {
            var $modal = '<div class="modal fade uwp-auth-modal bsui" tabindex="-1" role="dialog" aria-labelledby="uwp-profile-modal-title" aria-hidden="true"><div class="modal-dialog "><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="uwp-profile-modal-title"></h5></div><div class="modal-body text-center"><i class="fas fa-circle-notch fa-spin fa-3x"></i></div></div></div></div>';
            if(!jQuery('.uwp-auth-modal').length){
                jQuery('body').append($modal);
            }
            jQuery('.uwp-auth-modal').modal();
        },
        success: function(data) {
            if(data.success){
                jQuery('.uwp-auth-modal .modal-content').html(data.data);
                setTimeout(function(){jQuery('.uwp-auth-modal .modal-content input:visible:enabled:first').focus();}, 300); // set focus on the first input after load animation

                // process login form
                jQuery(".uwp-auth-modal .modal-content form.uwp-login-form").submit(function(e){
                    e.preventDefault(e);
                    uwp_modal_login_form_process();
                });
            }
            uwp_init_auth_modal();
        }
    });
}

/**
 * Submit the login form via ajax.
 */
function uwp_modal_login_form_process(){
    var data = jQuery(".modal-content form.uwp-login-form").serialize() + '&action=uwp_ajax_login';
    $button_text = jQuery('.uwp-auth-modal .modal-content .uwp_login_submit').html();
    jQuery.ajax({
        type: "POST",
        url: uwp_localize_data.ajaxurl,
        data: data,
        beforeSend: function() {
            jQuery('.uwp-auth-modal .modal-content .uwp_login_submit').html('<i class="fas fa-circle-notch fa-spin"></i> ' + $button_text).prop('disabled', true);// disable submit
            jQuery('.uwp-auth-modal .modal-content .modal-error').html(''); // clear error messages
        },
        success: function(data) {
            if(data.success){
                jQuery('.uwp-auth-modal .modal-content .uwp_login_submit').html($button_text).prop('disabled', true);// remove spinner
                jQuery('.uwp-auth-modal .modal-content .modal-error').html(data.data);
                // Show success message for 1 second before redirecting.
                setTimeout(function(){
                    location.reload();
                }, 1000);

            }else if(data.success===false){
                jQuery('.uwp-auth-modal .modal-content .modal-error').html(data.data);
                jQuery('.uwp-auth-modal .modal-content .uwp_login_submit').html($button_text).prop('disabled', false);// enable submit
            }
            uwp_init_auth_modal();
        }
    });
}


/**
 * Get the login form via ajax and load it in a modal.
 */
function uwp_modal_register_form(){
    var data = {
        'action': 'uwp_ajax_register_form' // deliberately no nonce for caching reasons
    };
    jQuery.ajax({
        type: "POST",
        url: uwp_localize_data.ajaxurl,
        data: data,
        beforeSend: function() {
            var $modal = '<div class="modal fade uwp-auth-modal bsui" tabindex="-1" role="dialog" aria-labelledby="uwp-profile-modal-title" aria-hidden="true"><div class="modal-dialog "><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="uwp-profile-modal-title"></h5></div><div class="modal-body text-center"><i class="fas fa-circle-notch fa-spin fa-3x"></i></div></div></div></div>';
            if(!jQuery('.uwp-auth-modal').length){
                jQuery('body').append($modal);
            }
            jQuery('.uwp-auth-modal').modal();
        },
        success: function(data) {
            if(data.success){
                jQuery('.uwp-auth-modal .modal-content').html(data.data);
                setTimeout(function(){jQuery('.uwp-auth-modal .modal-content input:visible:enabled:first').focus();}, 300); // set focus on the first input after load animation

                // process login form
                jQuery(".uwp-auth-modal .modal-content form.uwp-registration-form").submit(function(e){
                    e.preventDefault(e);
                    uwp_modal_register_form_process();
                });
            }
            uwp_init_auth_modal();
        }
    });
}

/**
 * Submit the login form via ajax.
 */
function uwp_modal_register_form_process(){
    var data = jQuery(".modal-content form.uwp-registration-form").serialize() + '&action=uwp_ajax_register';
    $button = jQuery('.uwp-auth-modal .modal-content .uwp_register_submit');
    $button_text = $button.html();
    jQuery.ajax({
        type: "POST",
        url: uwp_localize_data.ajaxurl,
        data: data,
        beforeSend: function() {
            $button.html('<i class="fas fa-circle-notch fa-spin"></i> ' + $button_text).prop('disabled', true);// disable submit
            jQuery('.uwp-auth-modal .modal-content .modal-error').html(''); // clear error messages
        },
        success: function(data) {
            if(data.success){
                $button.html($button_text).prop('disabled', true);// remove spinner
                jQuery('.uwp-auth-modal .modal-content .modal-error').html(data.data);
                // Show success message for 1 second before redirecting.
                // setTimeout(function(){
                //     location.reload();
                // }, 1000);

            }else if(data.success===false){
                jQuery('.uwp-auth-modal .modal-content .modal-error').html(data.data);
                $button.html($button_text).prop('disabled', false);// enable submit
            }
            uwp_init_auth_modal();
        }
    });
}


/**
 * Get the forgot password form via ajax and load it in a modal.
 */
function uwp_modal_forgot_password_form(){
    var data = {
        'action': 'uwp_ajax_forgot_password_form' // deliberately no nonce for caching reasons
    };
    jQuery.ajax({
        type: "POST",
        url: uwp_localize_data.ajaxurl,
        data: data,
        beforeSend: function() {
            var $modal = '<div class="modal fade uwp-auth-modal bsui" tabindex="-1" role="dialog" aria-labelledby="uwp-profile-modal-title" aria-hidden="true"><div class="modal-dialog "><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="uwp-profile-modal-title"></h5></div><div class="modal-body text-center"><i class="fas fa-circle-notch fa-spin fa-3x"></i></div></div></div></div>';
            if(!jQuery('.uwp-auth-modal').length){
                jQuery('body').append($modal);
            }
            jQuery('.uwp-auth-modal').modal();
        },
        success: function(data) {
            if(data.success){
                jQuery('.uwp-auth-modal .modal-content').html(data.data);
                setTimeout(function(){jQuery('.uwp-auth-modal .modal-content input:visible:enabled:first').focus();}, 300); // set focus on the first input after load animation

                // process login form
                jQuery(".uwp-auth-modal .modal-content form.uwp-forgot-form").submit(function(e){
                    e.preventDefault(e);
                    uwp_modal_forgot_password_form_process();
                });
            }
            uwp_init_auth_modal();
        }
    });
}

/**
 * Submit the forgot password form via ajax.
 */
function uwp_modal_forgot_password_form_process(){
    var data = jQuery(".modal-content form.uwp-forgot-form").serialize() + '&action=uwp_ajax_register';
    $button = jQuery('.uwp-auth-modal .modal-content .uwp_forgot_submit');
    $button_text = $button.html();
    jQuery.ajax({
        type: "POST",
        url: uwp_localize_data.ajaxurl,
        data: data,
        beforeSend: function() {
            $button.html('<i class="fas fa-circle-notch fa-spin"></i> ' + $button_text).prop('disabled', true);// disable submit
            jQuery('.uwp-auth-modal .modal-content .modal-error').html(''); // clear error messages
        },
        success: function(data) {
            if(data.success){
                $button.html($button_text).prop('disabled', true);// remove spinner
                jQuery('.uwp-auth-modal .modal-content .modal-error').html(data.data);
                // Show success message for 1 second before redirecting.
                // setTimeout(function(){
                //     location.reload();
                // }, 1000);

            }else if(data.success===false){
                jQuery('.uwp-auth-modal .modal-content .modal-error').html(data.data);
                $button.html($button_text).prop('disabled', false);// enable submit
            }
            uwp_init_auth_modal();
        }
    });
}