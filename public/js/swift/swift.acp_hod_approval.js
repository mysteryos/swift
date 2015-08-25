(window.acp_hod_approval = function () {
    
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
    $pvProcessInfo.on('click','.pv-row',function(e){
        //Highlight selected
        $pvProcessInfo.find('.pv-row').removeClass('pv-selected');
        var $row = $(this);
        $row.addClass('pv-selected');
        
        //Load Up document
        if($row.find('.doc-list').length)
        {
            if($docBrowser.find('ul.doc-list')[0].id !== $row.find('.doc-list')[0].id)
            {
                $docBrowser.html('');
                //Move doc list into view
                $row.find('.doc-list').clone(true).removeClass('hide').appendTo($docBrowser);
            }
            
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
    
    $pvProcessInfo.on('click','.btn-approve,.btn-reject',function(e){
        e.preventDefault();
        var $this = $(this);
        if(!$this.hasClass('saving'))
        {
            if($this.hasClass('btn-reject'))
            {
                var rejectComment = prompt("Please enter a reason for declining this form.");
                if(!rejectComment)
                {
                    return false;
                }
            }
            
            $this.addClass('saving');
            $.ajax({
                url: $this.attr('href'),
                data:'comment='+rejectComment,
                type: 'POST',
                success:function(response)
                {
                    $this.parents('.pv-row').addClass('pv-success');
                     
                    if($this.hasClass('btn-approve'))
                    {
                        $this.addClass('btn-success');
                    }

                    if($this.hasClass('btn-reject'))
                    {
                        $this.addClass('btn-danger');
                    }
                    $this.parent().find('a.btn').addClass('disabled').attr('disabled','disabled');
                    $this.parents('.pv-row').next().find('.pv-form').trigger('click');
                    $this.removeClass('saving');
                },
                error:function(xhr,textStatus,errorThrown)
                {
                    alert(xhr.responseText);
                    $this.removeClass('saving');
                }
            });
        }
        return false;
    });
    
    $pvProcessInfo.on('click','.btn-suggest',function(e){
        $pvProcessInfo.find('a.btn-suggest').removeClass('active');
        $(this).addClass('active'); 
    });
    
    //Hide Loading Message
    messenger_hidenotiftop();
})();    