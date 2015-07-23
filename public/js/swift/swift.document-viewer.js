/* 
 * Name: Document-Viewer
 */
$(function(){
    var $noDoc = $('#no-doc');
    var $docBrowser = $('#doc-browser');
    var $doc_container = $('#doc-container');
    
    $docBrowser.on('click','li',function(e){
        var $this = $(this);
        if(!$this.hasClass('doc-selected'))
        {
            $this.parents('ul').find('li').removeClass('doc-selected');
            $this.addClass('doc-selected');
            
            var $url = $this.attr('data-href');
            if($doc_container.find('iframe').length)
            {
                if($doc_container.find('iframe').attr('src') !== $url)
                {
                    $doc_container.find('iframe').attr('src',$url);
                }
                return;
            }

            $doc_container.append($iframe.attr('src',$url));
        }
    });      
});