<?php
/**
 * Description of ElasticSearchHelper: Helps with Indexing/Updating with Elastic Search Server (https://www.elastic.co)
 *
 * @author kpudaruth
 */

Namespace Swift\Services;

class ElasticSearchHelper {

    /*
     * Laravel Queue - Index Task
     * @param array $job
     * @param array $data
     */
    public function indexTask($job,$data)
    {
        if(\Config::get('website.elasticsearch') === true)
        {
            if(isset($data['context']))
            {
                $this->IndexEs($data);
            }
            else
            {
                \Log::error('ElasticSearchHelper: No context set for the following dataset: '.json_encode($data));
            }
        }
        $job->delete();
    }

    /*
     * Laravel Queue - Update Task
     * @param array $job
     * @param array $data
     */
    public function updateTask($job,$data)
    {
        if(\Config::get('website.elasticsearch') === true)
        {
            if(isset($data['context']))
            {
                $this->UpdateEs($data);
            }
            else
            {
                \Log::error('ElasticSearchHelper: No context set for the following dataset: '.json_encode($data));
            }
        }
        $job->delete();        
    }

    /*
     * Index Elastic Search Document
     * @param array $data
     */
    public function IndexEs($data)
    {
        $params = array();
        $params['index'] = \App::environment();
        $params['type'] = $data['context'];
        $model = new $data['class'];
        $form = $model::find($data['id']);
        if($form)
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

    /*
     * Update Elastic Search
     * @param array $data
     */
    public function UpdateEs($data)
    {
        $params = array();
        $params['index'] = \App::environment();
        $params['type'] = $data['context'];
        $model = new $data['class'];
        $form = $model::withTrashed()->find($data['id']);
        
        if(count($form))
        {
            /*
             * Main form is being updated
             */
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
                    //Id is always that of parent
                    $params['id'] = $parent->id;
                    //fetch all children, given that we cannot save per children basis
                    $children = $parent->{$data['info-context']}()->get();
                    if(count($children))
                    {
                        //Get data in a format that can be saved by Elastic Search
                        $params['body']['doc'][$data['info-context']] = $this->saveFormat($children);
                    }
                    else
                    {
                        //Empty it is
                        $params['body']['doc'][$data['info-context']] = array();
                    }
                }
                else
                {
                    \Log::error("Parent not found for {$data['context']} - {$data['class']}, Id: {$data['id']}");
                    return false;
                }
            }

            //Check if Parent Exists
            try
            {
                $result = \Es::get([
                    'id' => $params['id'],
                    'index' => $params['index'],
                    'type' => $data['context']
                ]);
            } catch (Exception $ex) {
                if($ex instanceof \Elasticsearch\Common\Exceptions\Missing404Exception || $ex instanceof \Guzzle\Http\Exception\ClientErrorResponseException)
                {
                    //if not, we set it
                    if (isset($parent) && $parent)
                    {
                        $this->indexEs([
                            'context' => $data['context'],
                            'class' => get_class($parent),
                            'id' => $parent->id,
                        ]);
                    }
                    else
                    {
                        \Log::error('Unexpected error in updating elasticsearch records, parent not set with message: '.$ex->getMessage());
                        return false;
                    }
                }
                else
                {
                    \Log::error('Unexpected error in updating elasticsearch records: '.$ex->getMessage());
                    return false;
                }
            }
            
            \Es::update($params);
        }
    }
    
    /*
     * Iterate through all Es accessors of the model.
     * @param \Illuminate\Database\Eloquent\Model $object
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
     * @param mixed $object
     * 
     * @return array
     */
    public function saveFormat($object)
    {
        if($object instanceof \Illuminate\Database\Eloquent\Model)
        {
            return $object->getEsSaveFormat();
        }
        else
        {
            return array_map(function($value)
            {
                return $value->getEsSaveFormat();
            }, $object->all());
        }
    }
}
