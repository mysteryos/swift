<?php
/*
 * Name: Swift Tag
 * Description:
 */

namespace Process;

class SwiftTag extends process
{
    protected $resourceName = "SwiftTag";

    public function __construct($controller)
    {
        parent::__construct($controller);
    }

    public function save($tagList)
    {
        if(\Input::get('pk') && !is_numeric(\Input::get('pk')))
        {
            $this->parentForm = $this->parentResource->with('tag')->find($this->pk);
            if($this->parentForm)
            {
                //Lets check those tags
                if(count($this->parentForm->tag))
                {
                    if(\Input::get('value'))
                    {
                        //It already has some tags
                        //Save those not in table
                        foreach(\Input::get('value') as $val)
                        {
                            $found = false;
                            foreach($this->parentForm->tag as $t)
                            {
                                if($t->type == $val)
                                {
                                    $found = true;
                                    break;
                                }
                            }
                            //Save
                            if(!$found)
                            {
                                /*
                                 * Validate dat tag
                                 */
                                if(key_exists($val,$tagList))
                                {
                                    $this->resource->type = $val;
                                    if(!$this->parentForm->tag()->save($this->resource))
                                    {
                                        return \Response::make('Error: Unable to save tags',400);
                                    }
                                }
                                else
                                {
                                    return \Response::make('Error: Invalid tags',400);
                                }
                            }
                        }

                        //Delete values from table, not in value array

                        foreach($this->parentForm->tag as $t)
                        {
                            $found = false;
                            foreach(\Input::get('value') as $val)
                            {
                                if($val == $t->type)
                                {
                                    $found = true;
                                    break;
                                }
                            }
                            //Delete
                            if(!$found)
                            {
                                if(!$t->delete())
                                {
                                    return \Response::make('Error: Cannot delete tag',400);
                                }
                            }
                        }
                    }
                    else
                    {
                        //Delete all existing tags
                        if(!$this->parentForm->tag()->delete())
                        {
                            return \Response::make('Error: Cannot delete tag',400);
                        }
                    }
                }
                else
                {
                    //Alright, just save then
                    foreach(\Input::get('value') as $val)
                    {
                        /*
                         * Validate dat tag
                         */
                        if(key_exists($val,$tagList))
                        {
                            $this->resource->type = $val;
                            if(!$this->parentForm->tag()->save($this->resource))
                            {
                                return \Response::make('Error: Unable to save tags',400);
                            }
                        }
                        else
                        {
                            return \Response::make('Error: Invalid tags',400);
                        }
                    }
                }
                return \Response::make('Success');
            }
            else
            {
                return \Response::make('Error: Document not found',400);
            }
        }
        else
        {
            return \Response::make('Error: Document number invalid',400);
        }
    }
}