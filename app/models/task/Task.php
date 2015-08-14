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

    protected $resource;

    protected $table;

    protected $data = array();

    public function __construct($controller)
    {
        $this->controller = $controller;

        $this->resource = new $this->table;

        $this->data = array_merge($this->data,$this->controller->data);
    }
}