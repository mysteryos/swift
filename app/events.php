<?php
/* 
 * Name: Events
 * Description: All your events belong to me
 */

//Order Tracking Events

$orderTracking = new \Swift\Events\OrderTracking;

Event::subscribe($orderTracking);
