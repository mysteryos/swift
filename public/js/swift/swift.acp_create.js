/* 
 * Name: Accounts Payable: Create Form
 */
(window.acp_create = function() {
    $('#acprequest-create-form').validate({
        ignore: '',
        rules : {
            billable_company_code: {
                required: true
            },
            supplier_code: {
                required: true
            }
        },
        messages: {
            billable_company_code: {
                required: 'Please select a customer'
            },
            supplier_code: {
                required: 'Please select a supplier'
            }
        },

        // Ajax form submition
        submitHandler : function(form) {
                var savemsg = Messenger({extraClasses:'messenger-on-top messenger-fixed'}).post({
                                message: 'Saving Accounts Payable form',
                                type: 'info',
                                id: 'notif-top'
                              });
                $('#save-draft').attr('disabled','disabled').addClass('disable');
                $(form).ajaxSubmit({
                        dataType: 'json',
                        success : function(data) {
                            savemsg.update({
                                type: 'success',
                                message: 'Save Success!'
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
                        error: function (xhr, status, error) {
                            $('#save-draft').removeAttr('disabled').removeClass('disable');
                            savemsg.update({
                                type: 'error',
                                message: xhr.responseText,
                                hideAfter: 5
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
    $('#customercode,#suppliercode').val('');
    
    /*
     * Select2 instantiation
     */
    $('#customercode').select2({
        placeholder: 'Enter a billable company code/name',
        allowClear: true,
        minimumInputLength: 0,
        positionDropdownAbsolute: false,
        ajax: {
             url: "/ajaxsearch/acp-customercode",
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
                 var more = (page * 10) < data.total;
                 if(data.total > 0)
                 {
                     var found;
                     found = $.map(data.customers, function (item) {
                                return {
                                    id: item.AN8,
                                    name: item.ALPH,
                                    text: item.ALPH+" (Code:"+item.AN8+")",
                                    category: item.AC09
                                };
                      });
                     return {results: found, more:more};
                 }
                 else
                 {
                     return {results: ''};
                 }
             }
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

    $('#suppliercode').select2({
        placeholder: 'Enter a supplier code/name',
        allowClear: true,
        minimumInputLength: 0,
        positionDropdownAbsolute: false,
        ajax: {
             url: "/ajaxsearch/acp-searchsupplier",
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
                 var more = (page * 10) < data.total;
                 if(data.total > 0)
                 {
                     var found;
                     found = $.map(data.suppliers, function (item) {
                         return {
                             id: item.Supplier_Code,
                             name: $.trim(item.Supplier_Name),
                             text: $.trim(item.Supplier_Name)+" (Code: "+item.Supplier_Code+")"+" | "+item.Supplier_LongAddNo+" | "+item.Supplier_Add1
                         };
                      });
                     return {results: found, more:more};
                 }
                 else
                 {
                     return {results: ''};
                 }
             }
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
    
    messenger_hidenotiftop();
})();