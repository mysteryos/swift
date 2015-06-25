(window.acp_cheque_sign = function () {
    
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
    
    //Load doc on click
    $pvProcessInfo.on('click','.invoice-row',function(e){
        var $this = $(this);
        //Highlight selected
        $pvProcessInfo.find('.invoice-row').removeClass('bg-color-yellow');
        $this.addClass('bg-color-yellow');
        
        //Load Up document
        if($this.find('.doc-list').length)
        {
            if($docBrowser.find('ul.doc-list').length && $docBrowser.find('ul.doc-list')[0].id !== $this.find('.doc-list')[0].id)
            {
                $docBrowser.html('');
            }
            
            //Move doc list into view
            $this.find('.doc-list').clone(true).removeClass('hide').appendTo($docBrowser);
            
            var $url = $this.find('.doc-list li:first').attr('data-href');
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
    
    $pvProcessInfo.on('click','.btn-sign-cheque',function(e){
        var $this = $(this);
        $this.addClass('disabled');
        $this.attr('disabled','disabled');
        Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
            id: 'notif-top',
            errorMessage: 'Error: form not saved',
            successMessage: 'Form has been saved',
            progressMessage: 'Saving cheque signatures',
            action: $.ajax,
        },
        {
            type:'POST',
            url: $this.parents('.cs-form').attr('action'),
            data: $this.parents('.cs-form').serialize(),
            success:function()
            {
                $this.removeClass('btn-default');
                $this.removeClass('btn-error');
                $this.addClass('btn-success');
                $this.find('i.fa-check').removeClass('hide');
            },
            error:function(xhr, status, error)
            {
                $this.addClass('btn-error');
                $this.removeClass('disabled');
                $this.removeAttr('disabled');
                return xhr.responseText;                
            }
        });
    });
    
    //Hide Loading Message
    messenger_hidenotiftop();
})();    