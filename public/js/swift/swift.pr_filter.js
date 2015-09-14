function pr_filter()
{
    var $filter_options = $('#filter-options');
    var $filterBtnDatePicker = $filter_options.find('.datepicker');
    $filterBtnDatePicker.datepicker({
        dateFormat : 'dd/mm/yy',
        prevText : '<i class="fa fa-chevron-left"></i>',
        nextText : '<i class="fa fa-chevron-right"></i>',            
    });
    
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
    
    if(document.getElementById('select_filter_customer_code'))
    {
        $('#select_filter_customer_code').val('');
        $('#select_filter_customer_code').select2({
            placeholder: 'Please select a customer',
        });
    }
    
    if(document.getElementById('select_filter_owner_user_id'))
    {
        $('#select_filter_owner_user_id').val('');
        $('#select_filter_owner_user_id').select2({
            placeholder: 'Please select a user',
        });
    }
    
    
    if(document.getElementById('select_filter_driver_id'))
    {
        $('#select_filter_driver_id').val('');
        $('#select_filter_driver_id').select2({
            placeholder: 'Please select a driver',
        });
    }
    
    if(document.getElementById('select_filter_step'))
    {
        $('#select_filter_step').val('');
        $('#select_filter_step').select2({
            placeholder: 'Please select a step',
        });
    }
    
}