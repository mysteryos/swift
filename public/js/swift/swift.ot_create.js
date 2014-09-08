/* 
 * Name: Order Tracking: Create Form
 */
(window.ot_create = function() {
    $('#order-tracking-create-form').validate({
        rules : {
            name: {
                    required: true
            }
        },
        messages: {
            name: {
                required: 'Please enter a name to identify this order'
            }
        },

        // Ajax form submition
        submitHandler : function(form) {
                var savemsg = Messenger({extraClasses:'messenger-on-top messenger-fixed'}).post({
                                message: 'Saving order tracking form',
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

    // START AND FINISH DATE
    $('#startdate').datepicker({
            dateFormat : 'dd.mm.yy',
            prevText : '<i class="fa fa-chevron-left"></i>',
            nextText : '<i class="fa fa-chevron-right"></i>',
            onSelect : function(selectedDate) {
                    $('#finishdate').datepicker('option', 'minDate', selectedDate);
            }
    });

    $('#finishdate').datepicker({
            dateFormat : 'dd.mm.yy',
            prevText : '<i class="fa fa-chevron-left"></i>',
            nextText : '<i class="fa fa-chevron-right"></i>',
            onSelect : function(selectedDate) {
                    $('#startdate').datepicker('option', 'maxDate', selectedDate);
            }
    });

    messenger_hidenotiftop();
})();