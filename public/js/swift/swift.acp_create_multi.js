function multiShowForm()
{
    if($('#doc-list').find('.check-document:checked:enabled').length)
    {
        $('#no-form').hide();
        $('#form-container').show();
    }
    else
    {
        $('#no-form').show();
        $('#form-container').hide();        
    }
}

(window.acp_create_multi = function () {
    var $multiDropzone = $('#multi-dropzone');
    var $formContent = $('#form-content');
    var $divHeight = $(window).height()-$('#doc-content').offset().top;
    var $noDoc = $('#no-doc');
    var $docContainer = $('#doc-container');
    var uploadmsg;
    
    $('#doc-list').resizable({
        containment: "#acp_create_multi_container",
        handles: 'e',
        alsoResizeReverse: "#doc-content",
        minWidth: 300
    }).height($divHeight);
    
    $('#form-content').resizable({
        containment: "#acp_create_multi_container",
        handles: 'e',
        alsoResizeReverse: "#doc-content",
        minWidth: 300
    }).height($divHeight);
    
    $('#doc-content').resizable({
        containment: "#acp_create_multi_container",
        handles: 'e',
        minWidth: 500
    }).height($divHeight);
    
    /*
     * File View Initialize
     */
    
    $('#doc-list').on('click','a.file-view',function(e){
        e.preventDefault();
        var $this = $(this);
        if($this.attr('data-type') && $this.attr('href'))
        {
            var $appendDoc;
            switch($this.attr('data-type'))
            {
                case "image/jpeg":
                case "image/png":
                case "image/bmp":
                case "image/jpg":
                    $appendDoc = '<img style="width:100%;height:auto;" class="image-view" src="'+$this.attr('href')+'"/>';
                    $noDoc.hide();
                    $docContainer.show().html('').append($appendDoc);                    
                    break;
                case "application/pdf":
                    $appendDoc = '<iframe class="document-iframe" style="width:100%;height:100%" src="/pdfviewer/viewer.html?file='+$this.attr('href')+'"></iframe>';
                    $noDoc.hide();
                    $docContainer.show().html('').append($appendDoc);                      
                    break;
                case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
                case "application/vnd.ms-excel":
                case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
                case "application/msword":
                default:
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
                    break;
            }
        }
        else
        {
            messenger_notiftop('Unable to initialize document preview','error');
            $docContainer.html('').hide();
            $noDoc.show();
        }
        return false;
    });
    
    /*
     * Checkbox
     */
    
    $('#doc-list').on('click','.check-document',function(e){
        multiShowForm();
    });
    
    $('#check-all-files').on('click',function(e){
        $('#doc-list .check-document').prop('checked',this.checked);
        multiShowForm();
    });
    
    /*
     * Initialize File Upload
     */
    var previewNode = document.querySelector("#template");
    var previewTemplate = previewNode.parentNode.innerHTML;
    previewNode.style.display = "none";    
    var myDropzone = new Dropzone('div#content', {
                        url: $multiDropzone.attr('data-action'),
                        clickable: '#btn-upload',
                        previewsContainer: "#multi-dropzone",
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
                                                    url: $multiDropzone.attr('data-delete')+'/'+$(file.previewElement).attr('data-name'),
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
                            $('#doc-list .btn.delete').on('click',function(){
                                var $thisParent = $(this).closest('.row.dz-success');
                                if(confirm('Are you sure you want to delete this file: '+$thisParent.attr('data-name')+' ?'))
                                {
                                    var deletemsg = Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
                                        type: 'info',
                                        message: 'Deleting file '+$thisParent.attr('data-name')
                                    });

                                    $.ajax({
                                        type:'DELETE',
                                        url: $multiDropzone.attr('data-delete')+'/'+$thisParent.attr('data-name'),
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
    
    myDropzone.on("sending", function(file,xhr,formdata) {
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
                var icon = '<i class="fa fa-file-image-o row-space-right-1"></i>';
                break;
            case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
            case "application/vnd.ms-excel":
                var icon = '<i class="fa fa-file-excel-o row-space-right-1"></i>';
                break;
            case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
            case "application/msword":
                var icon = '<i class="fa fa-file-word-o row-space-right-1"></i>';
                break;
            case "application/pdf":
                var icon = '<i class="fa fa-file-pdf-o row-space-right-1"></i>';
                break;                        
            default:
                var icon = '<i class="fa fa-file-o row-space-right-1"></i>';
                break;
        }

        $(file.previewElement).find('span.name').prepend(icon);
        $(file.previewElement).find('div.progress').removeClass('hide');
        return file;
    });

    myDropzone.on("success", function (file,response){
        Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
            showCloseButton: true,
            type: 'success',
            message: "Upload Complete: "+file.name,
            hideAfter: 3
        });
        var res = $.parseJSON(response);
        if(res.success)
        {
            //Set Anchor
            $(file.previewElement).find('span.name').wrapInner("<a class='file-view' data-type='"+file.type+"' href='"+res.url+"'/>");            
            //Set Doc Id
            $(file.previewElement).attr('data-url',res.url);
            $(file.previewElement).attr('data-name',res.name);
            $(file.previewElement).find('input.check-document').val(res.name).removeAttr('disabled').show();
            //CleanUp
            $(file.previewElement).removeClass('dz-processing').removeAttr('id');            
        }
        //Hide Progress Bar
        $(file.previewElement).find('div.progress').addClass('hide');
        return file;
    });
    
    myDropzone.on("cancelled",function(){
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

    myDropzone.on('error',function(file,errorMsg,xhr){
        Messenger({extraClasses: 'messenger-fixed messenger-on-bottom messenger-on-right'}).post({
            showCloseButton: true,
            type: 'error',
            hideAfter: 0,
            message: 'Upload Failed: "'+file.name+'" - '+errorMsg
        });            
    });

    myDropzone.on('uploadprogress',function(file,progress){
        var $uploadprogress = $('#upload-progress');
        if($uploadprogress[0].length != 0)
        {
            $uploadprogress.animate({width:progress+'%'},500);
        }
    });

    myDropzone.on('totaluploadprogress',function(progress){
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
     * Form
     */
    
    /*
     * Clean previous instances of select2
     */
    $('#customercode,#suppliercode,#hodapproval').val('');
    
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
    
    $('#hodapproval').select2({
        multiple: true,
        query: function (query){
            var data = {results: []};

            $.each($.parseJSON(document.getElementById('hod_user_list').value), function(){
                if(query.term.length === 0 || this.name.toLowerCase().indexOf(query.term.toLowerCase()) >= 0 ){
                    data.results.push({id: this.id, text: this.name });
                }
            });

            query.callback(data);
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
    
    //Comments
    $('#comment-textarea').atwho({
        at: "@",
        data: '/ajaxsearch/userall',
        search_key: 'name',
        limit: 5,
        delay: 300,
        tpl: '<li data-value="${name}">${name} <small>${email}</small></li>',
        insert_tpl: '<input type="button" value="@${name}" data-id="${id}" title="${email}" class="usermention btn btn-default btn-xs" />',
        show_the_at: true
    });
    
//    //On Mention Add
//    $('#comment-textarea').on('inserted.atwho',function(e,$li){
//        document.getElementById('input_mentions').value = $('#comment-textarea').find('.usermention').map(function(){
//            return $(this).attr('data-id');
//        }).get().join();
//    });
//    
//    //On Mention Delete
//    $formContent.on('remove','.usermention',function(){
//        document.getElementById('input_mentions').value = $('#comment-textarea').find('.usermention').map(function(){
//            return $(this).attr('data-id');
//        }).get().join();        
//    });
    
    var multi_form_validator = $('#multi_form').validate({
        ignore: '',
        rules: {
            billable_company_code: {
                required: true
            },
            supplier_code: {
                required: true
            },
            'document[]': {
                required: true
            }
        },
        messages: {
            billable_company_code: {
                required: 'Please select a customer'
            },
            supplier_code: {
                required: 'Please select a supplier'
            },
            hodapproval: {
                required: 'Please select HOD'
            },
            'document[]': {
                required: 'Select a document'
            }
        },
        errorPlacement: function(error, element) {
            if (element.attr("name") == "document[]" )
            {
                error.appendTo(element.parents('div.dz-success').find('strong.error'));
            }
            else
            {
                error.insertAfter(element);
            }
        },
        // Ajax form submition
        submitHandler : function(form) {
                //Fill in Mentions
                document.getElementById('input_mentions').value = $('#comment-textarea').find('.usermention').map(function(){
                    return $(this).attr('data-id');
                }).get().join();
                document.getElementById('input_comment').value = $('#comment-textarea').text();
                
                var savemsg = Messenger({extraClasses:'messenger-on-top messenger-fixed'}).post({
                                message: 'Saving Accounts Payable form',
                                type: 'info',
                                id: 'notif-top'
                              });
                              
                $formContent.prepend("<div class='loading-overlay'></div>");
                
                $('#btn-save,#btn-save-publish').attr('disabled','disabled').addClass('disabled');
                
                $(form).ajaxSubmit({
                        dataType: 'json',
                        success : function(data) {
                            savemsg.update({
                                type: 'success',
                                message: 'Save Success!'
                            });
                            
                            $('#btn-save,#btn-save-publish').removeAttr('disabled').removeClass('disabled');
                            
                            $formContent.find('div.loading-overlay').remove();
                            
                            $('input.check-document:checked').each(function(){
                                $(this).parents('div.dz-success').remove();
                            });
                            
                            if($('div.dz-success').length === 0)
                            {
                                $('#no-form').show();
                                $('#form-container').hide();
                            }
                        },
                        error: function (xhr, status, error) {
                            savemsg.update({
                                type: 'error',
                                message: xhr.responseText,
                                hideAfter: 5
                            });
                            
                            $('#btn-save,#btn-save-publish').removeAttr('disabled').removeClass('disabled');
                            
                            $formContent.find('div.loading-overlay').remove();
                        }
                });
                return false;
        }
    });
    
    $('#btn-save').on('click',function(e){
        $('#hodapproval').rules('remove');
    });
    
    $('#btn-save-publish').on('click',function(e){
        $('#hodapproval').rules('add',{
            required: true
        });
    });
    
    $('#btn-reset').on('click',function(){
        $('#customercode,#suppliercode,#hodapproval').select2('close').select2("val", "");
        document.getElementById('input_mentions').value = "";
        document.getElementById('comment-textarea').innerHTML = "";
        document.getElementById('input_comment').value = "";
        $('#multi_form div').removeClass('has-error');
        multi_form_validator.resetForm();
        multi_form_validator.reset();
        $('#no-form').show();
        $('#form-container').hide();        
        return true;
    });
    
    
    
    //Hide Loading Message
    messenger_hidenotiftop();    
})();