/* 
 * Name: Admin Utility
 */
(window.admin_utility = function () {
    
    $('#mssql_sync_form').validate({
        rules : {
            sqlstatement: {
                    required: true
            }
        },
        messages: {
            sqlstatement: {
                required: 'Please enter your sql statment'
            }
        },

        // Ajax form submition
        submitHandler : function(form) {
                $(form).find('div.row-result').slideUp('300');
                var savemsg = Messenger({extraClasses:'messenger-on-top messenger-fixed'}).post({
                                message: 'Submitting form',
                                type: 'info',
                                id: 'notif-top'
                              });
                $(form).find('input.btn-submit').attr('disabled','disabled').addClass('disable');
                $(form).ajaxSubmit({
                        dataType: 'json',
                        success : function(data) {
                            savemsg.update({
                                type: 'success',
                                message: 'Success!',
                            });
                            $(form).find('textarea.col-result').val(data.result);
                            $(form).find('div.row-result').slideDown('300',function(){
                                savemsg.hide();
                                $(form).find('input.btn-submit').removeAttr('disabled').removeClass('disable');
                            });
                        },
                        error: function (data) {
                            $(form).find('input.btn-submit').removeAttr('disabled').removeClass('disable');
                            savemsg.update({
                                type: 'error',
                                message: 'Failed with msg:'+data.msg,
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