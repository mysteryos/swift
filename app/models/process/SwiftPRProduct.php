<?php
/*
 * Name: Swift PR Product
 * Description: PR - Products Processing
 */

namespace Process;

class SwiftPRProduct extends Process
{
    protected $resourceName = "SwiftPRProduct";

    public function __construct($controller)
    {
        parent::__construct($controller);
    }

    public function create()
    {
        $this->resource->{\Input::get('name')} = \Input::get('value') == "" ? null : \Input::get('value');
        if($this->parentForm->product()->save($this->resource))
        {
            switch(\Input::get('name'))
            {
                case 'jde_itm':
                    \Queue::push('Helper@getProductPrice',array('product_id'=>$this->resource->getKey(),'class'=>get_class($this->resource)));
                    break;
            }
            return \Response::make(json_encode(['encrypted_id'=>\Crypt::encrypt($this->resource->id),'id'=>$this->resource->id]));
        }
        else
        {
            return \Response::make('Failed to save. Please retry',400);
        }
    }

    public function delete()
    {
        $this->setForm();
        if($this->form)
        {
            $this->parentForm = $this->form->pr()->first();

            //Basic Check
            if(!$this->parentForm->isOwner() && !$this->controller->permission->isAdmin())
            {
                return \Response::make("You don't have permission to complete this action",500);
            }

            $canRemoveProducts = $this->parentForm->workflow()->whereHas('nodes',function($q){
                return $q->whereHas('definition',function($q){
                    return $q->where('name','=','pr_preparation');
                });
            })->count();

            if($this->parentForm->isOwner() && !$this->controller->permission->isAdmin() && !$canRemoveProducts)
            {
                return \Response::make("You don't have access for this action, Please see your administrator",500);
            }

            return $this->processDelete();
        }
        else
        {
            return \Response::make('Form not found',404);
        }
    }

    public function put()
    {
        $this->setForm();
        if($this->form)
        {
            switch(\Input::get('name'))
            {
                case 'jde_itm':
                    \Queue::push('Helper@getProductPrice',array('product_id'=>$this->form->getKey(),'class'=>$this->resourceName));
                    break;
            }

            return $this->processPut();
        }
        else
        {
            return \Response::make('Error saving product: Invalid PK',400);
        }
    }

    public function save($parentFormId)
    {
        $this->parentForm = $this->parentResource->find(\Crypt::decrypt($parentFormId));

        /*
         * If not admin & not owner of form
         */
        if(!$this->controller->permission->isAdmin() && !$this->parentForm->isOwner())
        {
            return $this->controller->forbidden();
        }

        if($this->parentForm)
        {
            $v = \Input::get('value');
            //Validation

            switch(\Input::get('name'))
            {
                case 'jde_itm':
                    if(!is_numeric($v) || $v === "")
                    {
                        return \Response::make("Please select a valid product",500);
                    }
                    else
                    {
                        if(!\JdeProduct::find(\Input::get('value')))
                        {
                            return \Response::make("Please select an existing product",500);
                        }
                    }
                    break;
                case 'pickup':
                    if($v === "" || !is_numeric($v))
                    {
                        return \Response::make("Please select a valid pickup option",500);
                    }
                    else
                    {
                        if(!in_array($v,[0,1]))
                        {
                            return \Response::make("Please select a valid pickup value",500);
                        }
                    }
                    break;
                case 'reason_id':
                    if($v === "" || !is_numeric($v))
                    {
                        return \Response::make("Please select a valid reason code",500);
                    }
                    else
                    {
                        if(!\SwiftPRReason::find($v))
                        {
                            return \Response::make("Please select an existing reason code",500);
                        }
                    }
                    break;
                case 'invoice_id':
                    if($v === "" || !is_numeric($v))
                    {
                        return \Response::make("Please enter a valid invoice number",500);
                    }
                    else
                    {
                        if($v < 0)
                        {
                            return \Response::make("Please enter a positive value",500);
                        }
                    }
                    break;
                case 'qty_client':
                case 'qty_pickup':
                case 'qty_store':
                case 'qty_triage_picking':
                case 'qty_triage_disposal':
                    if($v === "" || !is_numeric($v))
                    {
                        return \Response::make("Please enter a valid quantity",500);
                    }
                    else
                    {
                        if($v < 0)
                        {
                            return \Response::make("Please enter a positive value",500);
                        }
                    }
                    break;
                case 'reason_others':
                    break;
                default:
                    return \Response::make("Unknown field",500);
                    break;
            }

            /*
             * New Product
             */
            if(is_numeric(\Input::get('pk')))
            {
                return $this->create();
            }
            else
            {
                return $this->put();
            }

        }

        return \Response::make("Form not found",500);
    }
}
