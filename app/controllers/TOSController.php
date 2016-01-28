<?php

/**
 * Created by PhpStorm.
 * User: kpudaruth
 * Date: 12/01/2016
 * Time: 08:03
 */
class TOSController extends Controller
{
    public function terms()
    {
        return \View::make('terms');
    }

    public function privacy()
    {
        return \View::make('privacy');
    }
}