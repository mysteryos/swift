/* 
 * Name: Freight Company Create Form
 */
(window.fc_create = function(){
    var $orderTrackingCreateForm = $('#freight-company-create-form').validate({
        rules : {
            name: {
                    required: true
            },
            email: {
                email: true
            }
        },
        messages: {
            name: {
                required: 'Please enter a name to identify this freight company'
            }
        },

        // Ajax form submition
        submitHandler : function(form) {
                var savemsg = Messenger({extraClasses:'messenger-on-top messenger-fixed'}).post({
                                message: 'Saving freight company',
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
    //Hide Loading Message
    messenger_hidenotiftop(); 
})();