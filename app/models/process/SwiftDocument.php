<?php
namespace Process;

/**
 * Description of SwiftErpOrder
 *
 * @author kpudaruth
 */

class SwiftDocument extends process
{

    protected $resourceName = "SwiftDocument";

    public function __construct($controller)
    {
        parent::__construct($controller);
    }

    public function upload($resourceName,$resourceId,\Closure $callback = null)
    {
        $this->form = (new $resourceName)->find($resourceId);

        if($this->form)
        {
            if($this->onUpload($callback))
            {
                if(!$this->parentResource)
                {
                    throw new \RuntimeException("Parent resource not set for document");
                }

                if(\Input::file('file'))
                {
                    $this->parentResource->document = \Input::file('file');
                    if($this->form->document()->save($this->parentResource))
                    {
                        return \Response::make(json_encode(['success'=>1,
                                        'url'=>$this->parentResource->getAttachedFiles()['document']->url(),
                                        'id'=>Crypt::encrypt($this->parentResource->id),
                                        'updated_on'=>$this->parentResource->getAttachedFiles()['document']->updatedAt(),
                                        'updated_by'=>Helper::getUserName($this->parentResource->user_id,$this->controller->currentUser)]));
                    }
                    else
                    {
                        return \Response::make('Upload failed.',400);
                    }
                }
                else
                {
                    return \Response::make('File not found.',400);
                }
            }
        }
        else
        {
            return \Response::make('Form not found',404);
        }
    }
    
    public function delete($parentResourceId,\Closure $closure = null)
    {
        $this->form = $this->parentResource->find($parentResourceId);

        if($this->form)
        {
            if($this->onDelete($callback))
            {
                if($this->form->delete())
                {
                    return \Response::make(json_encode(['success'=>1,
                                                        'url'=>$doc->getAttachedFiles()['document']->url(),
                                                        'id'=>Crypt::encrypt($doc->id)]));
                }
            }
        }
        
        return \Response::make('Delete failed.',400);
    }
}