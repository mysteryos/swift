/*
 * Name: ACP Payment Issue
 * Description:
 */

(window.acp_payment_issue = function(){
    tableHeightSize();
    function toggleHighlightBar()
    {
        $.contentdiv.find('.pvform.highlight').length > 0 ? $('div.toggle-oncheck').show() : $('div.toggle-oncheck').hide();
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
    $("#inbox-table [rel=tooltip]").tooltip();

    $.contentdiv.on('change',"#inbox-table input[type='checkbox']",function(e) {
           $(this).closest('tr').toggleClass("highlight", this.checked);
           toggleHighlightBar();
    });

    /*
     * Tick Menu
     */

    $.contentdiv.on('click','.btn-tick-all',function(){
        $.contentdiv.find('tr.pvform').addClass('highlight')
                .find('input[type="checkbox"]').prop("checked",true);
        toggleHighlightBar();
    });

    $.contentdiv.on('click','.btn-tick-clear',function(){
        $.contentdiv.find('tr.pvform').removeClass('highlight')
                .find('input[type="checkbox"]').prop("checked",false);
        toggleHighlightBar();
    });

//    $.contentdiv.on('click','.btn-tick-nobatchnumber',function(){
//        $.contentdiv.find('tr.pvform').removeClass('highlight')
//                .find('input[type="checkbox"]').prop("checked",false);
//        var $pvlist = $.contentdiv.find('.input-batchnumber').filter(function(){
//            return this.value === "";
//        });
//        $pvlist.parents('tr.pvform').addClass('highlight')
//                .find('input[type="checkbox"]').prop("checked",true);
//        toggleHighlightBar();
//    });

    $.contentdiv.on('click','.btn-tick-nopvnumber',function(){
        $.contentdiv.find('tr.pvform').removeClass('highlight')
                .find('input[type="checkbox"]').prop("checked",false);
        var $pvlist = $.contentdiv.find('.input-paymentnumber').filter(function(){
            return this.value === "";
        });
        $pvlist.parents('tr.pvform').addClass('highlight')
                .find('input[type="checkbox"]').prop("checked",true);
        toggleHighlightBar();
    });

    /*
     * Activate Filter
     */

    acp_filter();

    //Reset table
    $('#inbox-table select.form-control, #inbox-table input.form-control').removeClass('bg-color-light-green');

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

    context.attach('tr.pvform',
    [
        {
            text: 'Set Payment Number',
            action: function(e,obj)
            {
                $('.dropdown-context').css({display:''}).find('.drop-left').removeClass('drop-left');
                setpaymentnum();
            }
        },
        {
            text: 'Set Cheque Signator',
            action: function(e,obj)
            {
                $('.dropdown-context').css({display:''}).find('.drop-left').removeClass('drop-left');
                $('#chequeSignatorModal').modal({
                    backdrop: false
                });
            }
        },
        {
            text: 'Set Payment Type',
            action: function(e,obj)
            {
                $('.dropdown-context').css({display:''}).find('.drop-left').removeClass('drop-left');
                $('#paymentTypeModal').modal({
                    backdrop: false
                });
            }
        },
        {
            text: 'Publish Form',
            action: function(e,obj)
            {
                $('.dropdown-context').css({display:''}).find('.drop-left').removeClass('drop-left');
                publishForm();
            }
        }
    ]
    );

    $.contentdiv.on('contextmenu','tr.pvform',function(){
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

    $('#btn-setpublish').on('click',function(){
        publishForm();
        return false;
    });

    $('#btn-setbatchchequesignator').on('click',function(){
        $('#chequeSignatorModal').modal({
            backdrop: false
        });
        return false;
    });

    $('#btn-setpaymenttype').on('click',function(){
        $('#paymentTypeModal').modal({
            backdrop: false
        });
        return false;
    });

    $.contentdiv.on('click','a.btn-single-publish',function(e){
        e.preventDefault();
        savePublish($(this));
        return false;
    });

    function setpaymentnum()
    {
        var paymentnum = prompt('Set payment number for '+$('.pvform.highlight').length+($('.pvform.highlight').length === 1 ? ' form' : ' forms'));
        if($.isNumeric(paymentnum))
        {
            $('tr.pvform.highlight').find('input.input-paymentnumber').val(paymentnum);
            $('tr.pvform.highlight').find('input.input-paymentnumber').each(function(){
                saveInput($(this));
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
                saveInput($(this));
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

    function setbatchchequesignator()
    {
        $('tr.pvform.highlight').find('.input-cheque-signator-id').val($('#batchSelectChequeSignator').val());
        $('tr.pvform.highlight').find('.input-cheque-signator-id').each(function(){
            saveInput($(this));
        });
    }

    function setbatchtype()
    {
        $('tr.pvform.highlight').find('.input-payment-type').val($('#batchSelectType').val());
        $('tr.pvform.highlight').find('.input-payment-type').each(function(){
            saveInput($(this));
        });
    }

    function publishForm()
    {
        if(confirm('Do you want to publish these form(s)?'))
        {
            messenger_notiftop('Publishing Forms',5);
            $('tr.pvform.highlight').find('a.btn-single-publish').each(function(){
                savePublish($(this));
            });
        }
    }

    function savePublish($this)
    {
        $this.attr('disabled','disabled');
        $this.addClass('disabled');
        $.ajaxq("pvform"+$this.parents('.pv-form').attr('data-id'), {
            url: $this.attr('href'),
            type: 'POST',
            timeout: 10000,
            success:function(){
                $this.removeClass('btn-default');
                $this.removeClass('btn-danger');
                $this.addClass('btn-success');
                $.smallBox({
                        title : "Form published",
                        content : "Success",
                        color : "#26A65B",
                        icon : "fa fa-check",
                        timeout: 2000
                });
            },
            error: function(xhr,textStatus) {
                $this.removeClass('btn-default');
                $this.removeClass('btn-success');
                $this.addClass('btn-danger');
                $.smallBox({
                        title : "Form failed to publish: "+ (textStatus === "timeout" ? "Internet connection timed-out" : xhr.responseText),
                        content : "Look for the red tick button",
                        color : "#a65858",
                        icon : "fa fa-times",
                        timeout: 3500
                });
                $this.removeClass('disabled');
                $this.removeAttr('disabled');
            }
        });
    }

    function saveInput($this)
    {
        if($this.length)
        {
            if($this.val() !== $this.attr('data-prev-value'))
            {
                $.ajaxq("pvform"+$this.parents('.pv-form').attr('data-id'),{
                    type:'PUT',
                    url: $this.attr('data-url'),
                    data: {"pk":$this.attr('data-pk'),"value":$this.val()},
                    timeout: 5000,
                    success:function(result)
                    {
                        if($this.attr('data-pk') === "0")
                        {
                            $this.parents('.pvform').find('.input-with-pk').attr('data-pk',result.encrypted_id);
                        }
                        $this.attr("data-prev-value",$this.val());
                        $this.removeClass('bg-color-redLight');
                        $this.removeClass('bg-color-light-orange');
                        $this.addClass('bg-color-light-green');
                    },
                    error:function(xhr, textStatus, error)
                    {
                        $this.removeClass('bg-color-light-green');
                        $this.removeClass('bg-color-light-orange');
                        $this.addClass('bg-color-redLight');
                        messenger_notiftop((textStatus === "timeout" ? "Internet connection timed-out" : xhr.responseText),'error');
                    }
                });
            }
        }
    }

    $.contentdiv.on('change','.input-cheque-signator-id,.input-payment-type',function(e){
        saveInput($(this));
    });

    $.contentdiv.on('keyup','.input-paymentnumber',function(e){
        switch(e.keyCode)
        {
            case 38: // Up
                var $prev = $(this).parents('tr.pvform').prev();
                if($prev.length)
                {
                    if($(this).hasClass('input-paymentnumber'))
                    {
                        $prev.find('.input-paymentnumber').first().focus();
                    }
                    else if($(this).hasClass('input-batchnumber'))
                    {
                        $prev.find('.input-batchnumber').first().focus();
                    }
                }
                break;
            case 40: // Down
                var $next = $(this).parents('tr.pvform').next();
                if($next.length)
                {
                    if($(this).hasClass('input-paymentnumber'))
                    {
                        $next.find('.input-paymentnumber').first().focus();
                    }
                    else if($(this).hasClass('input-batchnumber'))
                    {
                        $next.find('.input-batchnumber').first().focus();
                    }
                }
                break;
            case 9:  // Tab
            case 13: // Enter
            case 37: // Left
            case 39: // Right
                break;
            case 8:
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

                var $this = $(this);
                window.clearTimeout($this.data('timer'));
                var wait = window.setTimeout(function(){
                    saveInput($this);
                }, 1000);
                $this.data('timer', wait);
        }
    });

    /*
     * Batch Cheque Signator
     */

    $('#chequeSignatorModal').on('shown.bs.modal', function(){
        //Reset Inputs
        $('#batchSelectChequeSignator')[0].selectedIndex = 0;
    });

    $('#btn-saveChequeSignator').on('click',function(){
       if($('#batchSelectChequeSignator')[0].selectedIndex === 0)
       {
           alert('Please select a user');
           return;
       }

       setbatchchequesignator();
       $('#chequeSignatorModal').modal('hide');
       return false;
    });

    /*
     * Batch Payment Type
     */

    $('#paymentTypeModal').on('shown.bs.modal', function(){
        //Reset Inputs
        $('#batchSelectType')[0].selectedIndex = 0;
    });

    $('#btn-saveType').on('click',function(){
       if($('#batchSelectType')[0].selectedIndex === 0)
       {
           alert('Please select a type');
           return;
       }

       setbatchtype();
       $('#paymentTypeModal').modal('hide');
        return false;
    });

    //Hide Loading Message
    messenger_hidenotiftop();
})();