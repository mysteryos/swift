<?php
/*
 * Name: Process
 * Description: Default Functions for all process classes
 */

namespace Process;

abstract class Process {

    //Controller attribute which inherits all attributes from our controllers
    protected $controller;
    //Form Primary Key - Int | Boolean
    protected $pk;
    //Child Form - Class | Boolean
    protected $form;
    //Parent Form - Class | Boolean
    protected $parentForm;
    //Resource - Class | Boolean
    protected $resource;
    //Resource Name - String
    protected $resourceName;
    //Parent Resource - Class | Boolean
    protected $parentResource;
    //Parent Resource Name - String
    protected $parentResourceName;

    public function __construct($controller)
    {
        $this->controller = $controller;
        $this->form = false;


        /*
         * Resolve Resource Class
         */

        $this->setResource();

        /*
         * Resolve Parent Resource Class
         */

        $this->setParentResource();

        /*
         * Resolve Primary Key
         */

        $this->setPK();
    }

    /*
     * Magic Function: Call
     * Handles all standard event calls
     *
	 * @param  string  $method
	 * @param  array   $parameters
     * @return mixed
     */
    public function __call($method,$args)
    {
        /*
         * If method name starts with 'on'
         */
        if(substr($method, 0, strlen('on')) === 'on' && strlen($method) > 2 && ctype_upper($method[2]))
        {
            if($args[0] instanceof \Closure)
            {
                return $this->onEvent($args[0]);
            }
            else
            {
                throw new \RuntimeException("Argument 1 expected to be a closure");
            }
        }
    }

    /*
     * Handle Event Calls
     */
    private function onEvent($callback)
    {
        if ($callback)
        {
            return call_user_func($callback, $this);
        }

        return true;
    }

    /*
     * Setters
     */
    public function setResource($resource=null)
    {
        if(!$resource)
        {
            if(!isset($this->resource))
            {
                if(isset($this->resourceName))
                {
                   $this->resource = new $this->resourceName;
                }
                else
                {
                    throw new \RuntimeException("Resource Name must be set");
                }
            }
        }
        else
        {
            $this->resource = $resource;
        }
    }

    public function setParentResource($parentResource=null)
    {
        if(!$parentResource)
        {
            if(!isset($this->parentResource))
            {
                $this->parentResourceName = \Config::get("context.".$this->controller->context);
                if($this->parentResourceName)
                {
                    $this->parentResource = new $this->parentResourceName;
                }
                else
                {
                    $this->parentResource = false;
                }
            }
        }
        else
        {
            $this->parentResource = $parentResource;
        }
    }

    public function setPK()
    {
        if(!isset($this->pk))
        {
            if(\Input::has('pk'))
            {
                try
                {
                    //It's encrypted
                    if(!is_numeric(\Input::get('pk')) && strlen(\Input::get('pk')) > 150)
                    {
                        $this->pk = \Crypt::decrypt(\Input::get('pk'));
                    }
                    else
                    {
                        $this->pk = \Input::get('pk');
                    }
                } catch (Exception $ex) {
                    \Log::error("Attempt to decrypt PK failed, not valid encrypted PK - ".$ex->getMessage());
                    $this->pk = false;
                }
            }
            else
            {
                $this->pk = false;
            }
        }
    }

    /*
     * Set Parent Form
     */
    public function setParentForm()
    {
        if($this->parentResource)
        {
            if(!$this->pk)
            {
                throw new \RuntimeException("Primary key couldn't be resolved");
            }
            $this->parentForm = $this->parentResource->find($this->pk);
        }
        else
        {
            throw new RuntimeException("Unable to resolve parent resource name");
        }
    }

    /*
     * Set Form
     */
    public function setForm()
    {
        if($this->resource)
        {
            if(!$this->pk)
            {
                throw new \RuntimeException("Primary key couldn't be resolved");
            }
            $this->form = $this->resource->find($this->pk);
        }
        else
        {
            throw new RuntimeException("Unable to resolve resource name");
        }
    }

    /*
     * Process Create Form
     * @param boolean $workflowUpdate
     * @return \Illuminate\Support\Facades\Response
     */
    public function processCreate($workflowUpdate=false)
    {
        $this->resource->{\Input::get('name')} = \Input::get('value') == "" ? null : \Input::get('value');

        if($this->resource->save())
        {
            $this->form = $this->resource;
            if($workflowUpdate)
            {
                \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($this->form),'id'=>$this->form->id,'user_id'=>$this->controller->currentUser->id));
            }
            return \Response::make(json_encode(['encrypted_id'=>\Crypt::encrypt($this->form->id),'id'=>$this->form->id]));
        }
        else
        {
            return \Response::make('Failed to save. Please retry',400);
        }
    }

    /*
     * Process Create By Parent
     * @param string $parentRelationshipName
     * @param boolean $workflowUpdate
     * @return \Illuminate\Support\Facades\Response
     */
    public function processCreateByParent($relationshipName,$workflowUpdate=false)
    {
        $this->resource->{\Input::get('name')} = \Input::get('value') == "" ? null : \Input::get('value');

        if($this->parentForm === false)
        {
            throw new \RuntimeException("Set parent Form");
        }

        if($this->parentForm->$relationshipName()->save($this->resource))
        {
            $this->form = $this->resource;
            if($workflowUpdate)
            {
                //Workflow Update should be on parent
                \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($this->parentForm),'id'=>$this->parentForm->id,'user_id'=>$this->controller->currentUser->id));
            }
            return \Response::make(json_encode(['encrypted_id'=>\Crypt::encrypt($this->form->id),'id'=>$this->form->id]));
        }
        else
        {
            return \Response::make('Failed to save. Please retry',400);
        }
    }

    /*
     * Process Save Form - Single Attribute
     * @param boolean $workflowUpdate
     * @return \Illuminate\Support\Facades\Response
     */
    public function processPut($workflowUpdate=false)
    {
        $this->setForm();
        
        if($this->form)
        {
            $this->form->{\Input::get('name')} = \Input::get('value') == "" ? null : \Input::get('value');

            //Eloquent: Save
            if($this->form->save())
            {
                if($workflowUpdate)
                {
                    if(method_exists($this->form,'workflow'))
                    {
                        \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($this->form),'id'=>$this->form->getKey(),'user_id'=>$this->controller->currentUser->id));
                    }
                    elseif(isset($this->parentForm) && method_exists($this->parentForm,'workflow'))
                    {
                        \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($this->parentForm),'id'=>$this->parentForm->getKey(),'user_id'=>$this->controller->currentUser->id));
                    }
                    else
                    {
                        throw new \RuntimeException("Unable to locate workflow");
                    }
                }
                
                return \Response::make('Success');
            }
            else
            {
                return \Response::make('Failed to save. Please retry',400);
            }
        }
        else
        {
            return \Response::make('Error during save: Invalid PK',400);
        }
    }

    /*
     * Process Delete Form
     * @param boolean $workflowUpdate
     * @return \Illuminate\Support\Facades\Response
     */
    public function processDelete($workflowUpdate=false)
    {
        $this->setForm();

        if($this->form)
        {
            //Eloquent: Delete
            if($this->form->delete())
            {
                return \Response::make('Success');
            }
            else
            {
                return \Response::make('Unable to delete',400);
            }
        }
        else
        {
            return \Response::make('Entry not found',404);
        }
    }
}

