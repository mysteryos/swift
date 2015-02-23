<?php

class SwiftStoreDriver extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;    
    
    protected $table = "swift_store_driver";
    
    protected $fillable = ["name"];
    
    protected $dates = ['deleted_at'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'name'
    );
    
    protected $revisionFormattedFieldNames = array(
        'name' => 'Name',
    );
    
    public $revisionClassName = "Store Driver";
    public $revisionPrimaryIdentifier = "name";
    public $keepCreateRevision = true;
    public $softDelete = true;
    
    
}