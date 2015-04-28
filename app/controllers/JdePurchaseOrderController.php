<?php
class JdePurchaseOrderController extends UserController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageName = "JDE Purchase Order";
        $this->context = "jdepurchaseorder";
        $this->rootURL = $this->context = $this->data['context'] = $this->data['rootURL'] = "jde-purchase-order";
        $this->viewPermission = \Config::get("permission.{$this->context}.view");
    }

    public function getIndex()
    {
        
    }

    public function getView($id)
    {
        $po = \JdePurchaseOrder::with('item')->find($id);
        if($po)
        {
            $this->data['form'] = $po;
            $this->data['pageTitle'] = $po->name." - ".$po->supplier->name;
            if(\Request::ajax())
            {   
                echo \View::make('jdepurchaseorder/purchase-order-single',$this->data)->render();
                return;
            }
            else
            {
                return $this->makeView('jdepurchaseorder/view');
            }
        }
        else
        {
            if(\Request::ajax())
            {
                return \Response::make("Purchase order not found",404);
            }
            else
            {
                return parent::notfound();
            }
        }
    }

    public function getViewByForm($id)
    {
        $swiftPo = \SwiftPurchaseOrder::find(Crypt::decrypt($id));
        if($swiftPo)
        {
            if($swiftPo->validated === \SwiftPurchaseOrder::VALIDATION_FOUND)
            {
                $po = \JdePurchaseOrder::with('item')->find($swiftPo->order_id);
                if($po)
                {
                    $this->data['form'] = $po;
                    $this->data['pageTitle'] = $po->name." - ".$po->supplier->name;
                    if(\Request::ajax())
                    {
                        echo \View::make('jdepurchaseorder/purchase-order-single',$this->data)->render();
                        return;
                    }
                    else
                    {
                        return $this->makeView('jdepurchaseorder/view');
                    }
                }
                else
                {
                    if(\Request::ajax())
                    {
                        return \Response::make("Purchase order not found",404);
                    }
                    else
                    {
                        return parent::notfound();
                    }
                }
            }
            else
            {
                return \Response::make("Purchase Order not yet found",500);
            }
        }
        else
        {
            return parent::notfound();
        }
    }
}