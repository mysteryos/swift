<?php
/* 
 * Name: Swift Node Activity Join
 */

class SwiftNodeDefinitionJoin extends Eloquent {
    
    protected $table = "swift_node_definition_join";
    
    protected $guarded = array('id');
    
    protected $fillable = array('children_id','parent_id','php_function','pattern');
    
    public $timestamps = false;
    
    /*
     * Workflow Patterns
     * Documentation: http://zetacomponents.org/documentation/trunk/Workflow/theoretical_background.html
     */
    
    /*
     * Name: Sequence
     * Use Case Example: After an order is placed, the credit card specified by the customer is charged.
     * TLDR: 1 to 1 connection. After A is complete, it moves to B.
     */
    public static $P_SEQUENCE = 1;
    
    /*
     * Name: Parallel Split (AND-Spilt)
     * Use Case Example: After the credit card specified by the customer has been successfully charged, the activities of sending a confirmation email and starting the shipping process can be executed in parallel.
     * TLDR: After A is complete, it moves to B, C and D.
     */
    public static $P_AND_SPLIT = 2;
    
    /*
     * Name: Synchronization (AND-Join)
     * Use Case Example: After the confirmation email has been sent and the shipping process has been completed, the order can be archived.
     * TLDR: Many Nodes to 1 'AND' Connection. After A is complete, they move to B,C, & D
     */
    public static $P_AND_JOIN = 3;

    /*
     * Name: Multiple Choice (OR-Split)
     * TLDR: After either B, C or D is complete, workflow can moves to E
     */
    public static $P_OR_SPLIT = 6;

    /*
     * Name: Synchronizing Merge (OR-Join)
     * TLDR: After B and/or C has completed, it moves to E; Ignores conditions while branching
     */
    public static $P_OR_JOIN = 7;

    /*
     * Name: Exclusive Choice (XOR-Split)
     * Use Case Example: After an order has been received, the payment can be performed by either credit card or bank transfer.
     * TLDR: After B, C or D is complete, workflow moves to E. Checks conditions before doing branching
     */
    public static $P_XOR_SPLIT = 4;
    
    /*
     * Name: Simple Merge (XOR-Join)
     * Use Case Example: After the payment has been performed by either credit card or bank transfer, the order can be processed further.
     * TLDR: After E has been completed, workflow moves to either B, C or D
     */
    public static $P_XOR_JOIN = 5;
    
    /*
     * Name: Exclusive Compulsory Choice (XAND-Split)
     * TLDR: Same as AND but with variable number of nodes
     */
    public static $P_XAND_SPLIT = 8;
    
    /*
     * Name: Simple Merge (XAND-Join)
     * TLDR: Same as AND but with variable number of nodes
     */
    public static $P_XAND_JOIN = 9;
    
    
    public function childNode()
    {
        return $this->hasOne('SwiftNodeDefinition','id','children_id');
    }
    
    public function parentNode()
    {
        return $this->hasOne('SwiftNodeDefinition','id','parent_id');
    }
    
    public static function getByParent($parent_id)
    {
        return self::where('parent_id','=',$parent_id)->with('childNode','parentNode')->get();
    }
    
    public static function getByChild($children_id)
    {
        return self::where('children_id','=',$children_id)->with('childNode','parentNode')->get();
    }
}

