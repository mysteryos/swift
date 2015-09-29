/* 
 * Name: Product Returns: Create On Delivery Form
 */
(window.pr_create_ondelivery = function() {
    var newprod_count = 0;

    $('#invoice_id').select2({
        placeholder: 'Enter an exact invoice number',
        allowClear: true,
        minimumInputLength: 5,
        positionDropdownAbsolute: false,
        ajax: {
            url: "/ajaxsearch/pr-invoice-code-exact",
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
        $('#product-list').prepend("<div class='loading-overlay'></div>");
        $('#btn-addProducts').attr('disabled','disabled').addClass('disable');
        $.ajax({
            url:  '/product-returns/invoice-products-for-form/'+$(this).val(),
            type: 'GET',
            success:function(html)
            {
                $('#btn-addProducts').removeAttr('disabled').removeClass('disable');
                $('#product-list').find('div.loading-overlay').remove();
                $('#product-list').slideUp('300',function(){
                    $(this).html(html);
                    $('#product_tick_all').on('click',function(){
                        $('.product_checkbox').prop('checked',this.checked);
                    });
                    $(this).on('click','.pointable',function(){
                        var $checkbox = $(this).parent('tr').find('input:checkbox');
                        $checkbox.prop('checked', !$checkbox.prop('checked'));
                    });
                    $(this).slideDown('300');
                });

            },
            error: function(xhr)
            {
                $('#btn-addProducts').removeAttr('disabled').removeClass('disable');
                messenger_notiftop(xhr.responseText,'error',10);
                $('#product-list').find('div.loading-overlay').remove().html('<p class="text-center col-xs-12">Product Info will appear here</p>');
            }
        });
    });

    $('#qty_pickup_included').on('click',function(){
        if(this.checked)
        {
            $(this).parents('fieldset').find('div[data-qtyincluded="1"]').show();
        }
        else
        {
            $(this).parents('fieldset').find('div[data-qtyincluded="1"]').hide();
        }
    });

    $('#productFromInvoiceModal').on('shown.bs.modal', function(){
        document.getElementById('productFromInvoiceForm').reset();
        $('#product-list').html('<p class="text-center col-xs-12">Product Info will appear here</p>');
        $('#invoice_id').select2('data', null);
    });

    $('#btn-add-product-to-form').on('click',function(){
        if($('#invoice_id').select2('val') !== "")
        {

        }
        else
        {
            $('#productFromInvoiceModal').modal('hide');
        }
    });

    /*
     * Shortcut Key
     */
    
    $('#product-table').on('keydown','input,select','ctrl+return',function(){
        //new product
        var $productrow = add_new_product();
        $productrow.find('.product-id').select2('open');        
    });

    /*
     Form Validation
     */

    $.validator.addMethod('positiveNumber',
        function (value) {
            return Number(value) > 0;
        }, 'Enter a positive number.'
    );

    $.validator.addMethod('quantityNumber',
        function (value) {
            return Number(value) >= 0;
        }, 'Enter a valid number.'
    );

    var form_validator = $('#pr_create_form').validate({
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
        }
    });

    /*
    Add new product
     */
    function add_new_product()
    {
        var $dummy = $.contentdiv.find('.dummy.product-row');
        if($dummy.length)
        {
            var dummyproduct = $dummy.clone();
            dummyproduct.find('.form-control,.product-id').each(function(){
                var $this = $(this);
                $this.attr('name',$this.attr('name').replace('product[]','product['+(newprod_count+1)+']'));
                $this.removeAttr('disabled');
            });
            dummyproduct.removeClass('hide');
            dummyproduct.removeClass('dummy');
            
            dummyproduct.insertBefore($dummy);
            dummyproduct.find('.form-control,.product-id').each(function() {
                var $this = $(this);
                var $name = $this.attr('name').match(/product\[\d*]\[(.*)]$/);
                //Add validation by name
                if (typeof $name[1] !== "undefined") {
                    switch ($name[1]) {
                        case 'jde_itm':
                            $this.rules('add', {
                                required: true,
                                number: true
                            });
                            break;
                        case 'qty_client':
                            $this.rules('add', {
                                required: true,
                                number: true,
                                positiveNumber: true
                            });
                            break;
                        case 'qty_pickup':
                            $this.rules('add', {
                                required: true,
                                number: true,
                                quantityNumber: true
                            });
                            break;
                        case 'qty_triage_picking':
                            $this.rules('add', {
                                required: true,
                                number: true,
                                quantityNumber: true
                            });
                            break;
                        case 'qty_triage_disposal':
                            $this.rules('add', {
                                required: true,
                                number: true,
                                quantityNumber: true
                            });
                            break;
                        case 'reason_id':
                            $this.rules('add', {
                                required: true
                            });
                            break;
                        case 'pickup':
                            $this.rules('add', {
                                required: true
                            });
                            break;
                    }
                }
            });

            //Initialize product entry
            dummyproduct.find('.product-id').select2({
                placeholder: 'Enter a product code/name',
                allowClear: false,
                minimumInputLength: 3,
                id: function (item) {
                    return item.id;
                },
                ajax: {
                    url: '/ajaxsearch/product',
                    dataType: "json",
                    quietMillis: 500,
                    data: function (term, page) {
                        return {
                            term: term,
                            limit: 10,
                            page: page
                        };
                    },
                    results: function (data, page) {
                        var more = (page * 10) < data.total;
                        if(data.total > 0)
                        {
                            return {results: data.products, more:more};
                        }
                        else
                        {
                            return {results: ''};
                        }
                    }
                },
                formatSelection: function (item) {
                    return item.text;
                },
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
                $('.select2-input').focus();
            }).on('change',function(){
                $(this).valid();
                $(this).parents('.product-row').find('.product-qty-client').focus();
            });

            newprod_count += 1;
            return dummyproduct;
        }
        else
        {
            messenger_notiftop("An error occured. Please refresh the page and try again.","error")
        }        
    }

    //Reset page
    function resetForm() {
        $('#ccode').select2('val','');
        $.contentdiv.find(".product-row:not(.dummy)").remove();
        add_new_product();
        $('#btn-publish,#btn-save-draft,#btn-reset').removeAttr('disabled', 'disabled').removeClass('disable');
        form_validator.resetForm();
    }

    resetForm();
    
    /*
    Customer Select2
     */
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
    }).on('select2-close',function(){
        setTimeout(function() {
            $('.select2-container-active').removeClass('select2-container-active');
            $(':focus').blur();
        }, 1);        
    });

    $('#driver_id').select2({
        placeholder: 'Select a driver for this delivery'
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
    }).on('select2-close',function(){
        setTimeout(function() {
            $('.select2-container-active').removeClass('select2-container-active');
            $(':focus').blur();
        }, 1);
    }).rules('add', {
        required: true
    });


    $('#paper_number').rules('add',{
        required: true,
        number: true,
        positiveNumber: true
    });

    /*
     * Add product button
     */
    
    $.contentdiv.on('click','#btn-add-product',function(e){
        var $productrow = add_new_product();
        $productrow.find('.product-id').select2('open');
        return false; 
    });
    
    /*
     * Delete Product
     */
    
    $.contentdiv.on('click','.btn-delete-product',function(){
        $(this).parents('.product-row').slideUp('700',function(){
            $(this).remove();
            if($.contentdiv.find('.product-row:not(.dummy)').length === 0)
            {
                add_new_product();
            }
        });
        return false;
    });
    
    /*
     * Reset Form
     */
    $.contentdiv.on('click','#btn-reset',function(){
        resetForm();
        return false;
    });

    /*
     * Publish Form
     */

    $.contentdiv.on('click','#btn-publish',function()
    {
        var $stay = $('#check_new_after_save').is(':checked');
        if($('#pr_create_form').valid()){
            var savemsg = Messenger({extraClasses:'messenger-on-top messenger-fixed'}).post({
                message: 'Publishing product return form',
                type: 'info',
                id: 'notif-top'
            });
            $('#btn-publish,#btn-reset,#btn-save-draft').attr('disabled','disabled').addClass('disable');
            $('#pr_create_form').ajaxSubmit({
                data: {'publish': 1},
                dataType: 'json',
                success : function(data) {
                    if($stay)
                    {
                        savemsg.update({
                            type: 'success',
                            message: 'Publish Success! <a href="'+data.url+'">Click here to view the form</a>',
                            hideAfter: 5
                        });
                        resetForm();
                    }
                    else
                    {
                        savemsg.update({
                            type: 'success',
                            message: 'Save Success! Redirecting to the form',
                        });
                        $.pjax({
                            container: '#main',
                            url: data.url,
                            beforeReplace: function()
                            {
                                savemsg.hide();
                            }
                        });
                    }
                },
                error: function (data) {
                    $('#btn-publish,#btn-reset,#btn-save-draft').removeAttr('disabled').removeClass('disable');
                    savemsg.update({
                        type: 'error',
                        message: 'Publish Failed! Please retry.',
                        hideAfter: 5,
                    });
                }
            });
        }
        return false;
    });

    $.contentdiv.on('click','#btn-save-draft',function(){
        var $stay = $('#check_new_after_save').is(':checked');
        if($('#ccode').val() !== ""){
            var savemsg = Messenger({extraClasses:'messenger-on-top messenger-fixed'}).post({
                message: 'Saving product return draft form',
                type: 'info',
                id: 'notif-top'
            });
            $('#btn-publish,#btn-reset,#btn-save-draft').attr('disabled','disabled').addClass('disable');
            $('#pr_create_form').ajaxSubmit({
                data: {'publish': 0},
                dataType: 'json',
                success : function(data) {
                    if($stay)
                    {
                        savemsg.update({
                            type: 'success',
                            message: 'Save Success! <a href="'+data.url+'">Click here to view the form</a>',
                            hideAfter: 5
                        });
                        resetForm();
                    }
                    else
                    {
                        savemsg.update({
                            type: 'success',
                            message: 'Save Success! Redirecting to the form',
                        });
                        $.pjax({
                            container: '#main',
                            url: data.url,
                            beforeReplace: function()
                            {
                                savemsg.hide();
                            }
                        });
                    }
                },
                error: function (data) {
                    $('#btn-publish,#btn-reset,#btn-save-draft').removeAttr('disabled').removeClass('disable');
                    savemsg.update({
                        type: 'error',
                        message: 'Save Failed! Please retry.',
                        hideAfter: 5,
                    });
                }
            });
        }
        else
        {
            Messenger({extraClasses:'messenger-on-top messenger-fixed'}).post({
                message: 'Please select a customer',
                type: 'error',
                id: 'notif-top',
                hideAfter: 5
            });
        }
        return false;
    });

    messenger_hidenotiftop();
})();