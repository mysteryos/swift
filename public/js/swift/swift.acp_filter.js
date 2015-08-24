/*
 * Filter Menu
 */
function acp_filter()
{
    var $filter_options = $('#filter-options');
    var $filterBtnDatePicker = $filter_options.find('.datepicker');
    $filterBtnDatePicker.datepicker({
        dateFormat : 'dd/mm/yy',
        prevText : '<i class="fa fa-chevron-left"></i>',
        nextText : '<i class="fa fa-chevron-right"></i>',            
    });
    
    /*
     * Clean previous instances of select2
     */
    $('.select2-container').remove();

    if(document.getElementById('select_filter_supplier'))
    {
        $('#select_filter_supplier').val('');
        $('#select_filter_supplier').select2({
            placeholder: 'Please select a supplier',
        });
    }
    
    if(document.getElementById('select_filter_billable_company_code'))
    {
        $('#select_filter_billable_company_code').val('');
        $('#select_filter_billable_company_code').select2({
            placeholder: 'Please select a billable company'
        });
    }
    
    if(document.getElementById('select_filter_step'))
    {
        $('#select_filter_step').val('');
        $('#select_filter_step').select2({
            placeholder: 'Please select a step'
        });        
    }

    $('#filter-btn').on('click',function(){

        if($filter_options.is(':visible'))
        {
            //Hide form
            $filter_options.slideUp(300);
            $filter_options.find('.datepicker').datepicker('hide');
            $('#select_filter_supplier').select2('close');
        }
        else
        {
            //Show
            $filter_options.slideDown(300);
        }
    });

    $('#form-filter-options').on('submit',function(e){
        $.pjax.submit(e,'#main');
        return false;
    });
}