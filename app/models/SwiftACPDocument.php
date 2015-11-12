<?php
/*
 * Description: Accounts Payable - Document
 */

class SwiftACPDocument extends SwiftDocument {
    public function __construct(array $attributes = array())
    {
        $attributes = array('attachment_name'=>'document',
                            'attachment_config'=>array(
                                'storage' => 's3',
                                'url' => '/upload/:attachment/:id/:filename',
                                'default_url' => '/defaults/:style/missing.png',
                                'keep_old_files' => true,
                                'preserve_old_files' => true,
                                'path' => 'acpdocument/:id/:style/:filename',
                                'styles' => []
                                )
                            );
        parent::__construct($attributes);
    }
}