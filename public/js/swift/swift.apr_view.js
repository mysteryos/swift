/* 
 * Name: A&P Request View Form
 */
(window.apr_view = function () {
    //General Info
    $('.editable:not(.dummy)').editable({
        disabled: true
    });

    //File View
    $('#apr-docs').on('click','a.file-view',function(e){
       e.preventDefault();
       vex.open({
           className: 'vex-theme-default vex-file-viewer',
           content:'<div class="row"><div class="col-xs-12 text-align-center">'+$(this).html()+'</div></div><iframe src="http://docs.google.com/viewer?url='+encodeURIComponent($(this).attr('href'))+'&embedded=true" class="file-viewer"></iframe>',
       }).height($(window).height()).width($(window).width()*0.9);

       return false;
    });

    $('#upload-preview #template').hide();
    $('#upload-preview').find('button.btn.delete').hide();

    //Hide Loading Message
    messenger_hidenotiftop(); 

});