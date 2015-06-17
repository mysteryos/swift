/* 
 * Name: ACP Cheque Issue
 * Description:
 */

(window.acp_payment_cheque_issue = function(){
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
     * Tick Menu
     */
    
    $content.on('click','.btn-tick-all',function(){
        $content.find('tr.pvform').addClass('highlight')
                .find('input[type="checkbox"]').prop("checked",true);
        toggleHighlightBar();
    });
    
    $content.on('click','.btn-tick-clear',function(){
        $content.find('tr.pvform').removeClass('highlight')
                .find('input[type="checkbox"]').prop("checked",false);
        toggleHighlightBar();
    });
    
    $content.on('click','.btn-tick-nobatchnumber',function(){
        $content.find('tr.pvform').removeClass('highlight')
                .find('input[type="checkbox"]').prop("checked",false);
        var $pvlist = $content.find('.input-batchnumber').filter(function(){
            return this.value === "";
        });
        $pvlist.parents('tr.pvform').addClass('highlight')
                .find('input[type="checkbox"]').prop("checked",true);
        toggleHighlightBar();
    });
    
    $content.on('click','.btn-tick-nopvnumber',function(){
        $content.find('tr.pvform').removeClass('highlight')
                .find('input[type="checkbox"]').prop("checked",false);
        var $pvlist = $content.find('.input-paymentnumber').filter(function(){
            return this.value === "";
        });
        $pvlist.parents('tr.pvform').addClass('highlight')
                .find('input[type="checkbox"]').prop("checked",true);
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
    
    /*
     * Context menu
     */
    
    context.init({
        fadeSpeed: 100,
        filter: function ($obj){},
        above: 'auto',
        preventDoubleContext: true,
        compress: false
    });
    
    context.attach('tr.pvform',[
    {
        text: 'Set Payment Number',
        action: function(e,obj)
        {
            $('.dropdown-context').css({display:''}).find('.drop-left').removeClass('drop-left');
            setpaymentnum();
        }
    },
    {
        text: 'Set Batch Number',
        action: function(e,obj)
        {
            $('.dropdown-context').css({display:''}).find('.drop-left').removeClass('drop-left');            
            setbatchnum();
        }
    }
    ]);
    $content.find('tr.pvform').on('contextmenu',function(){
        var $this = $(this);
        if(!$this.hasClass('highlight'))
        {
            $this.addClass('highlight');
            $this.find('input[type="checkbox"]').prop('checked',true);
            toggleHighlightBar();
        }
    });
    
    $('#btn-setpayment').on('click',function(){
        setpaymentnum();
        return false;
    });
    
    $('#btn-setbatch').on('click',function(){
        setbatchnum();
        return false;
    });
    
    function setpaymentnum()
    {
        var paymentnum = prompt('Set payment number for '+$('.pvform.highlight').length+($('.pvform.highlight').length === 1 ? ' form' : ' forms'));
        if($.isNumeric(paymentnum))
        {
            $('tr.pvform.highlight').find('input.input-paymentnumber').val(paymentnum);
            $('tr.pvform.highlight').find('input.input-paymentnumber').each(function(){
                saveNumber($(this));
            });
        }
        else
        {
            if(paymentnum !== null)
            {
                messenger_notiftop("Payment number should be numeric.","error");
            }
        }        
    }
    
    function setbatchnum()
    {
        var batchnum = prompt('Set batch number for '+$('.pvform.highlight').length+($('.pvform.highlight').length === 1 ? ' form' : ' forms'));
        if($.isNumeric(batchnum))
        {
            $('tr.pvform.highlight').find('input.input-batchnumber').val(batchnum);
            $('tr.pvform.highlight').find('input.input-batchnumber').each(function(){
                saveNumber($(this));
            });            
        }
        else
        {
            if(batchnum !== null)
            {
                messenger_notiftop("Batch number should be numeric.","error");
            }
        }
    }
    
    function saveNumber($this)
    {
        if($this.length)
        {
            if($this.val() !== $this.attr('data-prev-value'))
            {
                Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
                    id: 'notif-top',
                    errorMessage: 'Error: save unsuccessful',
                    successMessage: 'Save Complete',
                    progressMessage: 'Please Wait...',
                    action: $.ajax,
                },
                {
                    type:'PUT',
                    url: $this.attr('data-url'),
                    data: {"pk":$this.attr('data-pk'),"value":$this.val()},
                    success:function(result)
                    {
                        $this.attr("data-prev-value",$this.val());
                        if($this.attr('data-pk') === "0")
                        {
                            $this.parents('.pvform').find('.input-paymentnumber,.input-batchnumber').attr('data-pk',result.encrypted_id);
                        }
                        $this.removeClass('bg-color-redLight');
                        $this.removeClass('bg-color-light-orange');                        
                        $this.addClass('bg-color-light-green');
                    },
                    error:function(xhr, status, error)
                    {
                        $this.removeClass('bg-color-light-green');
                        $this.removeClass('bg-color-light-orange');
                        $this.addClass('bg-color-redLight');
                        return xhr.responseText;
                    }
                });
            }
        }
    }
    
    $('#cheque-issue-table').on('keypress','.input-paymentnumber,.input-batchnumber',function(e){
        if(e.keyCode == 13) {
            saveNumber($(this));
        }
    });
    
    $('#cheque-issue-table').on('keyup','.input-paymentnumber,.input-batchnumber',function(e){
        switch(e.keyCode)
        {
            case 8:  // Backspace
                //console.log('backspace');
            case 9:  // Tab
            case 13: // Enter
            case 37: // Left
            case 38: // Up
            case 39: // Right
            case 40: // Down
                break;
            default:
                if(this.getAttribute('data-pk') !== "0")
                {
                    if(this.value !== this.getAttribute('data-prev-value'))
                    {
                        $(this).removeClass('bg-color-redLight').removeClass('bg-color-light-green').addClass('bg-color-light-orange');   
                    }
                    else
                    {
                        $(this).removeClass('bg-color-redLight').removeClass('bg-color-light-orange').addClass('bg-color-light-green');
                    }
                }            
        }
    });
    
    

    //Hide Loading Message
    messenger_hidenotiftop();
})();