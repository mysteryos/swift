(window.acp_payment_voucher_process = function () {

    var $pvProcessDoc = $('#pv-process-doc');
    var $pvProcessContainer = $('#pv_process_container');
    var $pvProcessInfo = $('#pv-process-info');
    var $iframe = $('<iframe/>');
    var $noDoc = $('#no-doc');
    var $docBrowser = $('#doc-browser');

    //var search
    $('#search-pv').keyup(function(e){
        //ESC Key
        if(e.keyCode === 27)
        {
            $(this).val('');
            $('#pv-process-info .panel').show();
        }
        else
        {
            var val = '^(?=.*\\b' + $.trim($(this).val()).split(/\s+/).join('\\b)(?=.*\\b') + ').*$',
                reg = RegExp(val, 'i'),
                text;

            $('#pv-process-info .panel').show().filter(function() {
                text = $(this).text().replace(/\s+/g, ' ');
                return !reg.test(text);
            }).hide();
        }
    });

    $pvProcessInfo.on('submit','.pv-form',function(e){
       e.preventDefault();
       return false;
    });

    $docBrowser.on('click','li',function(e){
        var $this = $(this);
        if(!$this.hasClass('doc-selected'))
        {
            $this.parents('ul').find('li').removeClass('doc-selected');
            $this.addClass('doc-selected');

            var $url = $this.attr('data-href');
            if($pvProcessDoc.find('iframe').length)
            {
                if($pvProcessDoc.find('iframe').attr('src') !== $url)
                {
                    $pvProcessDoc.find('iframe').attr('src',$url);
                }
                return;
            }

            $pvProcessDoc.append($iframe.attr('src',$url));
        }
    });

    //Load doc on focus
    $pvProcessInfo.on('focus','.payment-voucher-val',function(e){
        //Highlight selected
        $pvProcessInfo.find('.pv-row').removeClass('pv-selected');
        var $row = $(this).parents('.pv-row');
        $row.addClass('pv-selected');

        //Load Up document
        if($row.find('.doc-list').length)
        {
            if($docBrowser.find('ul.doc-list').length === 0 || $docBrowser.find('ul.doc-list')[0].id !== $row.find('.doc-list')[0].id)
            {
                $docBrowser.html('');
            }

            //Move doc list into view
            $row.find('.doc-list').clone(true).removeClass('hide').appendTo($docBrowser);

            var $url = $row.find('.doc-list li:first').attr('data-href');
            $noDoc.hide();
            if($pvProcessDoc.find('iframe').length)
            {
                if($pvProcessDoc.find('iframe').attr('src') !== $url)
                {
                    $pvProcessDoc.find('iframe').attr('src',$url);
                }
                return;
            }

            $pvProcessDoc.append($iframe.attr('src',$url));
        }
        else
        {
            $pvProcessDoc.find('iframe').remove();
            $noDoc.show();
        }
    });

    $pvProcessInfo.on('keydown','.payment-voucher-val',function(e){
       //Arrow Down
       if(e.keyCode === 40)
       {
           var $nextParent = $(this).parents('.pv-row').next('.pv-row');
           if($nextParent.length)
           {
               $nextParent.find('.payment-voucher-val').focus();
           }
           return true;
       }

       //Arrow Up
       if(e.keyCode === 38)
       {
           var $previousParent = $(this).parents('.pv-row').prev('.pv-row');
           if($previousParent.length)
           {
               $previousParent.find('.payment-voucher-val').focus();
           }
           return true;
       }

       //Enter Key
       if(e.keyCode === 13)
       {
           var $this = $(this);
           //Save
           if($.trim($this.val()) === "")
           {
               alert('Please input a payment voucher number');
           }
           else
           {
               if(!$this.hasClass('saving'))
               {
                   $this.addClass('saving');
                   var $form = $this.parents('.pv-form');
                   $.ajax({
                       url: $form.attr('action'),
                       data: $form.serialize(),
                       type: 'POST',
                       dataType:'json',
                       success:function(response)
                       {
                            if(typeof response.id !== "undefined")
                            {
                                $form.find('.pv-id-val').val(response.id);
                            }
                            $this.parents('.pv-row').addClass('pv-success');
                            $this.removeClass('saving');
                            var $nextParent = $this.parents('.pv-row').next('.pv-row');
                            if($nextParent.length)
                            {
                                $nextParent.find('.payment-voucher-val').focus();
                            }
                       },
                       error:function(xhr,textStatus,errorThrown)
                       {
                            alert(xhr.responseText);
                            $this.removeClass('saving');
                       }

                   });
               }
           }
       }
    });
    //Hide Loading Message
    messenger_hidenotiftop();
})();