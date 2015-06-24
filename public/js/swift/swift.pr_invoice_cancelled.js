(window.pr_invoice_cancelled = function(){
    
    var $btnPublish = $('#btn-publish');
    var $productContainer = $('#product-container');
    
    /*
     * Clean previous instances of select2
     */
    $('.select2-container').remove();    
    
    $('#invoice_cancelled_form').validate({
        ignore: '',
        rules : {
            invoice_code: {
                required: true
            }
        },
        messages: {
            invoice_code: {
                required: 'Please select an invoice'
            }
        },

        // Ajax form submition
        submitHandler : function(form) {
                var savemsg = Messenger({extraClasses:'messenger-on-top messenger-fixed'}).post({
                                message: 'Saving Invoice Cancelled Form',
                                type: 'info',
                                id: 'notif-top'
                              });
                $btnPublish.attr('disabled','disabled').addClass('disable');
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
                           $btnPublish.removeAttr('disabled').removeClass('disable');
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
    $('#invoicecode_autocomplete').val('');
    
    $('#invoicecode_autocomplete').select2({
        placeholder: 'Enter an invoice number',
        allowClear: true,
        minimumInputLength: 3,
        positionDropdownAbsolute: false,
        ajax: {
             url: "/ajaxsearch/pr-invoice-code",
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
                     found = $.map(data.invoices, function (item) {
                                return {
                                    id: item.DOC,
                                    name: item.DOC,
                                    text: item.DOC+" ("+item.DCTO+")"+ " - "+item.IVD+ " - "+item.ALPH+" ("+item.AN8+")",
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
        $productContainer.prepend("<div class='loading-overlay'></div>");
        $btnPublish.attr('disabled','disabled').addClass('disable');
        $.ajax({
           url:  '/product-returns/invoice-products/'+$(this).val(),
           type: 'GET',
           success:function(html)
           {
                $btnPublish.removeAttr('disabled').removeClass('disable');
                $productContainer.find('div.loading-overlay').remove();
                $productContainer.slideUp('300',function(){
                    $(this).html(html);
                    $(this).slideDown('300');
                });
               
           },
           error: function(xhr)
           {
               $btnPublish.removeAttr('disabled').removeClass('disable');
               messenger_notiftop(xhr.responseText,'error',10);
               $('#product-container').find('div.loading-overlay').remove().html('<p class="col-xs-12 text-center">Product Information will Appear here</p>');
           }
        });
    });
    
    $('#btn-reset').on('click',function(){
        $('#invoicecode_autocomplete').val('');
        $productContainer.html('<p class="col-xs-12 text-center">Product Information will Appear here</p>');
        return true;
    });
    
    //Hide Loading Message
    messenger_hidenotiftop();    
})();