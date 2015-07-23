/* 
 * Name: ACP Assign Exec Cheque Issue
 * Description:
 */

(window.acp_cheque_assign_exec = function(){
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
    $("#inbox-table [rel=tooltip]").tooltip();

    $("#inbox-table input[type='checkbox']").change(function(e) {
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
    
    $content.on('click','.btn-tick-no-exec',function(){
        $content.find('tr.pvform').removeClass('highlight')
                .find('input[type="checkbox"]').prop("checked",false);
        var $pvlist = $content.find('.input-exec').filter(function(){
            return this.selectedIndex === 0;
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
    
    context.attach('tr.pvform',
    [
        {
            text: 'Set Executive',
            action: function(e,obj)
            {
                $('.dropdown-context').css({display:''}).find('.drop-left').removeClass('drop-left');
                $('#assignExecModal').modal({
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
    
    $content.find('tr.pvform').on('contextmenu',function(){
        var $this = $(this);
        if(!$this.hasClass('highlight'))
        {
            $this.addClass('highlight');
            $this.find('input[type="checkbox"]').prop('checked',true);
            toggleHighlightBar();
        }
    });

    $('#btn-setpublish').on('click',function(){
        publishForm();
        return false;
    });
    
    $('#btn-setbatchexec').on('click',function(){
        $('#assignExecModal').modal({
            backdrop: false
        });
        return false;
    });
    
    $('#inbox-table').on('click','a.btn-single-publish',function(e){
        e.preventDefault();
        savePublish($(this));
        return false;
    });
    
    function setbatchexec()
    {
        $('tr.pvform.highlight').find('.input-exec').val($('#batchSelectExec').val());
        $('tr.pvform.highlight').find('.input-exec').each(function(){
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
                $this.removeClass('disabled');
                $this.removeAttr('disabled');                
            },
            error: function(xhr) {
                $this.removeClass('btn-default');
                $this.removeClass('btn-success');
                $this.addClass('btn-danger');
                $.smallBox({
                        title : "Form failed to publish: "+xhr.responseText,
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
    
    $('#inbox-table').on('change','.input-exec',function(e){
        saveInput($(this));
    });
    
    /*
     * Batch Cheque Signator
     */
    
    $('#assignExecModal').on('shown.bs.modal', function(){
        //Reset Inputs
        $('#batchSelectExec')[0].selectedIndex = 0;       
    });
    
    $('#btn-saveExec').on('click',function(){
       if($('#batchSelectExec')[0].selectedIndex === 0)
       {
           alert('Please select a user');
           return;
       }
       
       setbatchexec();
       $('#assignExecModal').modal('hide');
       return false;
    });

    //Hide Loading Message
    messenger_hidenotiftop();
})();