<?php

namespace Permission;

abstract class Permission {
    protected $currentUser;

    protected $resource;

    public function __construct()
    {
        $this->currentUser = \Sentry::getUser();

        $this->query = new $this->resource;
    }
}