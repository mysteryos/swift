<?php
/*
 * Name: Swift PR
 * Description: Product Returns - Processing
 */

namespace Process;

class SwiftPR extends Process
{
    protected $resourceName = "SwiftPR";

    public function __construct($controller)
    {
        parent::__construct($controller);
    }
    
    public function create($type)
    {
        $this->form = new $this->resourceName([
            'type' => $type,
            'customer_code' => \Input::get('customer_code'),
            'owner_user_id' => $this->controller->currentUser->id
        ]);

        if($this->form->save())
        {
            if(\WorkflowActivity::update($this->form,$this->controller->context))
            {
                //Story Relate
                \Queue::push('Story@relateTask',array('obj_class'=>get_class($this->form),
                                                     'obj_id'=>$this->form->id,
                                                     'action'=>\SwiftStory::ACTION_CREATE,
                                                     'user_id'=>$this->controller->currentUser->id,
                                                     'context'=>get_class($this->form)));
                //Success
                return \Response::make(json_encode(['success'=>1,'url'=>\Helper::generateUrl($this->form)]));
            }
            else
            {
                return \Response::make("Failed to save workflow",400);
            }
        }
        else
        {
            return \Response::make("Save unsuccessful",500);
        }
    }

    public function createInvoiceCancelled()
    {
        if(\Input::has('invoice_code'))
        {
            $invoiceCode = \Input::get('invoice_code');
            $lines = \JdeSales::getProducts((int)$invoiceCode);
            if(count($lines))
            {
                $invoiceCancelledId = \SwiftPRReason::getInvoiceCancelledScottId();

                /*Check if form already exists*/

                $formExist = $this->resource->where('customer_code','=',$lines->first()->AN8)
                            ->whereHas('workflow',function($q){
                                //Status = Inprogress or Complete
                                return $q->where('status','!=',\SwiftWorkflowActivity::REJECTED);
                            })
                            ->whereHas('product',function($q) use ($invoiceCancelledId,$invoiceCode){
                                return $q->where('reason_id','=',$invoiceCancelledId)
                                         ->where('invoice_id','=',$invoiceCode,'AND');
                            })->get();

                if(count($formExist) > 0 )
                {
                    return \Response::make("Invoice already cancelled, <a href='".\Helper::generateURL($formExist->first())."' class='pjax'>Click here to view form</a>",500);
                }

                \Queue::push('Helper@saveInvoiceCancelled',
                            ['invoice_id'=>$invoiceCode,
                             'user_id'=>$this->controller->currentUser->id,
                             'context'=>$this->controller->context]);

                return \Response::make("Invoice with Number: ".$invoiceCode." is being cancelled.");

            }
            else
            {
                return \Response::make("Invoice not found",500);
            }
        }
        else
        {
            return \Response::make("Please input an invoice code");
        }
    }

    public function save()
    {
        $this->setForm(\Crypt::decrypt($form_id));
        if($this->form)
        {
            //If owner or is an Admin
            if($this->form->isOwner() || $this->controller->isAdmin)
            {
                switch(\Input::get('name'))
                {
                    case 'type':
                        if(!$this->controller->currentUser->isSuperUser())
                        {
                            return $this->controller->forbidden();
                        }
                    case 'customer_code':
                        if(\Input::get('value') === "" || !is_numeric(\Input::get('value')))
                        {
                            return \Response::make('Please select a valid customer',500);
                        }
                        else
                        {
                            if(!\JdeCustomer::find(\Input::get('value')))
                            {
                                return \Response::make('Please select an existing customer',500);
                            }
                        }
                    case 'paper_number':
                    case 'description':
                        break;
                    default:
                        return \Response::make("Unknown Field",500);
                        break;
                }

                return $this->processPut();
            }
            else
            {
                return $this->controller->forbidden();
            }
        }

        return \Response::make("Form not found",500);
    }
}
