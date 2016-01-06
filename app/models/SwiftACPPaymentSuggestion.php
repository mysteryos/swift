<?php
/**
 * Created by PhpStorm.
 * User: kpudaruth
 * Date: 16/12/2015
 * Time: 13:52
 */

class SwiftACPPaymentSuggestion extends \Eloquent
{
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;

    public $readableName = "Accounts Payable";

    protected $table = "scott_swift.swift_acp_payment_suggestion";

    protected $fillable = ['acp_id','type','amount'];

    public $dates = ['deleted_at'];

    /* Revisionable */

    protected $revisionEnabled = true;

    protected $keepRevisionOf = array('type','amount');

    protected $revisionFormattedFieldNames = array(
        'type' => 'Suggested Type',
        'amount' => 'Suggested Amount',
        'id' => 'ID'
    );

    public $saveCreateRevision = true;
    public $softDelete = true;
    public $revisionClassName =  "Accounts Payable Payment Suggestion";
    public $revisionPrimaryIdentifier = "id";

    /*
     * Event Observers
     */

    public static function boot() {
        parent:: boot();
        static::bootRevisionable();
    }

    /*
     * Relationships
     */

    public function acp()
    {
        return $this->belongsTo('SwiftACPRequest','acp_id');
    }

    /*
     * Accessors
     */

    public function getTypeRevisionAttribute($val)
    {
        if(array_key_exists($val,\SwiftACPPAyment::$type))
        {
            return \SwiftACPPAyment::$type[$val];
        }
        else
        {
            return "";
        }
    }
}