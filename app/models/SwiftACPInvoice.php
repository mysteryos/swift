<?php

/**
 * Description: Account Payable - Invoices
 *
 * @author kpudaruth
 */

class SwiftACPInvoice extends Eloquent
{
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;
    
    protected $table = "scott_swift.swift_acp_invoice";
    
    protected $fillable = ['number','date','date_received','due_date','due_amount','currency_code','gl_code'];
    
    protected $dates = ['deleted_at','date','due_date','date_received'];

    protected $touches = array('acp');

    protected $appends = ['due_amount_formatted'];

    protected $attributes = [
        'currency_code' => 'MUR',
        'due_amount' => 0
    ];

    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array('date_received','date','due_date','due_amount','currency_code','gl_code');
    
    protected $revisionFormattedFieldNames = array(
        'id' => 'ID',
        'date_received' => 'Date Received',
        'date' => 'Invoice Date',
        'due_date' => 'Date Due',
        'due_amount' => 'Amount Due',
        'currency_code' => 'Currency',
        'gl_code' => 'GL Code'
    );
    
    public $saveCreateRevision = true;
    public $softDelete = true;
    public $revisionClassName =  "Invoice";
    public $revisionPrimaryIdentifier = "id";
    
    /* Elastic Search */
    
    //Indexing Enabled
    public $esEnabled = true;
    //Context for Indexing
    public $esContext = "acpayable";
    //Info Context
    public $esInfoContext = "invoice";
    public $esRemove = ["acp_id","due_amount_formatted"];

    public function esGetParent()
    {
        return $this->acp;
    }
    
    /*
     * Event Observers
     */
    
    public static function boot()
    {
        parent:: boot();
        
        static::bootElasticSearchEvent();
        
        static::bootRevisionable();

        static::creating(function($model){
            if($model->date_received === null)
            {
                $model->date_received = date('Y-m-d');
            }
        });
    }    
    
    /*
     * Accessors
     */

    public function getCurrencyCodeRevisionAttribute($val)
    {
        $currency = Currency::find($val);
        if($currency)
        {
            return $currency->fullname;
        }

        return "(N/A)";
    }

    public function getCurrencyCodeEsAttribute($val)
    {
        return $this->getCurrencyCodeRevisionAttribute($val);
    }

    public function getDueAmountFormattedAttribute()
    {
        if($this->currency)
        {
            return $this->currency->code." ".number_format($this->due_amount);
        }

        return number_format($this->due_amount);
    }
    
    /*
     * Scope
     */
    
    /*
     * Relationships
     */

    public function acp()
    {
        return $this->belongsTo('SwiftACPRequest','acp_id');
    }
    
    public function currency()
    {
        return $this->belongsTo('Currency','currency_code');
    }

    /*
     * Query
     */
    
}
