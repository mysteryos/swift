/* 
 * Name: Salesman Edit
 * Description: Salesman form edit
 */

function addMulti($dummy,pk)
{
    $clone = $dummy.clone();
    $clone.removeClass('hide dummy');
    $clone.find('.editable').removeClass('dummy');
    $clone.find('.editable').each(function(){
        if(this.getAttribute('data-type')=="select2" && $(this).hasClass('client-editable'))
        {
            $(this).editable({
                disabled: $this.hasClass('editable-disabled'),
                placeholder: "Select a customer",
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
        else if(this.getAttribute('data-type')=="date" && this.getAttribute('data-name')=="date" && this.getAttribute('data-context')=="budget")
        {
            $(this).editable({
                disabled: $this.hasClass('editable-disabled'),
                format: 'mm-yyyy',
                viewformat: 'mm-yyyy',
                datepicker: {
                    onChangeMonthYear: function(  year,  month,  inst){
                        $(this).datepicker('setDate', new Date(year, month-1, 1));
                    }
                }                
            });
        }        
        else
        {
            $(this).editable({
                disabled: $(this).hasClass('editable-disabled')
            });
        }
        
        $(this).on('shown',function(e){
            presenceChannelCurrent.trigger('client-editable-shown',{user: presenceChannelCurrent.members.me ,name: $(this).attr('data-name'),pk: $(this).attr('data-pk'), id: this.id});
            if(this.getAttribute('data-type')=="select2")
            {
                /*
                 * Resize to fit within space
                 */
                $(this).parent('.editable-select2').find('.select2-container').width($(this).parents('.editable-select2').width()*0.6);
            }
            return true;
        }).on('hidden',function(e,reason){
            presenceChannelCurrent.trigger('client-editable-hidden',{user: presenceChannelCurrent.members.me, name: $(this).attr('data-name'),pk: $(this).attr('data-pk'), id: this.id})
        }).on('save',function(e,params){
            //First time save, set primary key
            if(this.getAttribute('data-pk') == "0")
            {
                $(this).parents('fieldset.multi').find('div.loading-overlay').remove();
                var response = $.parseJSON(params.response);
                //Set new pk value
                addEditablePk($(this).parents('fieldset'),response.encrypted_id,response.id);
                //Trigger Channel Event
                presenceChannelCurrent.trigger('client-multi-add',{user: presenceChannelCurrent.members.me , pk: response, context: $dummy.attr('data-name')});
                
                if(this.getAttribute('data-context') == "client")
                {
                    $(this).editable('disable');
                }
            }
            
            //Trigger Single Value Save as well
            presenceChannelCurrent.trigger('client-editable-save',{user: presenceChannelCurrent.members.me, name: this.getAttribute('data-name'),pk: $(this).attr('data-pk'), newValue: params.newValue, id: this.id})        
        }).on('submit',function(e){
            if(this.getAttribute('data-pk') == "0")
            {
                $(this).parents('fieldset.multi').prepend("<div class='loading-overlay'></div>");
            }            
        }).on('error',function(e){
            if(this.getAttribute('data-pk') == "0")
            {
                $(this).parents('fieldset.multi').find('div.loading-overlay').remove();
            }             
        });        
    });
    
    if(typeof pk !== "undefined")
    {
        addEditablePk($clone,pk.encrypted_id,pk.id);
    }
    
    $dummy.parents('.jarviswidget').find('form').prepend($clone);
    return true;
}

function addEditablePk($fieldset,$encryptedPk,$pk)
{
    var $editables = $fieldset.find('a.editable');
    $editables.editable('option', 'pk', $encryptedPk);
    $editables.attr('data-pk',$encryptedPk);
    $editables.each(function(){
        $this=$(this);
        $this.attr('id',$this.attr('data-context')+"_"+$this.attr('data-name')+"_"+$pk); 
    });       
    $editables.editable('option', 'pk', $encryptedPk);
    $editables.attr('data-pk',$encryptedPk);
    $editables.editable('enable');
    return true;
}

(window.salesman_edit = function () {
    
//Ribbon Buttons
    $('#ribbon a.btn-delete').on('click',function(e){
        e.preventDefault();
        var $this = $(this);
        var $delete = $this.children('i').hasClass('fa-trash-o');
        $.SmartMessageBox({
                title : "<i class='fa fa-times txt-color-red'></i> <span class='txt-color-red'><strong>Are you sure you wish to "+($delete? "delete" : "restore")+" this salesman?</strong></span> ?",
                content : "The salesman will be "+($delete? "locked from" : "unlocked for")+" editing after deletion",
                buttons : '[No][Yes]'

        }, function(ButtonPressed) {
                if (ButtonPressed == "Yes") {
                    Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
                        id: 'notif-top',
                        errorMessage: 'Error processing command',
                        successMessage: 'Salesman has been '+($delete? "deleted" : "restored"),
                        progressMessage: 'Please Wait...',
                        action: $.ajax,
                    },
                    {
                        type:'POST',
                        url: $this.attr('href'),
                        success:function()
                        {
                            window.setTimeout(function(){
                                $('a.btn-ribbon-refresh').click();
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
    
    //Bind pusher channel
    pusherSubscribeCurrentPresenceChannel(true,true);

    //Turn on inline Mode
    $.fn.editable.defaults.mode = 'inline';
    $.fn.editable.defaults.ajaxOptions = {type: "put"};

    $('.editable:not(.dummy)').each(function(){
        var $this = $(this);
        if(this.getAttribute('data-type')=="select2" && $(this).hasClass('client-editable'))
        {
            $(this).editable({
                disabled: $this.hasClass('editable-disabled'),
                placeholder: "Select a customer",
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
        else if(this.getAttribute('data-type')=="date" && this.getAttribute('data-name')=="date" && this.getAttribute('data-context')=="budget")
        {
            $(this).editable({
                disabled: $this.hasClass('editable-disabled'),
                format: 'mm-yyyy',
                viewformat: 'mm-yyyy',
                datepicker: {
                    onChangeMonthYear: function(  year,  month,  inst){
                        $(this).datepicker('setDate', new Date(year, month-1, 1));
                    }
                }                
            });
        }
        else
        {
            $this.editable({
               disabled: $this.hasClass('editable-disabled') 
            });

        }
        
        $this.on('shown',function(e){
            presenceChannelCurrent.trigger('client-editable-shown',{user: presenceChannelCurrent.members.me ,name: $(this).attr('data-name'),pk: $(this).attr('data-pk'), id: this.id});
            if(this.getAttribute('data-type')=="select2")
            {
                /*
                 * Resize to fit within space
                 */
                $(this).parent('.editable-select2').find('.select2-container').width($(this).parents('.editable-select2').width()*0.6);
            }
            return true;
        }).on('hidden',function(e,reason){
            presenceChannelCurrent.trigger('client-editable-hidden',{user: presenceChannelCurrent.members.me, name: $(this).attr('data-name'),pk: $(this).attr('data-pk'), id: this.id});
            return true;
        }).on('save',function(e,params){
            if($(this).editable('option','pk') !== "0")
            {
                //Bug fix for disappearing pks - Weird
                $(this).editable('option','pk',$(this).attr('data-pk'));
                presenceChannelCurrent.trigger('client-editable-save',{user: presenceChannelCurrent.members.me, name: $(this).attr('data-name'),pk: $(this).attr('data-pk'), newValue: params.newValue, id: this.id});
            }
            if(this.getAttribute('data-context') === "client")
            {
                $(this).editable('disable');
            }
            return true;
        });            
    });
    
    //Multi X-editable save
    $('.client-editable, .budget-editable').on('save',function(e,params){
        console.log('hum');
        //First time save, set primary key
        if(this.getAttribute('data-pk') == "0")
        {
            var response = $.parseJSON(params.response);
            $(this).parents('fieldset.multi').find('div.loading-overlay').remove();
            //Set new pk value
            addEditablePk($(this).parents('fieldset'),response.encrypted_id,response.id);
            //Trigger Channel Event
            presenceChannelCurrent.trigger('client-multi-add',{user: presenceChannelCurrent.members.me , pk: response, context: $(this).parents('fieldset').attr('data-name')});
            //Trigger Single Value Save as well
            presenceChannelCurrent.trigger('client-editable-save',{user: presenceChannelCurrent.members.me, name: this.getAttribute('data-name'),pk: this.getAttribute('data-pk'), newValue: params.newValue, id: this.id});
            
            if(this.getAttribute('data-context') == "client")
            {
                $(this).editable('disable');
            }
        }
        return true;
    }).on('submit',function(){
        if(this.getAttribute('data-pk') == "0")
        {
            $(this).parents('fieldset.multi').prepend("<div class='loading-overlay'></div>");
        }        
    }).on('error',function(){
        if(this.getAttribute('data-pk') == "0")
        {
            $(this).parents('fieldset.multi').find('div.loading-overlay').remove();
        }          
    });    
    
    /*
     * Add New
     */
    $('.btn-add-new').on('click',function(){
        $this = $(this);
        $dummy = $this.parents('.jarviswidget').find('fieldset.dummy');
        if($dummy.length)
        {
            addMulti($dummy);
        }
        else
        {
            messenger_notiftop('Error: Cannot add new record','error');
        }
    });    
    
    //Multi Delete
    $('.jarviswidget').on('click','fieldset.multi .btn-delete',function(){
        var $this = $(this);
        if(confirm('Are you sure you wish to delete this record?'))
        {
            var $thisname = $this.parents('fieldset.multi').attr('data-name').ucfirst();
            var $thiseditable = $this.parents('fieldset.multi').find('a.editable:first');
            if($thiseditable.attr('data-pk')=="0")
            {
                $this.parents('fieldset.multi').slideUp('500',function(){
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
                        presenceChannelCurrent.trigger('client-multi-delete',{user: presenceChannelCurrent.members.me , id: $thiseditable.attr('id'), context: $this.parents('fieldset.multi').attr('data-name')});
                        $this.parents('fieldset.multi').slideUp('500',function(){
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
    
    //Enable Commenting
    enableComments();    
    
    //Hide Loading Message
    messenger_hidenotiftop();    
})();