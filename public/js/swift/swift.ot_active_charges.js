(window.ot_active_charges = function () {
    
    if($('#storage_table_wrapper').length === 0 )
    {
        $('#storage_table').dataTable({
            "aaSorting": [],
            "sPaginationType" : "bootstrap",
            "scrollX": true,
            "bDestroy": true
        });
    }
    else
    {
        $.pjax({
            url: window.location.href
        });
    }
    
    if($('#demurrage_table_wrapper').length === 0 )
    {
        $('#demurrage_table').dataTable({
            "aaSorting": [],
            "sPaginationType" : "bootstrap",
            "scrollX": true,
            "bDestroy": true
        });
    }
    else
    {
        $.pjax({
            url: window.location.href
        });
    }    
    
    
    //Hide Loading Message
    messenger_hidenotiftop();    
})();
    