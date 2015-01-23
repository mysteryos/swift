<?php
/**
 * Description of ElasticSearchHelper: Helps with Indexing/Updating
 *
 * @author kpudaruth
 */

Namespace Swift\Services;

class ElasticSearchHelper {
    
    public function indexTask($job,$data)
    {
        if(isset($data['context']))
        {
            $this->IndexEs($data);
        }
        else
        {
            \Log::error('ElasticSearchHelper: No context set for the following dataset: '.json_encode($data));
        }
        $job->delete();
    }
    
    public function updateTask($job,$data)
    {
        if(isset($data['context']))
        {
            $this->UpdateEs($data);
        }
        else
        {
            \Log::error('ElasticSearchHelper: No context set for the following dataset: '.json_encode($data));
        }
        $job->delete();        
    }
    
    public function IndexEs($data)
    {
        $params = array();
        $params['index'] = \App::environment();
        $params['type'] = $data['context'];
        $model = new $data['class'];
        $form = $model::find($data['id']);
        if(count($form))
        {
            $params['id'] = $form->id;
            if($form->timestamps)
            {
                $params['timestamp'] = $form->updated_at->toIso8601String();
            }
            $params['body'][$data['context']] = $this->saveFormat($form);
            \Es::index($params);
        }
    }
    
    public function UpdateEs($data)
    {
        $params = array();
        $params['index'] = \App::environment();
        $params['type'] = $data['context'];
        $model = new $data['class'];
        $form = $model::withTrashed()->find($data['id']);
        
        if(count($form))
        {
            if($data['info-context'] === $data['context'])
            {
                $params['id'] = $data['id'];
                $params['body']['doc'][$data['info-context']] = $this->saveFormat($form);                  
            }
            else
            {
                //Form is child, we get parent
                $parent = $form->esGetParent();
                if(count($parent))
                {
                    $params['id'] = $parent->id;
                    $children = $parent->{$data['info-context']}()->get();
                    if(count($children))
                    {
                        $params['body']['doc'][$data['info-context']] = $this->saveFormat($children);
                    }
                    else
                    {
                        $params['body']['doc'][$data['info-context']] = array();
                    }
                }
            }
            \Es::update($params);
        }
    }
    
    /*
     * Iterate through all Es accessors of the model.
     */
    public function esAccessor(&$object)
    {
        if(is_object($object))
        {
            $attributes = $object->getAttributes();
            foreach($attributes as $name => $value)
            {
                $esMutator = 'get' . studly_case($name) . 'EsAttribute';
                if (method_exists($object, $esMutator)) {
                    $object->{$name} = $object->$esMutator($object->{$name});
                }
            }
        }
        else
        {
            throw New \RuntimeException("Expected type object");
        }
    }
    
    /*
     * Iterates over a collection applying the getEsSaveFormat function
     */
    public function saveFormat($obj)
    {
        if($obj instanceof \Illuminate\Database\Eloquent\Model)
        {
            return $obj->getEsSaveFormat();
        }
        else
        {
            return array_map(function($value)
            {
                return $value->getEsSaveFormat();
            }, $obj->all());
        }
    }
}
