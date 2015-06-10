<?php
/*
 * Name: Holiday Eloquent Model
 * Description: Table that contains a list of holidays. Should be updated yearly with new holidays
 */

class Holidays extends Eloquent {
    protected $table = "holidays";

    protected $dates = ['date'];

    /*
     * Get a list of all holiday dates
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAllDates()
    {
        return self::all()->lists('date');
    }
}