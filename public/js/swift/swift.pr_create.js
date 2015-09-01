/* 
 * Name: Product Returns: Create Form
 */
(window.pr_create = function() {
    $('#pr_create_form').validate({
        ignore: '',
        rules : {
            customer_code: {
                required: true
            }
        },
        messages: {
            customer_code: {
                required: 'Please select a customer'
            }
        },

        // Ajax form submition
        submitHandler : function(form) {
            var savemsg = Messenger({extraClasses:'messenger-on-top messenger-fixed'}).post({
                            message: 'Saving product return draft form',
                            type: 'info',
                            id: 'notif-top'
                          });
            $('#btn-publish,#btn-reset').attr('disabled','disabled').addClass('disable');
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
                        $('#btn-publish,#btn-reset').removeAttr('disabled').removeClass('disable');
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
    $('#ccode').val('');
    
    $('#ccode').select2({
        placeholder: 'Enter a customer code/name',
        allowClear: true,
        minimumInputLength: 0,
        positionDropdownAbsolute: false,
        ajax: {
             url: "/ajaxsearch/customercode/product-returns",
             dataType: "json",
             quietMillis: 500,
             data: function (term, page) {
                 return {
                     term: term,
                     limit: 10,
                     page: page
                 };
             },
             results: function (data, page){
                 var more = (page * 10) < data.total
                 if(data.total > 0)
                 {
                     var found;
                     found = $.map(data.customers, function (item) {
                         return {
                             id: item.AN8,
                             name: item.ALPH,
                             text: item.ALPH+" (Code:"+item.AN8+")",
                             category: item.AC09
                         }
                      });
                     return {results: found, more:more};
                 }
                 else
                 {
                     return {results: ''};
                 }
             },
        }
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
    
    $('#btn-reset').on('click',function(){
        $('#ccode').val('');
        return true;
    });

    messenger_hidenotiftop();
})();