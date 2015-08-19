(window.admin_users = function() {
    $('a.ajax-login').on('click',function(e){
        e.preventDefault();
        var $this = $(this);
        Messenger({extraClasses:'messenger-on-top messenger-fixed'}).run({
            id: 'notif-top',
            errorMessage: 'Error: user cannot be logged in',
            successMessage: 'User has been logged in',
            progressMessage: 'Logging in user',
            action: $.ajax,
        },
        {
            type:'GET',
            url: $this.attr('href'),
            data: $this.parents('.cs-form').serialize(),
            success:function()
            {
                location.reload();
            },
            error:function(xhr, status, error)
            {
                return xhr.responseText;                
            }
        });        
    });
    messenger_hidenotiftop();    
})();