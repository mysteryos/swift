(window.salescommission_commissionoverview = function() {
    
    $('#content').on('click','tr[data-url] td',function(){
        $.pjax({
            url: $(this).parent('tr').attr('data-url'),
            container: '#main'
       });
    });    
    
    //Hide Loading Message
    messenger_hidenotiftop();
})();