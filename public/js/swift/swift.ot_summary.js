/* 
 * Name: Order Tracking: Summary
 */
(window.ot_summary = function() {
    var table = $('#summary_table').dataTable({
        "aaSorting": [],
        "sPaginationType" : "bootstrap",
        "scrollX": true,
    });
    
    //new $.fn.dataTable.FixedColumns( table );
    
    //Hide Loading Message
    messenger_hidenotiftop();    
})();