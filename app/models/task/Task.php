<?php
/*
 * Name: Task
 * Description: Related Queries associated to tasks
 */

namespace Task;

abstract class Task {
    protected $query;

    //Controller attribute which inherits all attributes from our controllers
    protected $controller;

    protected $controller_data;

    protected $resource;

    protected $data = array();

    public function __construct($controller)
    {
        $this->controller = $controller;

        $this->query = (new $this->resource)->query();

        $this->data = array_merge($this->data,$this->controller_data);
    }
}