<?php
/*
 * Name: Swift PR
 * Description: Product Returns - Processing
 */

namespace Process;

class SwiftPR extends Process
{
    protected $resourceName = "SwiftPR";
    private $pdf;

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

    public function save($form_id)
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

    /*
     * Pickup PDF: Start
     */

    //Initialize Pdf Object
    private function initializePdf()
    {
        $this->pdf = new \Swift\PR\PrTcpdf('P', 'mm','A4', true, 'UTF-8', false);
        $this->pdf->SetCreator($this->controller->currentUser->first_name." ".$this->controller->currentUser->last_name);
        $this->pdf->SetAuthor('Scott Swift');
        $this->pdf->SetTitle('Pickup List');
        $this->pdf->SetSubject('Pickup List');
        $this->pdf->SetKeywords('Pickup, List, Product, Returns, System, Pdf');
        $this->pdf->SetMargins(20, 30, 20);
        $this->pdf->SetAutoPageBreak(TRUE, 120);
    }

    //Add Data by Form
    private function generateHTMLPdf()
    {
        $this->pdf->setFormData($this->form);
        $this->pdf->startPageGroup();
        $this->pdf->AddPage();
        
        //Product Info
        $this->pdfHTML .= "<table border=\"1\" cellpadding=\"1\">
                                <tr style=\"text-align:center;font-weight: bold;\">
                                    <th width=\"10%\">Qty</th>
                                    <th width=\"35%\">Description</th>
                                    <th width=\"20%\">Return Reason</th>
                                    <th width=\"15%\">Invoice No</th>
                                    <th width=\"10%\">Qty Picked Up</th>
                                    <th width=\"10%\">Qty Recd In Store</th>
                                </tr>";
        foreach($this->form->product as $product)
        {
            $this->pdfHTML.= "<tr style=\"text-align:center;vertical-align:middle;\" nobr=\"true\">
                                    <td width=\"10%\" height=\"20\">{$product->qty_client}</td>
                                    <td width=\"35%\" height=\"20\">{$product->name}</td>
                                    <td width=\"20%\" height=\"20\">{$product->reason_text}</td>
                                    <td width=\"15%\" height=\"20\">".($product->invoice_id == 0 ? "N/A" : $product->invoice_id)."</td>
                                    <td width=\"10%\" height=\"20\"></td>
                                    <td width=\"10%\" height=\"20\"></td>
                                </tr>";
        }
        
        $this->pdfHTML.= "</table>";
    }

    public function generatePdf($form_ids=array())
    {
        if(count($form_ids))
        {
            $this->initializePdf();
            $hasForms = false;
            $this->pdfHTML = "";
            foreach($form_ids as $id)
            {
                $this->form = $this->resource->with(['product'=>function($q){
                    return $q->where('pickup','=',\SwiftPRProduct::PICKUP);
                },'pickup'=>function($q){
                    return $q->has('driver')->orderBy('created_at','DESC');
                },'pickup.driver'])->has('pickup')
                ->find($id);
                if($this->form && count($this->form->product))
                {
                    $this->generateHTMLPdf();
                    $hasForms = true;
                }
            }
            if($hasForms)
            {
                $this->pdf->writeHTMLCell(0, 0, null, 90, $this->pdfHTML);
                return \Response::make($this->pdf->Output('pickup.pdf', 'I'));
            }
            else
            {
                return \Response::make("No valid form was found. Please make sure you have set the pickup & driver details.",500);
            }
        }
        else
        {
            return \Response::make("Please select a form",500);
        }
    }

    /*
     * Pickup PDF: End
     */
}
