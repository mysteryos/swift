function addMulti($dummy,pk)
{
    $clone = $dummy.clone();
    $clone.removeClass('hide dummy');
    $clone.find('.editable').removeClass('dummy');
    $clone.find('.editable').editable({
        disabled: $(this).hasClass('editable-disabled'),
        onblur: $(this).hasClass('editable-noblur') ? 'cancel' : 'submit'
    }).on('save',function(e,params){
        //First time save, set primary key
        if(this.getAttribute('data-pk') == "0")
        {
            var response = $.parseJSON(params.response);
            $(this).parents('fieldset.multi').find('div.loading-overlay').remove();
            //Set new pk value
            addEditablePk($(this).parents('fieldset'),response.encrypted_id,response.id);
            //Trigger Channel Event
            presenceChannelCurrent.trigger('client-multi-add',{user: presenceChannelCurrent.members.me , pk: response, context: $dummy.attr('data-name')});
            //Trigger Single Value Save as well
            presenceChannelCurrent.trigger('client-editable-save',{user: presenceChannelCurrent.members.me, name: this.getAttribute('data-name'),pk: this.getAttribute('data-pk'), newValue: params.newValue, id: this.id})
        }
        if(this.getAttribute('data-name')=="type" && this.getAttribute('data-context')=="payment")
        {
            $(this).parents('fieldset.fieldset-payment').find('div[class^="payment-"]').hide();
            $(this).parents('fieldset.fieldset-payment').find('div.payment-'+params.newValue).show();
        }
    }).on('shown',function(e){
        presenceChannelCurrent.trigger('client-editable-shown',{user: presenceChannelCurrent.members.me ,name: this.getAttribute('data-name'),pk: this.getAttribute('data-pk'), id: this.id})
    }).on('hidden',function(e,reason){
        presenceChannelCurrent.trigger('client-editable-hidden',{user: presenceChannelCurrent.members.me, name: this.getAttribute('data-name'),pk: this.getAttribute('data-pk'), id: this.id})
    }).on('save',function(e,params){
        if($(this).editable('option','pk') !== "0")
        {
            presenceChannelCurrent.trigger('client-editable-save',{user: presenceChannelCurrent.members.me, name: this.getAttribute('data-name'),pk: this.getAttribute('data-pk'), newValue: params.newValue, id: this.id})
        }
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

(window.acp_edit = function () {
    
    //Ribbon Buttons
    $('a.btn-ribbon-cancel').on('click',function(e){
        e.preventDefault();            
        var $this = $(this);
        $.SmartMessageBox({
                title : "<i class='fa fa-times txt-color-red'></i> <span class='txt-color-red'><strong>Are you sure you wish to cancel '"+document.getElementById('project-name').value+"' ?</strong></span> ?",
                content : "The form will be locked from editing after cancellation",
                buttons : '[No][Yes]'

        }, function(ButtonPressed) {
                if (ButtonPressed == "Yes") {
                    Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
                        id: 'notif-top',
                        errorMessage: 'Error cancelling accounts payable',
                        successMessage: 'Accounts payable has been cancelled',
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
    
    //Mark as important button
    
    $('a.btn-mark-important').on('click',function(e){
        e.preventDefault();
        var $this = $(this);
        var $important = $this.children('i').hasClass('fa-exclamation-triangle');
        $.SmartMessageBox({
                title : "<i class='fa fa-times txt-color-red'></i> <span class='txt-color-red'><strong>Are you sure you wish to "+($important? "unmark" : "mark")+" this accounts payable as important?</strong></span> ?",
                content : "All accounts payable users will receive a notice",
                buttons : '[No][Yes]'

        }, function(ButtonPressed) {
                if (ButtonPressed == "Yes") {
                    $this.attr('disabled');
                    Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
                        id: 'notif-top',
                        errorMessage: 'Error marking accounts payable',
                        successMessage: 'Accounts Payable has been '+($important? "unmarked" : "marked")+' as important',
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

    //Bind pusher channel
    pusherSubscribeCurrentPresenceChannel(true,true);

    //Turn on inline Mode
    $.fn.editable.defaults.mode = 'inline';
    $.fn.editable.defaults.ajaxOptions = {type: "put"};
    
    //General Info
    $('.editable:not(.dummy)').each(function(){
        var $this = $(this);
        if(this.getAttribute('data-type')=="select2" && this.getAttribute('data-name')=="supplier_code" && this.getAttribute('data-context')=="generalinfo")
        {
            $this.editable({
                disabled: $this.hasClass('editable-disabled'),
                onblur: $this.hasClass('editable-noblur') ? 'cancel' : 'submit',
                placeholder: 'Select a supplier',
                select2: {
                    allowClear: false,
                    minimumInputLength: 3,
                    id: function (item) {
                        return item.id;
                    },                    
                    ajax: {
                        url: '/ajaxsearch/searchsupplier',
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
                                found = $.map(data.suppliers, function (item) {
                                    return {
                                        id: item.Supplier_Code,
                                        name: $.trim(item.Supplier_Name),
                                        text: $.trim(item.Supplier_Name)+" (Code:"+item.Supplier_Code+")"
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
        else if(this.getAttribute('data-type')=="select2" && this.getAttribute('data-name')=="billable_company_code" && this.getAttribute('data-context')=="generalinfo")
        {
            $this.editable({
                disabled: $this.hasClass('editable-disabled'),
                onblur: $this.hasClass('editable-noblur') ? 'cancel' : 'submit',
                placeholder: "Select a billable company",
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
        else
        {
            $this.editable({
                disabled: $this.hasClass('editable-disabled'),
                onblur: $this.hasClass('editable-noblur') ? 'cancel' : 'submit',
            });
        }
        
        $this.on('shown',function(e){
            presenceChannelCurrent.trigger('client-editable-shown',{user: presenceChannelCurrent.members.me ,name: this.getAttribute('data-name'),pk: this.getAttribute('data-pk'), id: this.id});
            return true;
        }).on('hidden',function(e,reason){
            presenceChannelCurrent.trigger('client-editable-hidden',{user: presenceChannelCurrent.members.me, name: this.getAttribute('data-name'),pk: this.getAttribute('data-pk'), id: this.id});
            return true;
        }).on('save',function(e,params){
            if($(this).editable('option','pk') !== "0")
            {
                //Bug fix for disappearing pks - Weird
                $(this).editable('option','pk',this.getAttribute('data-pk'));
                presenceChannelCurrent.trigger('client-editable-save',{user: presenceChannelCurrent.members.me, name: this.getAttribute('data-name'),pk: this.getAttribute('data-pk'), newValue: params.newValue, id: this.id});
            }
            if(this.getAttribute('data-name')=="type" && this.getAttribute('data-context')=="payment")
            {
                $(this).parents('fieldset.fieldset-payment').find('div[class^="payment-"]').hide();
                $(this).parents('fieldset.fieldset-payment').find('div.payment-'+params.newValue).show();
            }
            return true;
        });
    });
    
    //Multi
    $('.creditnote-editable, .purchaseorder-editable, .invoice-editable, .paymentvoucher-editable, .payment-editable').on('save',function(e,params){
        var $this = $(this);
        //First time save, set primary key
        if(this.getAttribute('data-pk') == "0")
        {
            //Remove Overlay
            $this.parents('fieldset.multi').find('div.loading-overlay').remove();
            var response = $.parseJSON(params.response);
            //Set new pk value
            addEditablePk($(this).parents('fieldset'),response.encrypted_id,response.id);
            //Trigger Channel Event
            presenceChannelCurrent.trigger('client-multi-add',{user: presenceChannelCurrent.members.me , pk: response, context: $this.parents('fieldset').attr('data-name')});
            //Trigger Single Value Save as well
            presenceChannelCurrent.trigger('client-editable-save',{user: presenceChannelCurrent.members.me, name: $this.attr('data-name'),pk: $this.attr('data-pk'), newValue: params.newValue, id: this.id})
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
        var $this = $(this);
        var $dummy = $this.parents('.jarviswidget').find('fieldset.dummy');
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

    $('.jarviswidget').on('click','fieldset.multi .btn-delete',function(){
        var $this = $(this);
        var $parent = $this.parents('.widget-body');
        console.log($this);
        if(confirm('Are you sure you wish to delete this record?'))
        {
            var $thisname = $this.parents('fieldset.multi').attr('data-name').ucfirst();
            var $thiseditable = $this.parents('fieldset.multi').find('a.editable:first');
            if($thiseditable.attr('data-pk')=="0")
            {
                $this.parents('fieldset.multi').slideUp('500',function(){
                    $(this).remove();
                    messenger_notiftop($thisname+' entry has been deleted','success');
                    window.setTimeout(function(){
                        if($parent.find('fieldset.multi').not('.dummy').length == 0)
                        {
                            var $dummy = $parent.find('fieldset.dummy');
                            if($dummy.length)
                            {
                                addMulti($dummy);
                                messenger_notiftop($thisname+' dummy entry has been added','info');
                            }
                        }
                    },0);
                });
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
                            window.setTimeout(function(){
                                if($parent.find('fieldset.multi').not('.dummy').length == 0)
                                {
                                    var $dummy = $parent.find('fieldset.dummy');
                                    if($dummy.length)
                                    {
                                        addMulti($dummy);
                                        messenger_notiftop($thisname+' dummy entry has been added','info');
                                    }
                                }
                            },0);
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
    
    $('#acp-list').on('click','tr[data-url] td',function(){
        $.pjax({
            url: $(this).parent('tr').attr('data-url'),
            container: '#main'
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

    /*
     * Dropzone
     */

    // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
    var previewNode = document.querySelector("#template");
    var previewTemplate = previewNode.parentNode.innerHTML;
    previewNode.style.display = "none";
    var removeFileEvent;
    var theAwesomeDropZone= new Dropzone(document.getElementById('content'),{
        url:'/accounts-payable/upload/'+$('#id').val(),
        clickable: '#btn-upload',
        previewsContainer: "#upload-preview",
        previewTemplate: previewTemplate,
        autoQueue: true,
        createImageThumbnails : false,
        accept: function(file, done) {
            if(file.name.indexOf('+') !== -1)
            {
                done("Please remove the plus sign from your document's name.");
            }
            else
            {
                done();
            }
        },
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
                                    url: '/accounts-payable/upload/'+$(file.previewElement).attr('data-id'),
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
                        url: '/accounts-payable/upload/'+$thisParent.attr('data-id'),
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
        }
    });

    theAwesomeDropZone.on("sending", function(file,xhr,formdata) {
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

    theAwesomeDropZone.on("success", function (file,response){
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
            $editable.attr('id',res.id_normal);
            $editable.removeAttr('data-value');
            $editable.editable();
            $editable.removeClass('dummy hide');
        }
        //Hide Progress Bar
        $(file.previewElement).find('div.progress').addClass('hide');
        return file;
    });

    theAwesomeDropZone.on("cancelled",function(){
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

    theAwesomeDropZone.on('error',function(file,errorMsg,xhr){
        Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
            showCloseButton: true,
            type: 'error',
            hideAfter: 0,
            message: 'Upload Failed: "'+file.name+'" - '+errorMsg
        });            
    });

    theAwesomeDropZone.on('uploadprogress',function(file,progress){
        var $uploadprogress = $('#upload-progress');
        if($uploadprogress[0].length != 0)
        {
            $uploadprogress.animate({width:progress+'%'},500);
        }
    });

    theAwesomeDropZone.on('totaluploadprogress',function(progress){
       if(typeof uploadmsg !== "undefined" && progress == 100)
       {
           uploadmsg.hide();
       }            
    });
    
    //Drag & Drop File Fix
    
    var dragEle = document.getElementById( "content" );
    var dragster = new Dragster(dragEle);
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
    
    $('#content').on("drop", function (event) {
        dragster.dragleave(event);
    });

    /*
     * Google Doc Viewer
     */
    $('#acp-docs').on('click','a.file-view',function(e){
        e.preventDefault();
        var $this = $(this);
        //For Images
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
               href: "http://docs.google.com/viewer?url="+$this.attr('href')+"&embedded=true",
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