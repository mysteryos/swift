/* 
 * Name: Order Tracking: Create Form
 */
(window.salescommission_createscheme = function() {
    
    $('#select_type').select2({
        placeholder: 'Please select a type'
    })
    
    $('#create-form').validate({
        rules : {
            name: {
                required: true
            },
            type: {
                required: true
            }
        },
        messages: {
            name: {
                required: 'Please enter a name'
            },
            type: {
                required: 'Please select a type of scheme'
            }
        },

        // Ajax form submition
        submitHandler : function(form) {
                var savemsg = Messenger({extraClasses:'messenger-on-top messenger-fixed'}).post({
                                message: 'Saving product scheme',
                                type: 'info',
                                id: 'notif-top'
                              });
                $('#save-draft').attr('disabled','disabled').addClass('disable');
                $(form).ajaxSubmit({
                        dataType: 'json',
                        success : function(data) {
                            savemsg.update({
                                type: 'success',
                                message: 'Save Success!',
                            });
                            $.pjax({
                                container: '#main',
                                url: data.url,
                                beforeReplace: function()
                                {
                                    savemsg.hide();
                                }
                            });
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            $('#save-draft').removeAttr('disabled').removeClass('disable');
                            savemsg.update({
                                type: 'error',
                                message: xhr.responseText,
                                hideAfter: 5,
                            });
                        }
                });

                return false;
        }
    });

    messenger_hidenotiftop();
})();