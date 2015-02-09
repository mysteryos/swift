/* 
 * Name: Order Tracking: Summary
 */
(window.ot_summary = function() {
    if ( ! $.fn.DataTable.isDataTable( '#summary_table' ) ) {
        $('#summary_table').dataTable({
            "aaSorting": [],
            "sPaginationType" : "bootstrap",
            "scrollX": true,
        });        
    }
    
    //Hide Loading Message
    messenger_hidenotiftop();    
})();