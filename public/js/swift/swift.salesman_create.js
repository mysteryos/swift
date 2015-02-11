/* 
 * Name: Order Tracking: Create Form
 */
(window.salesman_create = function() {
    $('#salesman-create-form').validate({
        rules : {
            user_id: {
                required: true
            }
        },
        messages: {
            name: {
                required: 'Please select a user'
            }
        },

        // Ajax form submition
        submitHandler : function(form) {
                var savemsg = Messenger({extraClasses:'messenger-on-top messenger-fixed'}).post({
                                message: 'Saving user as salesman',
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
                        error: function (data) {
                            $('#save-draft').removeAttr('disabled').removeClass('disable');
                            savemsg.update({
                                type: 'error',
                                message: 'Save Failed! Please retry.',
                                hideAfter: 5,
                            });
                        }
                });

                return false;
        }
    });
    
    /*
     * Clean previous instances of select2
     */
    $('.select2-container').remove();
    $('#select_user_id').val('');
    
    $('#select_user_id').select2({
        placeholder: 'Select a user'
    }).on('select2-open',function(){
        $('#select2-drop-mask')
        .height($(window).height())
        .width($(window).width())
        .css({
            'opacity' : '.1',
            'position': 'fixed',
            'top': '0',
            'left': '0'
        });
    }).on('change',function(){
        $(this).valid();        
    });    

    messenger_hidenotiftop();
})();