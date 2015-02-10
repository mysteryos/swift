/* 
 * Name: Order Tracking: Summary
 */
(window.ot_summary = function() {
    if($('#summary_table_wrapper').length === 0 )
    {
        $('#summary_table').dataTable({
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