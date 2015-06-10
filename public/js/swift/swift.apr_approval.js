/* 
 * Name: A&P Request: Approvals
 */
(window.apr_approval = function() {
    var isSaving = false;
    
    function saveApproval($this)
    {
        var $parent = $this.parent();
        
        if(!$this.hasClass('on'))
        {
            //Remove all toggles
            $parent.find('a.btn-approve').removeClass('on');
            if(!isSaving)
            {
                isSaving = true;
            }
            $this.addClass('on');
            $.ajax({
                 type:'PUT',
                 url: $this.attr('url'),
                 data: {'value':$this[0].getAttribute('data-value'),'pk':$.trim($this[0].getAttribute('data-pk')),'name':$this[0].getAttribute('data-name')},
                 success:function(msg)
                 {
                    var jsonmsg = $.parseJson(msg);
                    if(jsonmsg.encrypted_id)
                    {
                       $this.attribute('data-pk',jsonmsg.encrypted_id);
                    }
                    isSaving = false;
                 },
                 error:function(xhr)
                 {
                    $this.removeClass('on');
                    messenger_notiftop(xhr.responseText,'error');
                    isSaving = false;
                 }
            });
        }
    }
    
    $.document_.on('pjax:start',function(e){
        if(isSaving)
        {
            if(!confirm("Information is being saved. Do you wish to navigate away from this page? This will result in loss of data."))
            {
               e.preventDefault();
               return false;
            }
        }
        
        $.document_.off('pjax:start');
        return true;
    });
    
    $.maindiv.on('click','.btn-approve',function(e){
        e.stopPropagation();
        var $this =  $(this);
        saveApproval($this);
        return false;
    });
    
    $.maindiv.on('click','tr.approval_product_row td.pointable',function(e){
        var $btn_accept = $(this).parents('tr').find('a.btn-accept');
        var $btn_reject = $(this).parents('tr').find('a.btn-reject');
        
        var $img_accept = $('<div/>',{
            class: 'btn-approve, btn-accept',
            html: '<i class="fa fa-lg fa-check fa-lg"></i> Approved',
        }).attr('style','position:absolute;');
        
        var $img_reject = $('<div/>',{
            class: 'btn-approve, btn-reject',
            html: '<i class="fa fa-lg fa-times fa-lg"></i> Rejected'
        }).attr('style','position:absolute;');
        
        if($btn_accept.hasClass('on'))
        {
            $img_reject.offset({
                top: e.pageY - $img_reject.outerHeight(),
                left: e.pageX - ($img_reject.outerWidth()-12)
            }).appendTo('body').effect('puff',null,700,function(){
                $(this).remove();
            });
            
            $btn_reject.trigger('click');
        }
        else
        {
            $img_accept.offset({
                top: e.pageY - $img_accept.outerHeight(),
                left: e.pageX - ($img_accept.outerWidth()-12)
            }).appendTo('body').effect('puff',null,700,function(){
                $(this).remove();
            });
            
            $btn_accept.trigger('click');
        }
    });
    
    messenger_hidenotiftop();
})();