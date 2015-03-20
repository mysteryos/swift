<?php
class JdePurchaseOrderController extends UserController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageName = "JDE Purchase Order";
        $this->context = "jdepurchaseorder";
        $this->rootURL = $this->data['rootURL'] = "jde-purchase-order";
    }

    public function getIndex()
    {
        \Artisan::call('jdetablefix:start');
    }
}