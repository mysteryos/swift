<?php
/*
 * Name: Extends Collection Class
 * Description:
 */

namespace Swift\Core;

class Collection extends \Illuminate\Database\Eloquent\Collection {
    
    /**
     * Get the collection of items' attributes only as a plain array.
     *
     * @return array
     */    
    public function attributesToArray()
    {
        return array_map(function($value)
        {
                return $value instanceof \Illuminate\Support\Contracts\ArrayableInterface ? $value->attributesToArray() : $value;

        }, $this->items);
    }
    
    /**
     * Get the collection of items' relations only as a plain array.
     *
     * @return array
     */    
    public function relationsToArray()
    {
        return array_map(function($value)
        {
                return $value instanceof \Illuminate\Support\Contracts\ArrayableInterface ? $value->relationsToArray() : $value;

        }, $this->items);
    }    
}

