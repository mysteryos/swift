<?php
/*
 * Description: Accounts Payable - Document
 */

class SwiftACPTempDocument extends SwiftDocument {
    public function __construct(array $attributes = array())
    {
        $attributes = array('attachment_name'=>'document',
                            'attachment_config'=>array(
                                'storage' => 's3',
                                'url' => '/upload/:attachment/:id/:filename',
                                'default_url' => '/defaults/:style/missing.png',
                                'keep_old_files' => true,
                                'preserve_old_files' => true,
                                'path' => 'acptempdocument/:id/:style/:filename',
                                'styles' => []
                                )
                            );
        parent::__construct($attributes);
    }

    public static function getAll()
    {
        return self::where('document_type','=','SwiftACPTempDocument')
                ->orderBy('created_at','ASC')
                ->get();
    }
}