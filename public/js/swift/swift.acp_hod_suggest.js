$('#acp_hod_suggest_container').on('click','#btn-cancel',function(){
    $('#acp_hod_suggest_select').select2('destroy');
    $('#pv-process-info').find('a.btn-suggest.is_active').removeClass('is_active');
    $(window).colorbox.close();
    return false;
});

$('#acp_hod_suggest_select').select2({
    multiple: true,
    query: function (query){
        var data = {results: []};

        $.each($.parseJSON(document.getElementById('acp_hod_suggest_user_list').value), function(){
            if(query.term.length === 0 || this.name.toLowerCase().indexOf(query.term.toLowerCase()) >= 0 ){
                data.results.push({id: this.id, text: this.name });
            }
        });

        query.callback(data);
    }    
}).on('select2-open',function(){
    $('#select2-drop').css({
        'zIndex': '10001'
    });
    
    $('#select2-drop-mask')
    .height($(window).height())
    .width($(window).width())
    .css({
        'opacity' : '.1',
        'position': 'absolute',
        'top': '0',
        'left': '0',
        'zIndex': '10000'
    });
}).on('change',function(){
    $.fn.colorbox.resize({});
});

$('#acp_hod_suggestion_form').on('click','#btn-send',function(){
    var $this = $(this);
    
    if($('#acp_hod_suggest_select').val() === "")
    {
        alert('Please select a user first.');
        return false;
    }
    
    $this.attr('disabled','disabled');
    
    Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
        id: 'notif-top',
        errorMessage: 'Error: suggestion unsuccessful',
        successMessage: 'Suggestion complete, thanks for your input',
        progressMessage: 'Please Wait...',
        action: $.ajax,
    },
    {
        type:'POST',
        url: $this.attr('href'),
        data: $('#acp_hod_suggestion_form').serialize(),
        success:function()
        {
            $('#acp_hod_suggest_select').select2('destroy');
            var activeBtn = $('#pv-process-info').find('a.btn-suggest.is_active');
            activeBtn.parents('.pv-row').find('a.btn').addClass('disabled').attr('disabled','disabled');
            activeBtn.parents('.pv-row').next().find('.pv-form').trigger('click');
            activeBtn.removeClass('is_active').addClass('btn-primary');
            $(window).colorbox.close();
            
        },
        error:function(xhr, status, error)
        {
            $this.removeAttr('disabled','disabled');
            $('#pv-process-info').find('a.btn-suggest.is_active').removeClass('is_active');
            return xhr.responseText;
        }
    });
    
    return false;
});