$('#share_container').on('click','#btn-cancel',function(){
    $(window).colorbox.close();
    return false;
});

$('#share_container').on('click','#btn-send',function(){
    var $this = $(this);
    
    if(document.getElementById('select_share_user_id').selectedIndex === 0)
    {
        alert('Please select a user first.');
        return false;
    }
    
    $this.attr('disabled','disabled');
    
    Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
        id: 'notif-top',
        errorMessage: 'Error: sharing unsuccessful',
        successMessage: 'Sharing complete',
        progressMessage: 'Please Wait...',
        action: $.ajax,
    },
    {
        type:'POST',
        url: $this.attr('href'),
        data: $('#share_form').serialize(),
        success:function()
        {
            $(window).colorbox.close();
        },
        error:function(xhr, status, error)
        {
            $this.removeAttr('disabled','disabled');
            return xhr.responseText;
        }
    });
    
    return false;
});

$('#share_container').on('click','.btn-delete-share',function(e){
    e.preventDefault();
    var $this = $(this);
    
    if(confirm('Are you sure you wish to delete this record?'))
    {
        Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
            id: 'notif-top',
            errorMessage: 'Error: record not deleted',
            successMessage: 'Record has been deleted',
            progressMessage: 'Please Wait...',
            action: $.ajax,
        },
        {
            type:'POST',
            url: $this.attr('href'),
            success:function()
            {
                $this.parents('.share-row').remove();
            },
            error:function(xhr, status, error)
            {
                return xhr.responseText;
            }
        });
    }
    return false;
});
