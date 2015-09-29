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

    public function validateCreate()
    {
        $validator = \Validator::make(\Input::all(),[
            'customer_code' => 'required|numeric|exists:sct_jde.jdecustomers,AN8',
            'type' => 'required|in:'.\SwiftPR::SALESMAN.','.\SwiftPR::ON_DELIVERY,
        ]);

        //When on delivery, additional rules
        $validator->sometimes('paper_number','required|numeric',function($input){
            return (int)$input->type === \SwiftPR::ON_DELIVERY && (bool)$input->publish === true;
        });

        $validator->sometimes('driver_id','required|numeric|exists:swift_driver,id',function($input){
            return (int)$input->type === \SwiftPR::ON_DELIVERY && (bool)$input->publish === true;
        });

        return $validator;
    }

    /*
     * Check permission for Create action
     * @return mixed
     */
    public function createPermissionCheck()
    {
        $permissionClass = new \Permission\SwiftPR();
        switch((int)\Input::get('type'))
        {
            case \SwiftPR::SALESMAN:
                if(!$permissionClass->canCreateSalesman())
                {
                    return \Response::make("You don't have the permission to access this resource.",500);
                }
                break;
            case \SwiftPR::ON_DELIVERY:
                if(!$permissionClass->canCreateOnDelivery())
                {
                    return \Response::make("You don't have the permission to access this resource.",500);
                }
                break;
        }
        return true;
    }

    /*
     * Create action
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //Validate the form's basic data
        $validator = $this->validateCreate();
        if((bool)\Input::get('publish') && $validator->fails())
        {
            return \Response::make($validator->messages(),500);
        }

        //Check permissions
        $permissionCheck = $this->createPermissionCheck();
        if($permissionCheck !== true)
        {
            return $permissionCheck;
        }

        //Start transaction for saving
        \DB::beginTransaction();
        $this->form = new $this->resourceName([
            'type' => \Input::get('type'),
            'customer_code' => \Input::get('customer_code'),
            'owner_user_id' => $this->controller->currentUser->id
        ]);

        //On Delivery needs paper number
        if((int)\Input::get('type') === \SwiftPR::ON_DELIVERY)
        {
            $this->form->paper_number = \Input::get('paper_number');
        }

        if($this->form->save())
        {
            if(!\Input::has('product') && \Input::get('publish'))
            {
                return \Response::make('Please add a product',500);
            }
            foreach(\Input::get('product') as $product)
            {
                if(\Input::get('publish'))
                {
                    //Validate it
                    $validator = \Validator::make($product,[
                        'jde_itm' => 'required|numeric|exists:sct_jde.jdeproducts,ITM',
                        'qty_client' => 'required|numeric|min:1',
                        'reason_id' => 'required|numeric|exists:swift_pr_reason,id',
                        'pickup' => 'numeric|in:0,1'
                    ]);
                    if($validator->fails())
                    {
                        \Db::rollBack();
                        return \Response::make($validator->messages(),500);
                    }
                }

                $productRow = $this->form->product()->getRelated();
                $productRow->fill($product);
                $this->form->product()->save($productRow);
            }

            \DB::commit();

            if(\WorkflowActivity::update($this->form,$this->controller->context))
            {
                //Story Relate
                \Queue::push('Story@relateTask',array('obj_class'=>get_class($this->form),
                                                     'obj_id'=>$this->form->id,
                                                     'action'=>\SwiftStory::ACTION_CREATE,
                                                     'user_id'=>$this->controller->currentUser->id,
                                                     'context'=>get_class($this->form)));
                //If form is being published
                if(\Input::get('publish'))
                {
                    //If on delivery, save pickup
                    if(\Input::get('type') === \SwiftPR::ON_DELIVERY)
                    {
                        $pickup = new SwiftPickup([
                            'pickup_date' => \Carbon::now(),
                            'driver_id' => \Input::get('driver_id'),
                            'status' => \SwiftPickup::COLLECTION_COMPLETE
                        ]);
                        $this->form->pickup()->save($pickup);
                    }

                    $validPublish = $this->publish($this->form->id,\SwiftApproval::PR_REQUESTER);
                    if($validPublish !== true)
                    {
                        return $validPublish;
                    }
                }

                //Success
                return \Response::make(json_encode(['success' => 1, 'url' => \Helper::generateUrl($this->form)]));
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
            if($this->form->isOwner() || $this->controller->permission->isAdmin())
            {
                switch(\Input::get('name'))
                {
                    case 'type':
                        if(!$this->controller->currentUser->isSuperUser())
                        {
                            return $this->controller->forbidden();
                        }
                        break;
                    case 'customer_code':
                        if(\Input::get('value') === "" || !is_numeric(\Input::get('value')))
                        {
                            return \Response::make('Please select a valid customer',400);
                        }
                        else
                        {
                            if(!\JdeCustomer::find(\Input::get('value')))
                            {
                                return \Response::make('Please select an existing customer',400);
                            }
                        }
                        break;
                    case 'paper_number':
                        if(\Input::get('value') === "" || !is_numeric(\Input::get('value')))
                        {
                            return \Response::make('Please enter a valid RFRF number',400);
                        }
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
                    return $q->where('pickup','=',\SwiftPRProduct::PICKUP)
                            ->whereHas('approvalretailman',function($q){
                                return $q->where('approved','=',\SwiftApproval::APPROVED);
                            });
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

    /*
     * Publishing: Start
     */

    /*
     * Publish Form
     * @param integer $form_id
     * @param integer $approvalType
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function publish($form_id,$approvalType)
    {
        $this->pk = $form_id;
        $this->approvalType = $approvalType;

        $this->setPublishForm();

        if($this->form)
        {
            $validation = $this->validatePublish();
            if($validation === true)
            {
                return $this->saveApproval();
            }
            else
            {
                return $validation;
            }
        }
        else
        {
            return \Response::make("Form not found",500);
        }
        
        return \Response::make("Unable to complete action",500);
    }

    /*
     * Sets the form to be published
     */
    private function setPublishForm()
    {
        switch($this->approvalType)
        {
            case \SwiftApproval::PR_REQUESTER:
                $this->form = $this->resource->with('product','product.discrepancy')->find($this->pk);
                break;
            case \SwiftApproval::PR_PICKUP:
                $this->form = $this->resource->with('pickup')->find($this->pk);
                break;
            case \SwiftApproval::PR_RECEPTION:
            case \SwiftApproval::PR_STOREVALIDATION:
                $this->form = $this->resource->with('productApproved')->find($this->pk);
                break;
            case \SwiftApproval::PR_CREDITNOTE:
                $this->form = $this->resource->with('creditnote')->find($this->pk);
                break;
            default:
                throw new \RuntimeException("This approval level is not supported.");
                break;
        }
    }

    /*
     * Validate the form before publishing
     * @return mixed
     */
    private function validatePublish()
    {
        switch($this->approvalType)
        {
            //Requester
            case \SwiftApproval::PR_REQUESTER:
                if(!$this->controller->permission->isAdmin() && !$this->form->isOwner())
                {
                    return \Response::make("You don't have permission to publish this form" ,400);
                }

                if(!count($this->form->product))
                {
                    return \Response::make('Please add some products to your form',400);
                }

                //Validation

                foreach($this->form->product as $p)
                {
                    if($p->jde_itm === null)
                    {
                        return \Response::make("Please set a Product for (ID: $p->id)",400);
                    }
                    if($p->reason_id === null)
                    {
                        return \Response::make("Please set a reason for '$p->name' (ID: $p->id)",400);
                    }
                    switch($this->form->type)
                    {
                        case \SwiftPR::ON_DELIVERY:
                            if($p->qty_pickup === null || $p->qty_pickup < 0)
                            {
                                return \Response::make("Please set a valid quantity at pickup for '$p->name' (ID: $p->id)",400);
                            }

                            if($p->qty_pickup !== ($p->qty_triage_picking + $p->qty_triage_disposal))
                            {
                                return \Response::make("Please make sure that quantity pickup tallies with quantity triage for '$p->name' (ID: $p->id)",400);
                            }
                            break;
                        case \SwiftPR::SALESMAN:
                            if($p->qty_client === null || $p->qty_client < 0)
                            {
                                return \Response::make("Please set a valid quantity at client for '$p->name' (ID: $p->id)",400);
                            }
                            break;
                    }
                }

                if($this->form->type === \SwiftPR::ON_DELIVERY)
                {
                    if($this->form->paper_number === null)
                    {
                        return \Response::make("Please enter an RFRF Paper number",400);
                    }

                    if(!count($this->form->pickup))
                    {
                        return \Response::make("Please enter pickup details",400);
                    }
                    else
                    {
                        foreach($this->form->pickup as $pickup)
                        {
                            if(!$pickup->driver_id)
                            {
                                return \Response::make("Please select a driver in your pickup details",400);
                            }

                            if($pickup->pickup_date === null)
                            {
                                return \Response::make("Please set a date in your pickup details",400);
                            }

                            if($pickup->status !== \SwiftPickup::COLLECTION_COMPLETE)
                            {
                                return \Response::make("Please set your pickup status to 'collection complete",400);
                            }
                        }
                    }
                }
                break;
            //Store Pickup
            case \SwiftApproval::PR_PICKUP:
                if(!$this->controller->permission->isAdmin() && !$this->controller->permission->isStorePikcup())
                {
                    return \Response::make("You don't have permission to publish this form" ,500);
                }

                //If there is no records of pickup for this form
                if(!count($this->form->pickup))
                {
                    return \Response::make("Please add your pickup details");
                }

                //Only forms of type salesman are allowed
                if($this->form->type !== \SwiftPR::SALESMAN)
                {
                    return \Response::make("Only forms by salesman have pickup");
                }
                break;
            //Store Reception
            case \SwiftApproval::PR_RECEPTION:
                if(!$this->controller->permission->isAdmin() && !$this->controller->permission->isStoreReception())
                {
                    return \Response::make("You don't have permission to publish this form" ,500);
                }

                foreach($this->form->productApproved as $p)
                {
                    if($p->qty_pickup === null || $p->qty_pickup < 0)
                    {
                        return \Response::make("Please set a valid quantity at pickup for '$p->name' (ID: $p->id)",500);
                    }

                    if($p->qty_pickup !== ($p->qty_triage_picking + $p->qty_triage_disposal))
                    {
                        return \Response::make("Please make sure that quantity pickup tallies with quantity triage for '$p->name' (ID: $p->id)",500);
                    }
                }
                break;
            //Store Validation
            case \SwiftApproval::PR_STOREVALIDATION:
                if(!$this->controller->permission->isAdmin() && !$this->controller->permission->isStoreValidation())
                {
                    return \Response::make("You don't have permission to publish this form" ,500);
                }

                foreach($this->form->productApproved as $p)
                {
                    if($p->qty_store === null || $p->qty_store < 0)
                    {
                        return \Response::make("Please set a valid quantity at store for '$p->name' (ID: $p->id)",500);
                    }

                    if($p->qty_store !== ($p->qty_triage_picking + $p->qty_triage_disposal))
                    {
                        return \Response::make("Please make sure that quantity store tallies with quantity triage for '$p->name' (ID: $p->id)",500);
                    }
                }
                break;
            //Accounting - Credit Note
            case \SwiftApproval::PR_CREDITNOTE:
                if(!$this->controller->permission->isAdmin() && !$this->controller->permission->isCreditor())
                {
                    return \Response::make("You don't have permission to publish this form" ,500);
                }

                if(!count($this->form->creditnote))
                {
                    return \Response::make("Please enter a credit note for this form",500);
                }
                break;
        }
        
        return true;
    }

    /*
     * Save approvals for publishing forms
     *
     * @param integer $approvalType
     * @return \Illuminate\Support\Facades\Response
     */
    private function saveApproval()
    {
        //Check if there is already an approval present
        $approval = $this->form->approval()
                    ->approvedBy($this->approvalType)
                    ->count();

        if($approval)
        {
            //Approval already present - System still processing form
            \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($this->form),'id'=>$this->form->id,'user_id'=>$this->controller->currentUser->id));
            /*
             * Check if form has already been approved
             */
            return \Response::make('Form already approved. System is busy processing',200);
        }
        else
        {
            $approvalSaved = $this->form->approval()->save(
               new \SwiftApproval([
                    'type' => $this->approvalType,
                    'approved' => \SwiftApproval::APPROVED,
                    'approval_user_id' => $this->controller->currentUser->id
               ])
            );

            if($approvalSaved)
            {
                \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($this->form),'id'=>$this->form->id,'user_id'=>$this->controller->currentUser->id));
                return true;
            }
            else
            {
                return \Response::make('Failed to approve form',400);
            }
        }
    }

    /*
     * Publishing: End
     */

    /*
     * Outputs an Excelsheet for download, containing all pending forms awaiting credit note issue.
     *
     * @return Illuminate\Support\Facades\Response
     */
    public function generateCreditNoteExcel()
    {
        $forms = $this->resource->orderBy('updated_at','desc')
                                    ->with(['order'])
                                    ->whereHas('workflow',function($q){
                                        return $q->where('status','=',\SwiftWorkflowActivity::INPROGRESS,'AND')
                                                ->whereHas('nodes',function($q){
                                                    return $q->where('user_id','=',0)
                                                             ->whereHas('definition',function($q){
                                                                return $q->where('name','=','pr_credit_note');
                                                            });
                                                });
                                    })
                                    ->get();
        if(count($forms))
        {
            // Create new PHPExcel object
            $objPHPExcel = new \PHPExcel();

            // Set document properties
            $objPHPExcel->getProperties()
                        ->setCreator("Scott Swift")
                        ->setLastModifiedBy("Scott Swift")
                        ->setTitle("Scott Swift - Product Returns ".date('Y-m-d h.m'))
                        ->setSubject("Scott Swift - Product Returns ".date('Y-m-d h.m'))
                        ->setDescription("Scott Swift - Pending Product Returns For Accounting Department")
                        ->setKeywords("scott swift pending product returns accounting")
                        ->setCategory("Scott Swift - Pending Product Returns");

            //Add Header Data
            $headers = array('A'=>'Date','B'=>'Form ID','C'=>'Order Number','D'=>'RFRF Reference','E'=>'Workflow Type','F'=>'Customer Name','G'=>'Customer Code');
            foreach($headers as $key => $header)
            {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($key.'1',$header);
            }

            //Print Rows of Data
            $count = 2;
            foreach($forms as $f)
            {
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$count,$f->updated_at->toDateString())
                        ->setCellValue('B'.$count,$f->id)
                        ->setCellValue('C'.$count,implode(',',array_map(function($v){
                            return $v['ref']."/".$v['type_name'];
                        },$f->order->toArray())))
                        ->setCellValue('D'.$count,$f->paper_number)
                        ->setCellValue('E'.$count,$f->type_name)
                        ->setCellValue('F'.$count,$f->customer_name)
                        ->setCellValue('G'.$count,$f->customer_code);
                $count++;
            }

            // Rename worksheet
            $objPHPExcel->getActiveSheet()->setTitle('Product Returns');


            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);

            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            ob_start();
            $objWriter->save('php://output');
            $excelOutput = ob_get_clean();
            return \Response::make($excelOutput,200,[
                'Content-Disposition' => 'inline; filename="Scott Swift - Pending Product Returns '.date('Y-m-d h.m').'.xls"',
                'Content-Type' => "application/vnd.ms-excel",
                'Content-Transfer-Encoding' => 'binary',
                'Cache-Control' => "max-age=0",
                'Expires' => "Mon, 26 Jul 1997 05:00:00 GMT",
                'Last-Modified' => gmdate('D, d M Y H:i:s').' GMT',
                'Pragma' => 'public'
            ]);
        }
        else
        {
            return \Response::make("No forms pending",400);
        }
    }
}
