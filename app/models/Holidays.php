<?php
/*
 * Name:
 * Description:
 */

class Holidays extends Eloquent {
    protected $table = "holidays";

    protected $dates = ['date'];

    public static function getAllDates()
    {
        return self::all()->lists('date');
    }
}