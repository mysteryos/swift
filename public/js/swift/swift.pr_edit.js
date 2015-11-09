/*
 * Name: PR Edit
 * Description: Product Returns form edit
 */

function addMulti($dummy,pk)
{
    $clone = $dummy.clone();
    $clone.removeClass('hide dummy');
    $clone.find('.editable').removeClass('dummy');
    $clone.find('.editable').each(function(){
        if(this.getAttribute('data-type')=="select2" && $(this).hasClass('product-editable'))
        {
            $(this).editable({
                disabled: $(this).hasClass('editable-disabled'),
                onblur: 'submit',
                placeholder: 'Select a product',
                select2: {
                    allowClear: false,
                    minimumInputLength: 3,
                    id: function (item) {
                        return item.id;
                    },
                    ajax: {
                        url: '/ajaxsearch/product',
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
                    initSelection: function (element, callback) {
                        callback({id: element.val() , text: element.parents('td.editable-select2').children('a.product-editable').html()});
                    }
                }
            }).on('shown', function(e, editable) {
                if (arguments.length == 2) {
                    setTimeout(function() {
                       editable.input.$input.select2("open");
                    }, 0);
                }
            });
        }
        else
        {
            $(this).editable({
                disabled: $(this).hasClass('editable-disabled'),
                onblur: 'submit'
            });
        }

        $(this).on('shown',function(e){
            presenceChannelCurrent.trigger('client-editable-shown',{user: presenceChannelCurrent.members.me ,name: $(this).attr('data-name'),pk: $(this).attr('data-pk'), id: this.id});
            if(this.getAttribute('data-type')=="select2")
            {
                /*
                 * Resize to fit within space
                 */
                $(this).parent('.editable-select2').find('.select2-container').width($(this).parents('.editable-select2').width()*0.7);
            }
            return true;
        }).on('hidden',function(e,reason){
            presenceChannelCurrent.trigger('client-editable-hidden',{user: presenceChannelCurrent.members.me, name: $(this).attr('data-name'),pk: $(this).attr('data-pk'), id: this.id})
        }).on('save',function(e,params){
            //First time save, set primary key
            var $this = $(this);
            if(this.getAttribute('data-pk') == "0")
            {
                if($(this).hasClass('product-editable'))
                {
                    $('#product-form').find('div.loading-overlay').remove();
                }
                else
                {
                    $this.parents('.multi').find('div.loading-overlay').remove();
                }
                var response = $.parseJSON(params.response);
                //Set new pk value
                addEditablePk($this.parents('.multi'),response.encrypted_id,response.id);
                //Trigger Channel Event
                presenceChannelCurrent.trigger('client-multi-add',{user: presenceChannelCurrent.members.me , pk: response, context: $dummy.attr('data-name')});
            }
            //Trigger Single Value Save as well
            presenceChannelCurrent.trigger('client-editable-save',{user: presenceChannelCurrent.members.me, name: $this.attr('data-name'),pk: $this.attr('data-pk'), newValue: params.newValue, id: this.id})
        }).on('submit',function(e){
            if(this.getAttribute('data-pk') == "0")
            {
                if($(this).hasClass('product-editable'))
                {
                    $('#product-form').prepend("<div class='loading-overlay'></div>");
                }
                else
                {
                    $(this).parents('.multi').prepend("<div class='loading-overlay'></div>");
                }
            }
        }).on('error',function(e){
            if(this.getAttribute('data-pk') == "0")
            {
                if($(this).hasClass('product-editable'))
                {
                    $('#product-form').find('div.loading-overlay').remove();
                }
                else
                {
                    $this.parents('.multi').find('div.loading-overlay').remove();
                }
            }
        });
    });

    if(typeof pk !== "undefined")
    {
        addEditablePk($clone,pk.encrypted_id,pk.id);
    }

    if($clone.is('tr'))
    {
        $dummy.parents('.jarviswidget').find('table').append($clone);
    }
    else
    {
        $dummy.parents('.jarviswidget').find('form').prepend($clone);
    }
    return true;
}

function addEditablePk($fieldset,$encryptedPk,$pk)
{
    $fieldset.find('a.editable').editable('option', 'pk', $encryptedPk);
    $fieldset.find('a.editable').attr('data-pk',$encryptedPk);
    $fieldset.find('a.editable').each(function(){
        $this=$(this);
        $this.attr('id',$this.attr('data-context')+"_"+$this.attr('data-name')+"_"+$pk);
    });
    return true;
}

function editableElement($element)
{
    $element.each(function(){
        var $this = $(this);
        if(this.getAttribute('data-type')=="select2" && $this.hasClass('product-editable'))
        {
            $this.editable({
                disabled: $this.hasClass('editable-disabled'),
                placeholder: 'Select a product',
                onblur: 'submit',
                select2: {
                    allowClear: false,
                    minimumInputLength: 3,
                    id: function (item) {
                        return item.id;
                    },
                    ajax: {
                        url: '/ajaxsearch/product',
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
                    initSelection: function (element, callback) {
                        callback({id: element.val() , text: element.parents('td.editable-select2').children('a.product-editable').html()});
                    }
                }
            }).on('shown', function(e, editable) {
                if (arguments.length == 2) {
                    setTimeout(function() {
                       editable.input.$input.select2("open");
                    }, 0);
                }
            });
        }
        else if(this.getAttribute('data-type')=="select2" && this.getAttribute('data-name')=="customer_code" && this.getAttribute('data-context')=="generalinfo")
        {
            $this.editable({
                disabled: $this.hasClass('editable-disabled'),
                placeholder: "Select a customer",
                onblur: 'submit',
                select2: {
                    allowClear: false,
                    minimumInputLength: 3,
                    id: function (item) {
                        return item.id;
                    },
                    ajax: {
                        url: "/ajaxsearch/customercode",
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
                                        text: item.ALPH+" (Code: "+item.AN8+")",
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
                    },
                    formatSelection: function (item) {
                        return item.text;
                    },
                    initSelection: function (element, callback) {
                        callback({id: element.val() , text: element.parents('div.editable-select2').children('a.editable').html()});
                    }
                }
            }).on('shown', function(e, editable) {
                if (arguments.length == 2) {
                    setTimeout(function() {
                       editable.input.$input.select2("open");
                    }, 0);
                }
            });
        }
        else
        {
            $this.editable({
               disabled: $this.hasClass('editable-disabled'),
               onblur: 'submit'
            });

        }

        $this.on('shown',function(e){
            presenceChannelCurrent.trigger('client-editable-shown',{user: presenceChannelCurrent.members.me ,name: $(this).attr('data-name'),pk: $(this).attr('data-pk'), id: this.id});
            if(this.getAttribute('data-type')=="select2")
            {
                /*
                 * Resize to fit within space
                 */
                $(this).parent('.editable-select2').find('.select2-container').width($(this).parents('.editable-select2').width()*0.7);
            }
            return true;
        }).on('hidden',function(e,reason){
            presenceChannelCurrent.trigger('client-editable-hidden',{user: presenceChannelCurrent.members.me, name: $(this).attr('data-name'),pk: $(this).attr('data-pk'), id: this.id});
            return true;
        });
        if(this.getAttribute('data-name') === "approval_approved")
        {
            /*
             * Approvals only
             */
            $this.on('save',function(e,params){
                if(this.getAttribute('data-pk') == "0")
                {
                    var response = $.parseJSON(params.response);
                    //Set new pk value
                    $(this).editable('option', 'pk', response.encrypted_id);
                    $(this).attr('data-pk',response.encrypted_id);

                    //Set comment pk as well
                    $(this).closest('div.row').find('a.editable[data-name="approval_comment"]').editable('option', 'pk', response.encrypted_id);
                    $(this).closest('div.row').find('a.editable[data-name="approval_comment"]').attr('data-pk',response.encrypted_id);

                    //Trigger Single Value Save as well
                    presenceChannelCurrent.trigger('client-editable-save',{user: presenceChannelCurrent.members.me, name: $(this).attr('data-name'),pk: $(this).attr('data-pk'), newValue: params.newValue, id: this.id})
                }
                return true;
            });
        }
        else
        {
            //Normal Editables
            $this.on('save',function(e,params){
                if($(this).editable('option','pk') !== "0")
                {
                    //Bug fix for disappearing pks - Weird
                    $(this).editable('option','pk',$(this).attr('data-pk'));
                    presenceChannelCurrent.trigger('client-editable-save',{user: presenceChannelCurrent.members.me, name: $(this).attr('data-name'),pk: $(this).attr('data-pk'), newValue: params.newValue, id: this.id});
                }
                return true;
            });
        }
    });
}

(window.pr_edit = function () {

    $(".product-filter a").on('click',function() {
            var selText = $(this).text();
            var $this = $(this);
            $this.parents('.btn-group').find('.dropdown-toggle').html(selText + ' <span class="caret"></span>');
            var filter = $this.attr("data-approvalstatus");
            $this.parents('.dropdown-menu').find('li').removeClass('active');

            if(typeof filter !== "undefined")
            {
                $('tr.fieldset-product').hide();
                $('tr.fieldset-product[data-approvalstatus="'+filter+'"]').show();
            }
            else
            {
                $('tr.fieldset-product').show();
            }
    });

    //Ribbon Buttons
    $('a.btn-ribbon-cancel').on('click',function(e){
        e.preventDefault();
        var $this = $(this);
        $.SmartMessageBox({
                title : "<i class='fa fa-times txt-color-red'></i> <span class='txt-color-red'><strong>Are you sure you wish to cancel this Product Returns?</strong></span> ?",
                content : "The form will be locked from editing after cancellation",
                buttons : '[No][Yes]'

        }, function(ButtonPressed) {
                if (ButtonPressed == "Yes") {
                    Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
                        id: 'notif-top',
                        errorMessage: 'Error cancelling Product Returns',
                        successMessage: 'Product Returns has been cancelled',
                        progressMessage: 'Please Wait...',
                        action: $.ajax,
                    },
                    {
                        type:'POST',
                        url: $this.attr('href'),
                        success:function()
                        {
                            window.setTimeout(function(){
                                $('a.btn-ribbon-refresh:first').click();
                            },'2000');
                        },
                        error:function(xhr, status, error)
                        {
                            return xhr.responseText;
                        }
                    });
                }

        });
        return false;
    });

    //Help button
        $('a.btn-help').on('click',function(e){
        e.preventDefault();
        var $this = $(this);
        $this.attr('disabled','disabled');
        $this.addClass('loading-animation');
        $.ajax({
            url: $this.attr('data-href'),
            type: 'GET',
            success:function(text)
            {
                $.smallBox({
			title : "Help information",
			content : text,
			color : "#5384AF",
			icon : "fa fa-question"
		});
                $this.removeAttr('disabled');
                $this.removeClass('loading-animation');
            },
            error:function(xhr, status, error)
            {
                $.smallBox({
			title : "Help information",
			content : xhr.responseText,
			color : "#5384AF",
			icon : "fa fa-question"
		});
                $this.removeAttr('disabled');
                $this.removeClass('loading-animation');
            }
        });
    });

    $('a.btn-force-update').on('click',function(e){
        e.preventDefault();
        var $this = $(this);
        $this.attr('disabled','disabled');
        $this.addClass('loading-animation');
        $.ajax({
            url: $this.attr('data-href'),
            type: 'GET',
            success:function(text)
            {
                $.smallBox({
			title : "Workflow information",
			content : text,
			color : "#26A65B",
			icon : "fa fa-check"
		});
                $this.removeAttr('disabled');
                $this.removeClass('loading-animation');
            },
            error:function(xhr, status, error)
            {
                $this.removeAttr('disabled');
                $this.removeClass('loading-animation');
                return xhr.responseText;
            }
        });
    });

    //Mark as important button

    $('a.btn-mark-important').on('click',function(e){
        e.preventDefault();
        var $this = $(this);
        var $important = $this.children('i').hasClass('fa-exclamation-triangle');
        $.SmartMessageBox({
                title : "<i class='fa fa-times txt-color-red'></i> <span class='txt-color-red'><strong>Are you sure you wish to "+($important? "unmark" : "mark")+" this product returns as important?</strong></span> ?",
                content : "All product returns users will receive a notice",
                buttons : '[No][Yes]'

        }, function(ButtonPressed) {
                if (ButtonPressed == "Yes") {
                    $this.attr('disabled');
                    Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
                        id: 'notif-top',
                        errorMessage: 'Error marking product returns',
                        successMessage: 'Product returns has been '+($important? "unmarked" : "marked")+' as important',
                        progressMessage: 'Please Wait...',
                        action: $.ajax,
                    },
                    {
                        type:'PUT',
                        url: $this.attr('href'),
                        success:function()
                        {
                            if($important)
                            {
                                $this.attr('data-original-title',"Mark as important");
                                $this.children('i').removeClass('fa-exclamation-triangle');
                                $this.children('i').addClass('fa-exclamation');
                                $this.tooltip();
                            }
                            else
                            {
                                $this.attr('data-original-title',"Unmark as important");
                                $this.children('i').addClass('fa-exclamation-triangle');
                                $this.children('i').removeClass('fa-exclamation');
                                $this.tooltip();
                            }
                            $this.removeAttr('disabled');
                        },
                        error:function(xhr, status, error)
                        {
                            $this.removeAttr('disabled');
                            return xhr.responseText;
                        }
                    });
                }

        });
        return false;
    });

    //Bind pusher channel & events
    pusherSubscribeCurrentPresenceChannel(true,true);

    //Turn on inline Mode
    $.fn.editable.defaults.mode = 'inline';
    $.fn.editable.defaults.ajaxOptions = {type: "put"};

    //Enable X-Editable
    editableElement($('.editable:not(.dummy)'));

    //Multi
    $('.product-editable, .pickup-editable, .erporder-editable, .creditnote-editable').on('save',function(e,params){
        var $this = $(this);
        //First time save, set primary key
        if(this.getAttribute('data-pk') == "0")
        {
            //Remove Overlay
            if($(this).hasClass('product-editable'))
            {
                $('#product-form').find('div.loading-overlay').remove();
            }
            else
            {
                $this.parents('.multi').find('div.loading-overlay').remove();
            }
            var response = $.parseJSON(params.response);
            //Set new pk value
            addEditablePk($(this).parents('.multi'),response.encrypted_id,response.id);
            //Trigger Channel Event
            presenceChannelCurrent.trigger('client-multi-add',{user: presenceChannelCurrent.members.me , pk: response, context: $this.parents('.multi').attr('data-name')});
            //Trigger Single Value Save as well
            presenceChannelCurrent.trigger('client-editable-save',{user: presenceChannelCurrent.members.me, name: $this.attr('data-name'),pk: $this.attr('data-pk'), newValue: params.newValue, id: this.id})
        }
        return true;
    }).on('submit',function(){
        if(this.getAttribute('data-pk') == "0")
        {
            if($(this).hasClass('product-editable'))
            {
                $('#product-form').prepend("<div class='loading-overlay'></div>");
            }
            else
            {
                $(this).parents('.multi').prepend("<div class='loading-overlay'></div>");
            }
        }
    }).on('error',function(){
        if(this.getAttribute('data-pk') == "0")
        {
            if($(this).hasClass('product-editable'))
            {
                $('#product-form').find('div.loading-overlay').remove();
            }
            else
            {
                $(this).parents('.multi').find('div.loading-overlay').remove();
            }
        }
    });

    /*
     * Add New
     */
    $('.btn-add-new').on('click',function(){
        $this = $(this);
        $dummy = $this.parents('.jarviswidget').find('.multi.dummy');
        if($dummy.length)
        {
            var $editableContainer = $.maindiv.find('.editable-container');
            if($editableContainer.length)
            {
                if($editableContainer.prev().hasClass('editable'))
                {
                    $editableContainer.prev().editable('hide');
                }
            }
            addMulti($dummy);
        }
        else
        {
            messenger_notiftop('Error: Cannot add new record','error');
        }
    });

    $('.jarviswidget').on('click','.multi .btn-delete',function(){
        var $this = $(this);
        if(confirm('Are you sure you wish to delete this record?'))
        {
            var $thisname = $this.parents('.multi').attr('data-name').ucfirst();
            var $thiseditable = $this.parents('.multi').find('a.editable:first');
            if($thiseditable.attr('data-pk')=="0")
            {
                $this.parents('.multi').slideUp('500',function(){
                   $(this).remove();
                });
                messenger_notiftop($thisname+' entry has been deleted','success');
            }
            else
            {
                Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
                    id: 'notif-top',
                    errorMessage: 'Error removing '+$thisname+' entry',
                    successMessage: $thisname+' entry has been deleted',
                    progressMessage: 'Please Wait...',
                    action: $.ajax,
                },
                {
                    type:'DELETE',
                    url: $this.attr('href'),
                    data: {pk:$thiseditable.attr('data-pk')},
                    success:function()
                    {
                        presenceChannelCurrent.trigger('client-multi-delete',{user: presenceChannelCurrent.members.me , id: $thiseditable.attr('id'), context: $this.parents('.multi').attr('data-name')});
                        $this.parents('.multi').slideUp('500',function(){
                           $(this).remove();
                        });
                    },
                    error:function(xhr, status, error)
                    {
                        return xhr.responseText;
                    }
                });
            }
        }
        return false;
    });

    /*
     * Add From Invoice
     */

    var $saveProductByInvoiceForm = $('#productFromInvoiceForm').validate({
        ignore: '',
        rules : {
            invoice_id: {
                required: true
            },
            'jde_itm[]': {
                required: true
            }
        },
        messages: {
            invoice_id: {
                required: 'Please select an invoice'
            },
            'jde_itm[]': {
                required: 'Please select at least one product'
            }
        },

        // Ajax form submition
        submitHandler : function(form) {
                var savemsg = Messenger({extraClasses:'messenger-on-top messenger-fixed'}).post({
                                message: 'Adding Products From Invoice',
                                type: 'info',
                                id: 'notif-top',
                                hideAfter: 0
                              });
                $('#btn-addProducts').attr('disabled','disabled').addClass('disable');
                $(form).ajaxSubmit({
                    dataType: 'json',
                    success : function(data) {
                        console.log($('#product-form'));
                        $('<div/>',{
                            'class':'loading-overlay'
                        }).appendTo('#product-form');
                        $('#product-form').load(document.getElementById('product-form').getAttribute('data-load'),null,function(){
                            editableElement($('#product-form .editable:not(.dummy)'));
                            $(this).find('div.loading-overlay').remove();
                        });
                        savemsg.update({
                            type: 'success',
                            message: data.msg,
                            hideAfter: 5
                        });
                        $('#productFromInvoiceModal').modal('hide');
                    },
                    error: function (xhr, status, error) {
                        $('#btn-addProducts').removeAttr('disabled').removeClass('disable');
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

    $('#invoice_id').select2({
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
        $saveProductByInvoiceForm.resetForm();
        $('#product-list').html('<p class="text-center col-xs-12">Product Info will appear here</p>');
        $('#invoice_id').select2('data', null);
    });

    /*
     * Dropzone
     */

    // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
    var previewNode = document.querySelector("#template");
    var previewTemplate = previewNode.parentNode.innerHTML;
    previewNode.style.display = "none";
    var removeFileEvent;
    var theAwesomeDropZoneAP= new Dropzone(document.getElementById('content'),{
        url:'/product-returns/upload/'+$('#id').val(),
        clickable: '#btn-upload',
        previewsContainer: "#upload-preview",
        previewTemplate: previewTemplate,
        autoQueue: true,
        createImageThumbnails : false,
        addedfile: function(file) {
            var node, removeLink, _i, _j, _k, _len, _len1, _len2, _ref, _ref1, _ref2, _results;
            if (this.element === this.previewsContainer) {
              this.element.classList.add("dz-started");
            }
            if (this.previewsContainer) {
              file.previewElement = Dropzone.createElement(this.options.previewTemplate.trim());
              file.previewTemplate = file.previewElement;
              this.previewsContainer.appendChild(file.previewElement);
              _ref = file.previewElement.querySelectorAll("[data-dz-name]");
              for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                node = _ref[_i];
                node.textContent = file.name;
              }
              _ref1 = file.previewElement.querySelectorAll("[data-dz-size]");
              for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
                node = _ref1[_j];
                node.innerHTML = this.filesize(file.size);
              }
              if (this.options.addRemoveLinks) {
                file._removeLink = Dropzone.createElement("<a class=\"dz-remove\" href=\"javascript:undefined;\" data-dz-remove>" + this.options.dictRemoveFile + "</a>");
                file.previewElement.appendChild(file._removeLink);
              }
              removeFileEvent = (function(_this) {
                return function(e) {
                  e.preventDefault();
                  e.stopPropagation();
                  if (file.status === Dropzone.UPLOADING) {
                    return Dropzone.confirm(_this.options.dictCancelUploadConfirmation+file.name+" ?", function() {
                      return _this.removeFile(file);
                    });
                  } else {
                    if (_this.options.dictRemoveFileConfirmation) {
                      return Dropzone.confirm(_this.options.dictRemoveFileConfirmation+file.name+" ?", function() {
                            var deletemsg = Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
                                type: 'info',
                                message: 'Deleting file '+file.name
                            });
                            if($(file.previewElement).hasClass('dz-error'))
                            {
                                _this.removeFile(file);
                            }
                            else
                            {
                                $.ajax({
                                    type:'DELETE',
                                    url: '/product-returns/upload/'+$(file.previewElement).attr('data-id'),
                                    success:function()
                                    {
                                        deletemsg.update({
                                            type: 'success',
                                            message: file.name+' has been deleted',
                                            hideAfter: 3,
                                            showCloseButton: true
                                        });
                                        _this.removeFile(file);
                                    },
                                    error:function(xhr, status, error)
                                    {
                                        deletemsg.update({
                                            type: 'error',
                                            message: xhr.responseText,
                                            showCloseButton: true
                                        });
                                    }
                                });
                            }

                            return true;
                      });
                    } else {
                      return _this.removeFile(file);
                    }
                  }
                };
              })(this);
              _ref2 = file.previewElement.querySelectorAll("[data-dz-remove]");
              _results = [];
              for (_k = 0, _len2 = _ref2.length; _k < _len2; _k++) {
                removeLink = _ref2[_k];
                _results.push(removeLink.addEventListener("click", removeFileEvent));
              }
              return _results;
            }
        },
        init: function() {
            $('#upload-preview .btn.delete').on('click',function(){
                var $thisParent = $(this).closest('.row.dz-success');
                if(confirm('Are you sure you want to delete this file: '+$thisParent.attr('data-name')+' ?'))
                {
                    var deletemsg = Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
                        type: 'info',
                        message: 'Deleting file '+$thisParent.attr('data-name')
                    });

                    $.ajax({
                        type:'DELETE',
                        url: '/product-returns/upload/'+$thisParent.attr('data-id'),
                        success:function()
                        {
                            deletemsg.update({
                                type: 'success',
                                message: $thisParent.attr('data-name')+' has been deleted',
                                hideAfter: 3,
                                showCloseButton: true
                            });
                            $thisParent.remove();
                        },
                        error:function(xhr, status, error)
                        {
                            deletemsg.update({
                                type: 'error',
                                message: xhr.responseText,
                                showCloseButton: true
                            });
                        }
                    });
                }
                return false;
            });

            //Init Tags

        }
    });

    theAwesomeDropZoneAP.on("sending", function(file,xhr,formdata) {
            uploadmsg = Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
            message: 'Uploading "'+file.name+'" <div class="progress progress-sm progress-striped active"><div aria-valuetransitiongoal="25" class="progress-bar bg-color-blue" id="upload-progress" style="width: 0%;" aria-valuenow="25"></div> </div>',
            hideAfter: 0,
            showCloseButton: false,
            type: 'info',
            id: 'uploadmsg'
        });

        switch(file.type)
        {
            case "image/jpeg":
            case "image/png":
            case "image/bmp":
            case "image/jpg":
                var icon = '<i class="fa fa-file-image-o"></i>';
                break;
            case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
            case "application/vnd.ms-excel":
                var icon = '<i class="fa fa-file-excel-o"></i>';
                break;
            case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
            case "application/msword":
                var icon = '<i class="fa fa-file-word-o"></i>';
                break;
            case "application/pdf":
                var icon = '<i class="fa fa-file-pdf-o"></i>';
                break;
            default:
                var icon = '<i class="fa fa-file-o"></i>';
                break;
        }

        $(file.previewElement).find('span.name').prepend(icon);
        $(file.previewElement).find('div.progress').removeClass('hide');
        return file;
    });

    theAwesomeDropZoneAP.on("success", function (file,response){
        Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
            showCloseButton: true,
            type: 'success',
            message: "Upload Complete: "+file.name,
            hideAfter: 3
        });
        var res = $.parseJSON(response);
        if(res.success)
        {
            //Add File preview anchor
            $(file.previewElement).find('span.name').wrapInner("<a class='file-view' rel='tooltip' data-original-title='Last update: "+res.updated_on+" &#013; Updated By: "+res.updated_by+"' data-placement='bottom' href='"+res.url+"'/>");
            //Set Doc Id
            $(file.previewElement).attr('data-id',res.id);
            $(file.previewElement).find("[rel=tooltip]").tooltip();
            //Set Tag Editable
            var $editable = $(file.previewElement).find('a.editable');
            $editable.html('');
            $editable.attr('data-pk',res.id);
            $editable.removeAttr('data-value');
            $editable.editable();
            $editable.removeClass('dummy hide');

        }
        //Hide Progress Bar
        $(file.previewElement).find('div.progress').addClass('hide');
        return file;
    });

    theAwesomeDropZoneAP.on("cancelled",function(){
       if(typeof uploadmsg !== null)
       {
           uploadmsg.hide();
       }
        Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
            showCloseButton: true,
            type: 'info',
            hideAfter: 3,
            message: 'Upload Cancelled: "'+file.name+'"'
        });
    });

    theAwesomeDropZoneAP.on('error',function(file,errorMsg,xhr){
        Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
            showCloseButton: true,
            type: 'error',
            hideAfter: 0,
            message: 'Upload Failed: "'+file.name+'" - '+errorMsg
        });
    });

    theAwesomeDropZoneAP.on('uploadprogress',function(file,progress){
        var $uploadprogress = $('#upload-progress');
        if($uploadprogress[0].length != 0)
        {
            $uploadprogress.animate({width:progress+'%'},500);
        }
    });

    theAwesomeDropZoneAP.on('totaluploadprogress',function(progress){
       if(typeof uploadmsg !== "undefined" && progress == 100)
       {
           uploadmsg.hide();
       }
    });

    //Drag & Drop File Fix

    var dragEle = document.getElementById( "content" );
    new Dragster(dragEle);
    dragEle.addEventListener( "dragster:enter", function (e) {
        for (n in e.detail.dataTransfer.types)
        {
            if (e.detail.dataTransfer.types[n] === "Files"){
                e.target.classList.add( "dragged-over" );
                break;
            }
        }

    }, false );

    dragEle.addEventListener( "dragster:leave", function (e) {
        e.target.classList.remove( "dragged-over" );
    }, false );

    /*
     * Google Doc Viewer
     */
    $('a.file-view').on('click',function(e){
        e.preventDefault();
        var $this = $(this);
        //For images
        if($this.attr('href').indexOf('.jpg') !== -1 || $this.attr('href').indexOf('.jpeg') !== -1 || $this.attr('href').indexOf('.png') !== -1 || $this.attr('href').indexOf('.bmp') !== -1)
        {
            $.colorbox({
                href: $this.attr('href'),
                maxHeight:"100%",
                maxWidth:"90%",
                innerWidth:"100%",
                innerHeight:"100%",
                initialWidth:"64px",
                initialHeight:"84px",
                closeButton:true,
                iframe: false,
            });
        }
        else
        {
            //For Docs
            $.colorbox({
                href: "http://docs.google.com/viewer?url="+encodeURIComponent($this.attr('href'))+"&embedded=true",
                maxHeight:"100%",
                maxWidth:"90%",
                innerWidth:"100%",
                innerHeight:"100%",
                initialWidth:"64px",
                initialHeight:"84px",
                closeButton:true,
                iframe: true,
            });
        }
    });

    $.document_.bind('cbox_complete', function () {
        $('html').css({ overflow: 'hidden' });
    }).bind('cbox_closed', function () {
        $('html').css({ overflow: 'auto' });
    });

    //Publish button
    $('a.btn-publish').on('click',function(e){
        e.preventDefault();
        var $this = $(this);
        $.SmartMessageBox({
                title : "<span class='txt-color-greenDark'><i class='fa fa-share'></i> Publish Form?</span>",
                content : "A notification will be sent to the responsible parties",
                buttons : '[No][Yes]'

        }, function(ButtonPressed) {
            if (ButtonPressed == "Yes") {
                Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
                    id: 'notif-top',
                    errorMessage: 'Error: form not published',
                    successMessage: 'Form has been published',
                    progressMessage: 'Please Wait...',
                    action: $.ajax,
                },
                {
                    type:'POST',
                    url: $this.attr('href'),
                    success:function()
                    {
                        window.setTimeout(function(){
                            $('a.btn-ribbon-refresh:first').click();
                        },'2000');
                    },
                    error:function(xhr, status, error)
                    {
                        return xhr.responseText;
                    }
                });
            }
            else
            {
                return false;
            }

        });
        return false;
    });

    //Enable Commenting
    enableComments();

    //Hide Loading Message
    messenger_hidenotiftop();
})();