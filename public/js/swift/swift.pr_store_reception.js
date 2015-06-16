/* 
 * Name: Product Returns - Store Reception
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
            if(this.getAttribute('data-type')=="select2")
            {
                /*
                 * Resize to fit within space
                 */
                $(this).parent('.editable-select2').find('.select2-container').width($(this).parents('.editable-select2').width()*0.7);
            }
            return true;
        }).on('save',function(e,params){
            //First time save, set primary key
            var $this = $(this);
            if(this.getAttribute('data-pk') == "0")
            {
                $this.parents('.multi').find('div.loading-overlay').remove();
                var response = $.parseJSON(params.response);
                //Set new pk value
                addEditablePk($this.parents('.multi'),response.encrypted_id,response.id);
                //Trigger Channel Event
            }
            //Trigger Single Value Save as well
        }).on('submit',function(e){
            if(this.getAttribute('data-pk') == "0")
            {
                $(this).parents('.multi').prepend("<div class='loading-overlay'></div>");
            }            
        }).on('error',function(e){
            if(this.getAttribute('data-pk') == "0")
            {
                $(this).parents('.multi').find('div.loading-overlay').remove();
            }             
        });
    });
    
    if(typeof pk !== "undefined")
    {
        addEditablePk($clone,pk.encrypted_id,pk.id);
    }
    
    if($clone.is('tr'))
    {
        $dummy.parents('.data-container').find('table').append($clone);
    }
    else
    {
        $dummy.parents('.data-container').find('form').prepend($clone);
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
            if(this.getAttribute('data-type')=="select2")
            {
                /*
                 * Resize to fit within space
                 */
                $(this).parent('.editable-select2').find('.select2-container').width($(this).parents('.editable-select2').width()*0.7);
            }
            return true;
        })
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
                }
                return true;
            });            
        }
    });    
}

(window.pr_store_reception = function() {
    
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
            $this.parents('.multi').find('div.loading-overlay').remove();
            var response = $.parseJSON(params.response);
            //Set new pk value
            addEditablePk($(this).parents('.multi'),response.encrypted_id,response.id);
        }
        return true;
    }).on('submit',function(){
        if(this.getAttribute('data-pk') == "0")
        {
            $(this).parents('.multi').prepend("<div class='loading-overlay'></div>");
        }
    }).on('error',function(){
        if(this.getAttribute('data-pk') == "0")
        {
            $(this).parents('.multi').find('div.loading-overlay').remove();
        }        
    });
    
    /*
     * Add New
     */
    $('.btn-add-new').on('click',function(){
        $this = $(this);
        $dummy = $this.parents('.data-container').find('.multi.dummy');
        if($dummy.length)
        {
            addMulti($dummy);
        }
        else
        {
            messenger_notiftop('Error: Cannot add new record','error');
        }
    });
    
    $('.data-container').on('click','.multi .btn-delete',function(){
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
    
    //Publish button
    $('#content').on('click','a.btn-publish',function(e){
        e.preventDefault();
        var $this = $(this);
        $.SmartMessageBox({
                title : "<span class='txt-color-greenDark'><i class='fa fa-share'></i> Publish Form?</span>",
                content : "A notification will be sent to the responsible parties",
                buttons : '[No][Yes]'

        }, function(ButtonPressed) {
            if (ButtonPressed == "Yes") {
                $this.attr('disabled','disabled');
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
                    },
                    error:function(xhr, status, error)
                    {
                        $this.removeAttr('disabled');
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
    
    messenger_hidenotiftop();
})();