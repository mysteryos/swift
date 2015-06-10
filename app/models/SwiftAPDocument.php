<?php
/*
 * Description: A&P Request - Document
 */

class SwiftAPDocument extends SwiftDocument {
    public function __construct(array $attributes = array())
    {
        $attributes = array('attachment_name'=>'document',
                            'attachment_config'=>array(
                                'storage' => 's3',
                                'url' => '/upload/:attachment/:id/:filename',
                                'default_url' => '/defaults/:style/missing.png',
                                'keep_old_files' => true,
                                'preserve_old_files' => true,
                                'path' => 'apdocument/:id/:style/:filename'                                
                                )
                            );        
        parent::__construct($attributes);
    }    
}