<?php
/*
 * Name:
 * Description:
 */

class SwiftScanInvoice extends Eloquent {
    
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = "swift_scan_invoice";
    
    protected $guarded = array('id');
    
    protected $fillable = array('business_unit','invoice_no');
    
    public $timestamps = true;
    
    protected $dates = ['deleted_at'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'business_unit', 'invoice_no'
    );
    
    protected $revisionFormattedFieldNames = array(
        'invoice_no' => 'Invoice Number',
    );    
    
    public $revisionClassName = "Scanned Invoice";
    public $revisionPrimaryIdentifier = "invoice_no";    
    public $keepCreateRevision = true;
    public $softDelete = true;    
}