/*
 * Name: Order Tracking View Form
 */
(window.ot_view = function () {
    //General Info
    $('.editable:not(.dummy)').editable({
        disabled: true
    });

    //File View
    $('a.file-view').on('click',function(e){
        e.preventDefault();
        var $this = $(this);
        //For images
        if($this.attr('href').indexOf('.jpg') !== -1 || $this.attr('href').indexOf('.jpeg') !== -1 || $this.attr('href').indexOf('.png') !== -1 || $this.attr('href').indexOf('.bmp') !== -1)
        {
            $.colorbox({
                href: $this.attr('href'),
                maxHeight:"100%",
                maxWidth:"90%",
                innerWidth:"100%",
                innerHeight:"100%",
                initialWidth:"64px",
                initialHeight:"84px",
                closeButton:true,
                iframe: false,
            });
        }
        else
        {
            //For Docs
            $.colorbox({
                href: "http://docs.google.com/viewer?url="+encodeURIComponent($this.attr('href'))+"&embedded=true",
                maxHeight:"100%",
                maxWidth:"90%",
                innerWidth:"100%",
                innerHeight:"100%",
                initialWidth:"64px",
                initialHeight:"84px",
                closeButton:true,
                iframe: true,
            });
        }
    });

    $('#upload-preview #template').hide();
    $('#upload-preview').find('button.btn.delete').hide();

    $('#acp-list').on('click','tr[data-url] td',function(){
        $.pjax({
            url: $(this).parent('tr').attr('data-url'),
            container: '#main'
       });
    });

    //Enable Commenting
    enableComments();

    //Bind pusher channel
    pusherSubscribeCurrentPresenceChannel(true,true);

    //Hide Loading Message
    messenger_hidenotiftop();

})();