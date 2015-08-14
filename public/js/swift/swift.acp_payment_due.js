/* 
 * Name: ACP Cheque Issue
 * Description:
 */

(window.acp_payment_due = function(){
    var $content = $('#content');
    tableHeightSize();

    function toggleHighlightBar()
    {
        $content.find('.pvform.highlight').length > 0 ? $('div.toggle-oncheck').show() : $('div.toggle-oncheck').hide();
    }

    $(window).resize(function() {
           tableHeightSize()
    })

    function tableHeightSize() {

           var tableHeight = $(window).height() - 212;

           if (tableHeight < 320) {
                   $('.table-wrap').css('height', 320 + 'px');
           } else {
                   $('.table-wrap').css('height', tableHeight + 'px');
           }

    }

    //Gets tooltips activated
    $("#cheque-issue-table [rel=tooltip]").tooltip();

    $("#cheque-issue-table input[type='checkbox']").change(function(e) {
           $(this).closest('tr').toggleClass("highlight", this.checked);
           toggleHighlightBar();
    });
    
    /*
     * Activate Filter
     */
    
    acp_filter();
    
    //Hide Loading Message
    messenger_hidenotiftop();
})();