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
     * Filter Menu
     */
    
    $('#filter-btn').popover({
       content: function() {
           return document.getElementById('filter-popover').innerHTML;
       },
       html: true,
       placement: 'bottom',
       trigger: 'manual',
       container: '#filter-btn'
    }).on("shown.bs.popover",function(){
        var $filterBtnDatePicker = $('#filter-btn').find('.datepicker');
        $filterBtnDatePicker.datepicker('destroy');        
        $filterBtnDatePicker.removeClass('hasDatePicker');
        $filterBtnDatePicker.datepicker({
            dateFormat : 'dd/mm/yy',
            prevText : '<i class="fa fa-chevron-left"></i>',
            nextText : '<i class="fa fa-chevron-right"></i>',            
        });
        
        $('#filter-btn').find('#filter-btn-close').on("click",function(){
            $('#filter-btn').popover('hide');
            return false;
        });
        
        $('#filter-btn').find('form').on('submit',function(e){
             $.pjax.submit(e,'#main');
             return false;
        });
        
    }).on("hide.bs.popover",function(){
        $('#filter-btn').find('.datepicker').datepicker('destroy');
    });
    
    $('#filter-btn.popover-trigger').on("click",function(e){
        if(!$(e.target).parents('div.popover').length)
        {
            if($(this).find('div.popover:visible').length)
            {
                $(this).popover('hide');
            }
            else
            {
                $(this).popover('show');
            }
        }
    });
    
    //Hide Loading Message
    messenger_hidenotiftop();
})();